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
use oat\oatbox\filesystem\File;
use oat\oatbox\filesystem\FileSystemService;
use oat\tao\model\datatable\implementation\DatatableRequest;
use oat\tao\model\export\implementation\CsvExporter;
use oat\taoDelivery\model\fields\DeliveryFieldsService;
use oat\taoOutcomeUi\model\ResultsService;
use oat\taoOutcomeUi\model\table\ContextTypePropertyColumn;
use oat\taoOutcomeUi\model\table\VariableColumn;
use oat\taoOutcomeUi\model\table\VariableDataProvider;
use oat\taoOutcomeUi\scripts\task\ExportDeliveryResults;
use oat\taoTaskQueue\model\QueueDispatcher;
use oat\taoTaskQueue\model\QueueDispatcherInterface;
use oat\taoTaskQueue\model\Task\CallbackTaskInterface;
use Psr\Http\Message\StreamInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * DeliveryResultsExporter
 *
 * @author Gyula Szucs <gyula@taotesting.com>
 */
class DeliveryResultsExporter implements ServiceLocatorAwareInterface
{
    use OntologyAwareTrait;
    use ServiceLocatorAwareTrait;

    /**
     * Test Taker properties to be exported.
     *
     * @var array
     */
    private $testTakerProperties = [
        RDFS_LABEL,
        PROPERTY_USER_LOGIN,
        PROPERTY_USER_FIRSTNAME,
        PROPERTY_USER_LASTNAME,
        PROPERTY_USER_MAIL,
        PROPERTY_USER_UILG
    ];

    /**
     * Delivery properties to be exported.
     *
     * @var array
     */
    private $deliveryProperties = [
        RDFS_LABEL,
        DeliveryFieldsService::PROPERTY_CUSTOM_LABEL,
        TAO_DELIVERY_MAXEXEC_PROP,
        TAO_DELIVERY_START_PROP,
        TAO_DELIVERY_END_PROP,
        DELIVERY_DISPLAY_ORDER_PROP,
        TAO_DELIVERY_ACCESS_SETTINGS_PROP
    ];

    /**
     * @var \core_kernel_classes_Resource
     */
    private $delivery;

    /**
     * @var ResultsService
     */
    private $resultsService;

    /**
     * Which submitted variables are we querying for?
     *
     * Possible values:
     *  - lastSubmitted
     *  - firstSubmitted
     *
     * @var string
     */
    private $submittedVersion = ResultsService::VARIABLES_FILTER_LAST_SUBMITTED;

    /**
     * @see \oat\taoResultServer\models\classes\ResultManagement::getResultByDelivery()
     * @var array
     */
    private $storageOptions = [];

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
     * DeliveryResultsExporter constructor.
     *
     * @param string|\core_kernel_classes_Resource $delivery
     * @param ResultsService                       $resultsService
     */
    public function __construct($delivery, ResultsService $resultsService)
    {
        $this->delivery = $this->getResource($delivery);
        $this->resultsService = $resultsService;

        if (!$this->delivery->exists()) {
            throw new \RuntimeException('Results Export: delivery "'. $this->delivery->getUri() .'" does not exist.');
        }
    }

    /**
     * Get test taker columns to be exported.
     *
     * @return array
     */
    public function getTestTakerColumns()
    {
        $columns = [];

        // add custom properties, it contains the GROUP property as well
        $customProps = $this->getClass(TAO_CLASS_SUBJECT)->getProperties();

        $testTakerProps = array_merge($this->testTakerProperties, $customProps);

        foreach ($testTakerProps as $property){
            $property = $this->getProperty($property);
            $col = new ContextTypePropertyColumn(ContextTypePropertyColumn::CONTEXT_TYPE_TEST_TAKER, $property);

            if ($property->getUri() == RDFS_LABEL) {
                $col->label = __('Test Taker');
            }

            $columns[] = $col->toArray();
        }

        return $columns;
    }

    /**
     * Get delivery columns to be exported.
     *
     * @throws \RuntimeException
     * @return array
     */
    public function getDeliveryColumns()
    {
        $columns = [];

        // add custom properties, it contains the group property as well
        $customProps = $this->getClass($this->delivery->getOnePropertyValue($this->getProperty(RDF_TYPE)))->getProperties();

        $deliveryProps = array_merge($this->deliveryProperties, $customProps);

        foreach ($deliveryProps as $property){
            $property = $this->getProperty($property);
            $loginCol = new ContextTypePropertyColumn(ContextTypePropertyColumn::CONTEXT_TYPE_DELIVERY, $property);

            if ($property->getUri() == RDFS_LABEL) {
                $loginCol->label = __('Delivery');
            }

            $columns[] = $loginCol->toArray();
        }

        return $columns;
    }

    /**
     * Returns all grade columns to be exported.
     *
     * @throws \RuntimeException
     * @return array
     */
    public function getGradeColumns()
    {
        return $this->resultsService->getVariableColumns($this->delivery, \taoResultServer_models_classes_OutcomeVariable::class);
    }

    /**
     * Returns all response columns to be exported.
     *
     * @throws \RuntimeException
     * @return array
     */
    public function getResponseColumns()
    {
        return $this->resultsService->getVariableColumns($this->delivery, \taoResultServer_models_classes_ResponseVariable::class);
    }

    /**
     * @return array
     */
    public function getData()
    {
        $data = $this->resultsService->getResultsByDelivery(
            $this->delivery,
            $this->getColumnsToExport(),
            $this->submittedVersion,
            $this->storageOptions
        );

        // flattening data: only 'cell' is what we need
        return array_map(function($row){
            return $row['cell'];
        }, $data);
    }

    /**
     * Returns the payload for datatable js component.
     *
     * @return array
     */
    public function getDataTablePayload()
    {
        $request = DatatableRequest::fromGlobals();

        $page = $request->getPage();
        $limit = $request->getRows();

        // offset and limit be default for getResultsByDelivery()
        $this->storageOptions = array_merge([
            'offset' => $limit * ($page - 1),
            'limit' => $limit
        ], $this->storageOptions);

        $data = $this->getData();

        $countTotal = $this->resultsService->getImplementation()->countResultByDelivery([$this->delivery->getUri()]);

        $payload = [
            'data' => $data,
            'page' => $page,
            'amount'  => count($data),
            'total' => ceil($countTotal / $limit)
        ];

        return $payload;
    }

    /**
     * @return DeliveryResultsExporter
     */
    public function useFirstSubmittedVariables()
    {
        $this->submittedVersion = ResultsService::VARIABLES_FILTER_FIRST_SUBMITTED;

        return $this;
    }

    /**
     * @return DeliveryResultsExporter
     */
    public function useLastSubmittedVariables()
    {
        $this->submittedVersion = ResultsService::VARIABLES_FILTER_LAST_SUBMITTED;

        return $this;
    }

    /**
     * @param array $storageOptions
     * @return DeliveryResultsExporter
     */
    public function setStorageOptions(array $storageOptions)
    {
        $this->storageOptions = $storageOptions;

        return $this;
    }

    /**
     * @param array|string $columnsToExport
     * @return DeliveryResultsExporter
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
     * @return array
     */
    public function getColumnsToExport()
    {
        // if we have columns set from outside, let's use that otherwise we are querying for all type of columns
        $columns = $this->columnsToExport ?: array_merge(
            $this->getTestTakerColumns(),
            $this->getDeliveryColumns(),
            $this->getGradeColumns(),
            $this->getResponseColumns()
        );

        if (empty($this->builtColumns)) {
            // build column objects
            $this->builtColumns = $this->buildColumns($columns);
        }

        return $this->builtColumns;
    }

    /**
     * @return File
     */
    public function export()
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

        return $this->saveFile($exporter->export(true, false));
    }

    /**
     * @return CallbackTaskInterface
     */
    public function createExportTask()
    {
        /** @var QueueDispatcher $queueDispatcher */
        $queueDispatcher = $this->getServiceLocator()->get(QueueDispatcher::SERVICE_ID);

        $columns = [];

        // we need to convert every column object into array first
        foreach ($this->getColumnsToExport() as $column) {
            $columns[] = $column->toArray();
        }

        return $queueDispatcher->createTask(
            new ExportDeliveryResults(),
            [
                $this->delivery->getUri(),
                $columns,
                $this->submittedVersion
            ],
            __('CSV results export for delivery "%s"', $this->delivery->getLabel())
        );
    }

    /**
     * @param string|Resource|StreamInterface $content
     * @return File
     */
    private function saveFile($content)
    {
        /** @var Directory $queueStorage */
        $queueStorage = $this->getServiceLocator()
            ->get(FileSystemService::SERVICE_ID)
            ->getDirectory(QueueDispatcherInterface::FILE_SYSTEM_ID);

        $fileName = strtolower(\tao_helpers_Display::textCleaner($this->delivery->getLabel(), '*'))
            .'_'
            .\tao_helpers_Uri::getUniqueId($this->delivery->getUri())
            .'_'
            .date('YmdHis')
            .'.csv';

        $file = $queueStorage->getFile($fileName);

        $file->write($content);

        return $file;
    }

    /**
     * Decode the JSON encoded columns.
     *
     * @param string $columnsJson
     * @throws \RuntimeException
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
     * @return array
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