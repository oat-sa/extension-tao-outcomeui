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
use oat\tao\model\export\implementation\CsvExporter;
use oat\tao\model\taskQueue\QueueDispatcherInterface;
use oat\taoOutcomeUi\model\ResultsService;
use oat\taoOutcomeUi\model\table\ContextTypePropertyColumn;
use oat\taoOutcomeUi\model\table\VariableColumn;
use oat\taoOutcomeUi\model\table\VariableDataProvider;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * SingleDeliveryResultsExporter
 *
 * @author Gyula Szucs <gyula@taotesting.com>
 */
class SingleDeliveryResultsExporter implements ResultsExporterInterface
{
    use OntologyAwareTrait;
    use ServiceLocatorAwareTrait;

    /**
     * @var \core_kernel_classes_Resource
     */
    private $delivery;

    /**
     * @var ResultsService
     */
    private $resultsService;

    /**
     * Metadata columns to be exported.
     *
     * @var array
     */
    private $columnsToExport = [];

    /**
     * @var \tao_models_classes_table_Column[]
     */
    private $builtColumns = [];

    /**
     * Which submitted variables are we exporting?
     *
     * Possible values:
     *  - lastSubmitted (default)
     *  - firstSubmitted
     *
     * @var string
     */
    private $variableToExport = ResultsService::VARIABLES_FILTER_LAST_SUBMITTED;

    /**
     * @var array
     */
    private $storageOptions = [];
    /**
     * @var ColumnsProvider
     */
    private $columnsProvider;

    /**
     * @param string|\core_kernel_classes_Resource $delivery
     * @param ResultsService                       $resultsService
     * @param ColumnsProvider                      $columnsProvider
     * @throws \common_exception_NotFound
     */
    public function __construct($delivery, ResultsService $resultsService, ColumnsProvider $columnsProvider)
    {
        $this->delivery = $this->getResource($delivery);

        if (!$this->delivery->exists()) {
            throw new \common_exception_NotFound('Results Exporter: delivery "'. $this->delivery->getUri() .'" does not exist.');
        }

        $this->resultsService = $resultsService;
        $this->columnsProvider = $columnsProvider;
    }

    /**
     * @inheritdoc
     */
    public function getResourceToExport()
    {
        return $this->delivery;
    }

    /**
     * @inheritdoc
     */
    public function setColumnsToExport($columnsToExport)
    {
        if (is_string($columnsToExport)) {
            $columnsToExport = $this->decodeColumns($columnsToExport);
        }

        $this->columnsToExport = (array) $columnsToExport;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getColumnsToExport()
    {
        if (!empty($this->columnsToExport)) {
            $columns = $this->columnsToExport;
        } else {
            $variables = array_merge($this->columnsProvider->getGradeColumns(),$this->columnsProvider->getResponseColumns());
            usort($variables, function ($a, $b) {
                return strcmp($a["label"], $b["label"]);
            });
            $columns = array_merge(
                $this->columnsProvider->getTestTakerColumns(),
                $this->columnsProvider->getDeliveryColumns(),
                $variables
            );
        }

        if (empty($this->builtColumns)) {
            // build column objects
            $this->builtColumns = $this->buildColumns($columns);
        }

        return $this->builtColumns;
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
     * @inheritdoc
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
        $data = $this->resultsService->getResultsByDelivery(
            $this->getResourceToExport(),
            $this->getColumnsToExport(),
            $this->getVariableToExport(),
            $this->storageOptions
        );

        // flattening data: only 'cell' is what we need
        return array_map(function($row){
            return $row['cell'];
        }, $data);
    }

    /**
     * @inheritdoc
     */
    public function export($destination = null)
    {
        $columnNames = $this->resultsService->getColumnNames($this->getColumnsToExport());
        $data = $this->getData();

        // merge column names and data into one array
        $result = [];
        foreach ($data as $row) {
            $rowResult = [];
            foreach ($row as $rowKey => $rowVal) {
                $rowResult[$columnNames[$rowKey]] = $rowVal[0];
            }
            $result[] = $rowResult;
        }

        //If there are no executions yet, the file is exported but contains only the header
        if (empty($result)) {
            $result = [array_fill_keys($columnNames, '')];
        }

        $exporter = new CsvExporter($result);

        unset($columnNames, $data, $result);

        return is_null($destination)
            ? $this->saveToStorage($exporter)
            : $this->saveToLocal($exporter, $destination);
    }

    /**
     * @param CsvExporter $exporter
     * @param string      $destination
     * @return string
     * @throws \common_exception_InvalidArgumentType
     */
    private function saveToLocal($exporter, $destination)
    {
        $fullPath = realpath($destination) . DIRECTORY_SEPARATOR . $this->getFileName();

        file_put_contents($fullPath, $exporter->export(true, false));

        return $fullPath;
    }

    /**
     * @param CsvExporter $exporter
     * @return string
     * @throws \League\Flysystem\FileExistsException
     * @throws \common_Exception
     * @throws \common_exception_InvalidArgumentType
     */
    private function saveToStorage($exporter)
    {
        /** @var Directory $queueStorage */
        $queueStorage = $this->getServiceLocator()
            ->get(FileSystemService::SERVICE_ID)
            ->getDirectory(QueueDispatcherInterface::FILE_SYSTEM_ID);

        $file = $queueStorage->getFile($this->getFileName());

        $file->write($exporter->export(true, false));

        return $file->getPrefix();
    }

    /**
     * @return string
     */
    private function getFileName()
    {
        return strtolower(\tao_helpers_Display::textCleaner($this->delivery->getLabel(), '*'))
            .'_'
            .\tao_helpers_Uri::getUniqueId($this->delivery->getUri())
            .'_'
            .date('YmdHis') . rand(10, 99) //more unique name
            .'.csv';
    }

    /**
     * Decode the JSON encoded columns.
     *
     * @param string $columnsJson
     * @return array
     */
    private function decodeColumns($columnsJson)
    {
        return ($columnsData = json_decode($columnsJson, true)) !== null && json_last_error() === JSON_ERROR_NONE
            ? $columnsData
            : [];
    }

    /**
     * Build the column objects from the provided array of decoded column values. For example:
     *
     * [
     *  type = "oat\taoOutcomeUi\model\table\ContextTypePropertyColumn"
     *  label = "Test Taker"
     *  prop = "http://www.w3.org/2000/01/rdf-schema#label"
     *  contextType = "test_taker"
     * ]
     * [
     *  type = "oat\taoOutcomeUi\model\table\GradeColumn"
     *  label = "Planets and moons-SCORE"
     *  contextId = "http://taoplatform.loc/tao.rdf#i1499248290562399"
     *  contextLabel = "Planets and moons"
     *  variableIdentifier = "SCORE"
     * ]
     *
     * @param array $columnsData
     * @return \tao_models_classes_table_Column[]
     */
    private function buildColumns($columnsData)
    {
        $columns = [];
        $dataProvider = new VariableDataProvider();

        foreach ($columnsData as $column) {
            if (!isset($column['type']) || !is_subclass_of($column['type'], \tao_models_classes_table_Column::class)) {
                throw new \RuntimeException('Column type not specified or wrong type provided');
            }

            $column = \tao_models_classes_table_Column::buildColumnFromArray($column);
            if (!is_null($column)) {
                if ($column instanceof VariableColumn) {
                    $column->setDataProvider($dataProvider);
                }

                if ($column instanceof ContextTypePropertyColumn && $column->getProperty()->getUri() == RDFS_LABEL) {
                    $column->label = $column->isTestTakerType() ? __('Test Taker') : __('Delivery');
                }

                $columns[] = $column;
            }
        }
        
        return $columns;
    }
}