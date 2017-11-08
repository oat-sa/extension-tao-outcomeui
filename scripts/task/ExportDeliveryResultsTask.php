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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\taoOutcomeUi\scripts\task;

use common_report_Report as Report;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\action\Action;
use oat\oatbox\filesystem\Directory;
use oat\taoDelivery\model\fields\DeliveryFieldsService;
use oat\taoOutcomeUi\model\export\ResultExportService;
use oat\taoOutcomeUi\model\table\ContextTypePropertyColumn;
use oat\taoOutcomeUi\model\table\VariableColumn;
use oat\taoOutcomeUi\model\table\VariableDataProvider;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use oat\oatbox\action\ResolutionException;
use oat\taoOutcomeUi\model\ResultsService;

/**
 * Class ExportDeliveryResultsTask
 * @package oat\taoOutcomeUi\scripts
 */
class ExportDeliveryResultsTask implements Action, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;
    use OntologyAwareTrait;

    /**
     * The delivery to extract results
     *
     * @var \core_kernel_classes_Resource
     */
    protected $delivery;

    /**
     * The PHP resource of export file
     *
     * @var resource
     */
    protected $resource;

    /**
     * The path to working tmp file
     *
     * @var string
     */
    protected $tmpFile;

    /**
     * @param $params
     * @return Report
     */
    public function __invoke($params)
    {
        $this->loadExtensions();

        try {
            $this->parseParams($params);
        } catch (ResolutionException $e) {
            return new Report(Report::TYPE_ERROR, $e->getMessage());
        }

        try {
            $this->exportData();
            $file = $this->createTaskFile();
        } catch (\common_Exception $e) {
            return new Report(Report::TYPE_ERROR, $e->getMessage());
        }

        return new Report(
            Report::TYPE_SUCCESS,
            __('Results successfully exported for delivery "%s"', $this->delivery->getLabel()),
            $file->getPrefix()
        );
    }

    /**
     * Parse the provided parameters array to extract delivery uri.
     *
     * @param $params
     * @throws ResolutionException If delivery uri is not provided and does not exist
     */
    protected function parseParams($params)
    {
        if (empty($params)) {
            throw new ResolutionException(__('Parameters were not given. Expected Syntax: ExportDeliveryResults <deliveryId>'));
        }

        if (!isset($params[0])) {
            throw new ResolutionException('Delivery uri was not specified');
        }

        $this->delivery = $this->getResource($params[0]);
        if (!$this->delivery->exists()) {
            throw new ResolutionException('Provided delivery does not exist.');
        }
    }

    /**
     * @return array
     */
    private function getTestTakerColumns()
    {
        $columns = [];
        $testTakerProps = [RDFS_LABEL, PROPERTY_USER_LOGIN, PROPERTY_USER_FIRSTNAME, PROPERTY_USER_LASTNAME, PROPERTY_USER_MAIL, PROPERTY_USER_UILG];

        // add custom properties, it contains the group property as well
        $customProps = $this->getClass(TAO_CLASS_SUBJECT)->getProperties();

        $testTakerProps = array_merge($testTakerProps, $customProps);

        foreach ($testTakerProps as $property){
            $loginCol = new ContextTypePropertyColumn(ContextTypePropertyColumn::CONTEXT_TYPE_TEST_TAKER, $this->getProperty($property));
            $columns[] = $loginCol->toArray();
        }

        return $columns;
    }

    /**
     * @return array
     */
    private function getDeliveryColumns()
    {
        $columns = [];

        $deliveryProps = [RDFS_LABEL, DeliveryFieldsService::PROPERTY_CUSTOM_LABEL, TAO_DELIVERY_MAXEXEC_PROP, TAO_DELIVERY_START_PROP, TAO_DELIVERY_END_PROP, TAO_DELIVERY_RESULTSERVER_PROP, DELIVERY_DISPLAY_ORDER_PROP, TAO_DELIVERY_ACCESS_SETTINGS_PROP];

        // add custom properties, it contains the group property as well
        $customProps = $this->getClass($this->delivery->getOnePropertyValue($this->getProperty(RDF_TYPE)))->getProperties();

        $deliveryProps = array_merge($deliveryProps, $customProps);

        foreach ($deliveryProps as $property){
            $loginCol = new ContextTypePropertyColumn(ContextTypePropertyColumn::CONTEXT_TYPE_DELIVERY, $this->getProperty($property));
            $columns[] = $loginCol->toArray();
        }

        return $columns;
    }

    /**
     * Fetch the delivery results data from result service.
     * Format it and sort it by columns.
     * Write the output to the export file
     *
     * @throws \common_Exception
     * @throws \common_exception_Error
     * @throws \core_kernel_persistence_Exception
     */
    protected function exportData()
    {
        $resultsService = $this->getResultsService();
        $filter = 'lastSubmitted';

        $columns = [];

        $cols = array_merge(
            $this->getTestTakerColumns(),
            $this->getDeliveryColumns(),
            $resultsService->getVariableColumns($this->delivery, \taoResultServer_models_classes_OutcomeVariable::class, $filter),
            $resultsService->getVariableColumns($this->delivery, \taoResultServer_models_classes_ResponseVariable::class, $filter)
        );

        $dataProvider = new VariableDataProvider();
        foreach ($cols as $col) {
            $column = \tao_models_classes_table_Column::buildColumnFromArray($col);
            if (!is_null($column)) {
                if ($column instanceof VariableColumn) {
                    $column->setDataProvider($dataProvider);
                }
                $columns[] = $column;
            }
        }
        $rows = $resultsService->getResultsByDelivery($this->delivery, $columns, $filter);
        $columnNames = array_reduce($columns, function ($carry, $item) {

            if ($item instanceof ContextTypePropertyColumn && $item->getProperty()->getUri() == RDFS_LABEL) {
                $carry[] = $item->isTestTakerType() ? __('Test Taker') : __('Delivery');
            } else {
                $carry[] = $item->getLabel();
            }

            return $carry;
        });

        if (!empty($rows)) {
            foreach ($rows as $key => $row) {
                $rowResult = [];
                foreach ($row['cell'] as $rowKey => $rowVal) {
                    $rowResult[$columnNames[$rowKey]] = $rowVal[0];
                }
                if ($key == 0) {
                    $this->initFile(array_keys($rowResult));
                }
                $this->exportCsvToFile($rowResult);
            }
        } else {
            $this->exportCsvToFile([array_fill_keys($columnNames, '')]);
        }
    }

    /**
     * Write the given data array to export file
     *
     * @param $data
     * @throws \common_Exception
     */
    protected function exportCsvToFile($data)
    {
        fputcsv($this->resource, $data, $this->getCsvControl('delimiter'), $this->getCsvControl('enclosure'));
    }

    /**
     * Get the file resource to write the export.
     * If file is not instantiated, delete if exists and write headers
     *
     * @param array $headers
     * @throws \common_Exception
     */
    protected function initFile(array $headers)
    {
        $this->tmpFile = \tao_helpers_File::createTempDir() . uniqid('delivery_result_export') . '.csv';
        $this->resource = fopen($this->tmpFile, 'w+');
        $this->exportCsvToFile($headers);
    }

    /**
     * Copy the tmpFile to task file located into the TaskQueue storage
     *
     * @return \oat\oatbox\filesystem\File
     * @throws \common_Exception
     */
    protected function createTaskFile()
    {
        /** @var Directory $queueStorage */
        $queueStorage = $this->getResultExportService()->getQueueStorage();

        $fileName = strtolower(\tao_helpers_Display::textCleaner($this->delivery->getLabel(), '*'))
            .'_'
            .\tao_helpers_Uri::getUniqueId($this->delivery->getUri())
            .'_'
            .date('YmdHis')
            .'.csv';

        $file = $queueStorage->getFile($fileName);

        rewind($this->resource);
        $file->put($this->resource);
        fclose($this->resource);
        unlink($this->tmpFile);
        rmdir(dirname($this->tmpFile));

        return $file;
    }

    /**
     * Get the specified CSV control (delimiter or enclosure)
     *
     * @param $name
     * @return mixed
     * @throws \common_Exception
     */
    protected function getCsvControl($name)
    {
        $controls = [
            'delimiter' => ',',
            'enclosure' => '"',
        ];

        switch ($name) {
            case 'delimiter':
                return $controls['delimiter'];
                break;
            case 'enclosure':
                return $controls['enclosure'];
                break;
            default:
                throw new \common_Exception('Csv controls are delimiter or enclosure.');
                break;
        }
    }

    /**
     * Get the results service
     *
     * @return ResultsService
     */
    protected function getResultsService()
    {
        return ResultsService::singleton();
    }

    /**
     * Get the results export service
     *
     * @return ResultExportService
     */
    protected function getResultExportService()
    {
        return (new ResultExportService())->setServiceLocator($this->getServiceLocator());
    }

    /**
     * Load the required TAO extensions (for constants)
     */
    protected function loadExtensions()
    {
        $this->getServiceLocator()->get(\common_ext_ExtensionsManager::SERVICE_ID)->getExtensionById('taoOutcomeUi');
    }
}