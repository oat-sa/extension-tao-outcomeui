<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoOutcomeUi\model\export;

use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\filesystem\Directory;
use oat\oatbox\filesystem\FileSystemService;
use oat\tao\model\taskQueue\QueueDispatcherInterface;
use oat\taoOutcomeUi\model\ResultsService;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * MultipleDeliveriesResultsExporter
 *
 * @author Gyula Szucs <gyula@taotesting.com>
 */
class MultipleDeliveriesResultsExporter implements ResultsExporterInterface
{
    use OntologyAwareTrait;
    use ServiceLocatorAwareTrait;

    /**
     * @var \core_kernel_classes_Resource|string
     */
    private $deliveryClass;

    private $columnsToExport = [];

    private $variableToExport = ResultsService::VARIABLES_FILTER_LAST_SUBMITTED;

    private $storageOptions = [];

    /**
     * @var ResultsService
     */
    private $resultsService;

    /**
     * Temporary directory to store the generated csv files.
     *
     * @var string
     */
    private $tmpDir;

    /**
     * MultipleDeliveriesResultsExporter constructor.
     *
     * @param string|\core_kernel_classes_Class $deliveryClass
     * @param ResultsService                    $resultsService
     * @throws \common_exception_NotFound
     */
    public function __construct($deliveryClass, ResultsService $resultsService)
    {
        $this->deliveryClass = $this->getClass($deliveryClass);

        if (!$this->deliveryClass->exists()) {
            throw new \common_exception_NotFound('Results Exporter: delivery class "'. $this->deliveryClass->getUri() .'" does not exist.');
        }

        $this->resultsService = $resultsService;

        $this->tmpDir = \tao_helpers_File::createTempDir();
    }

    /**
     * @inheritdoc
     */
    public function getResourceToExport()
    {
        return $this->deliveryClass;
    }

    /**
     * @inheritdoc
     */
    public function setColumnsToExport($columnsToExport)
    {
        $this->columnsToExport = $columnsToExport;

        return $this;
    }

    /**
     * Empty array means all columns need to be used for export.
     *
     * @return array
     */
    public function getColumnsToExport()
    {
        return $this->columnsToExport;
    }

    /**
     * @inheritdoc
     */
    public function setVariableToExport($variableToExport)
    {
        $allowedFilters = [
            ResultsService::VARIABLES_FILTER_ALL,
            ResultsService::VARIABLES_FILTER_FIRST_SUBMITTED,
            ResultsService::VARIABLES_FILTER_LAST_SUBMITTED,
        ];
        if (!in_array($variableToExport, $allowedFilters)) {
            throw new \InvalidArgumentException('Results Exporter: wrong submitted variable "'. $variableToExport .'"');
        }

        $this->variableToExport = $variableToExport;

        return $this;
    }

    /**
     * Always exports the last submitted variables.
     *
     * @return string
     */
    public function getVariableToExport()
    {
        return $this->variableToExport;
    }

    /**
     * @inheritdoc
     */
    public function setStorageOptions(array $storageOptions)
    {
        $this->storageOptions = $storageOptions;

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        $data = [];

        /** @var \core_kernel_classes_Resource $delivery */
        foreach ($this->deliveryClass->getInstances(true) as $delivery) {
            $data[$delivery->getUri()] = (new SingleDeliveryResultsExporter(
                $delivery,
                $this->resultsService,
                (new ColumnsProvider($delivery, $this->resultsService))
            ))
                ->setServiceLocator($this->getServiceLocator())
                ->getData();
        }

        return $data;
    }

    /**
     * @param null|string $destination
     * @return string
     * @throws \common_Exception
     */
    public function export($destination = null)
    {
        $this->exportIntoFolder($this->deliveryClass, $this->tmpDir);

        $tmpZipPath = \tao_helpers_File::createZip($this->tmpDir, true);

        if (file_exists($tmpZipPath)) {
            $finaleName = is_null($destination)
                ? $this->saveToStorage($tmpZipPath)
                : $this->saveToLocal($tmpZipPath, $destination);

            // delete temp files and dir
            unlink($tmpZipPath);
            if (\helpers_File::emptyDirectory($this->tmpDir)) {
                rmdir($this->tmpDir);
            }

            return $finaleName;
        }

        return '';
    }

    /**
     * @param string $tmpZipPath
     * @param string $destination
     * @return string
     */
    private function saveToLocal($tmpZipPath, $destination)
    {
        $fullPath = realpath($destination) . DIRECTORY_SEPARATOR . $this->getFileName();

        \tao_helpers_File::copy($tmpZipPath, $fullPath, false);

        return $fullPath;
    }

    /**
     * @param string $tmpZipPath
     * @return string
     * @throws \common_Exception
     */
    private function saveToStorage($tmpZipPath)
    {
        /** @var Directory $queueStorage */
        $queueStorage = $this->getServiceLocator()
            ->get(FileSystemService::SERVICE_ID)
            ->getDirectory(QueueDispatcherInterface::FILE_SYSTEM_ID);

        $file = $queueStorage->getFile($this->getFileName());

        $stream = fopen($tmpZipPath, 'r');

        $file->put($stream);

        fclose($stream);

        return $file->getPrefix();
    }

    /**
     * @return string
     */
    private function getFileName()
    {
        return strtolower(\tao_helpers_Display::textCleaner($this->deliveryClass->getLabel(), '*'))
            .'_'
            .\tao_helpers_Uri::getUniqueId($this->deliveryClass->getUri())
            .'_'
            .date('YmdHis') . rand(10, 99) //more unique name
            .'.zip';
    }

    /**
     * Recursively exports all results under a class into a folder.
     *
     * @param \core_kernel_classes_Class $deliveryClass
     * @param string $destination
     */
    private function exportIntoFolder(\core_kernel_classes_Class $deliveryClass, $destination)
    {
        /** @var \core_kernel_classes_Resource $delivery */
        foreach ($deliveryClass->getInstances(false) as $delivery) {
            (new SingleDeliveryResultsExporter(
                $delivery,
                $this->resultsService,
                (new ColumnsProvider($delivery, $this->resultsService))
            ))
                ->setServiceLocator($this->getServiceLocator())
                ->export($destination);
        }

        if ($subClasses = $deliveryClass->getSubClasses(false)) {
            /** @var \core_kernel_classes_Class $subClass */
            foreach ($subClasses as $subClass) {
                $newDestination = $destination . DIRECTORY_SEPARATOR . \tao_helpers_Display::textCleaner($subClass->getLabel(), '*');
                mkdir($newDestination);
                $this->exportIntoFolder($subClass, $newDestination);
            }
        }
    }
}