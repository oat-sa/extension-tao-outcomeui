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
 * Copyright (c) 2009-2012 (original work) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 *
 *
 */


namespace oat\taoOutcomeUi\controller;

use \common_Exception;
use \core_kernel_classes_Property;
use \core_kernel_classes_Resource;
use \tao_models_classes_table_Column;
use \tao_models_classes_table_PropertyColumn;
use oat\taoOutcomeUi\model\ResultsService;
use oat\taoOutcomeUi\model\table\VariableColumn;
use tao_helpers_Uri;
use oat\taoDeliveryRdf\model\DeliveryAssemblyService;
use oat\tao\model\export\implementation\CsvExporter;
use oat\taoOutcomeUi\model\table\VariableDataProvider;
use oat\taoResultServer\models\classes\ResultServerService;
use oat\taoDelivery\model\execution\DeliveryExecution;

/**
 * should be entirelyrefactored
 * Results Controller provide actions performed from url resolution
 *
 * @author Joel Bout <joel@taotesting.com>
 * @author Patrick Plichart <patrick@taotesting.com>
 * @package taoOutcomeUi
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 *
 */
class ResultTable extends \tao_actions_CommonModule {

    /**
     * constructor: initialize the service and the default data
     * @return Results
     */
    protected $service;

    public function __construct() {

        parent::__construct();
        $this->service = ResultsService::singleton();
    }

    /**
     * Result Table entry page
     * @throws \tao_models_classes_MissingRequestParameterException
     */
    public function index()
    {
        $deliveryService = DeliveryAssemblyService::singleton();
        if($this->getRequestParameter('classUri') !== $deliveryService->getRootClass()->getUri()) {
            $filter = $this->getRequestParameter('filter');
            $uri = $this->getRequestParameter('uri');
            if (!\common_Utils::isUri(tao_helpers_Uri::decode($uri))) {
                throw new \tao_models_classes_MissingRequestParameterException('uri');
            }
            $this->setData('filter', $filter);
            $this->setData('uri', $uri);
            $this->setView('resultTable.tpl');
        } else {
            $this->setData('type', 'info');
            $this->setData('error',__('No tests have been taken yet. As soon as a test-taker will take a test his results will be displayed here.'));
            $this->setView('index.tpl');
        }
    }

    /**
     * Download csv file with all results of all delivery executions of given delivery.
     */
    public function getCsvFileByDelivery()
    {
        $filter = 'lastSubmitted';
        $delivery = new \core_kernel_classes_Resource(tao_helpers_Uri::decode($this->getRequestParameter('uri')));
        $columns = [];
        $cols = array_merge(
             $this->getTestTakerColumn(),
             $this->service->getVariableColumns($delivery, CLASS_OUTCOME_VARIABLE, $filter),
             $this->service->getVariableColumns($delivery, CLASS_RESPONSE_VARIABLE, $filter)
        );

        $dataProvider = new VariableDataProvider();
        foreach ($cols as $col) {
            $column = tao_models_classes_table_Column::buildColumnFromArray($col);
            if (!is_null($column)) {
                if($column instanceof VariableColumn){
                    $column->setDataProvider($dataProvider);
                }
                $columns[] = $column;
            }
        }
        $columns[0]->label = __("Test taker");
        $rows = $this->service->getResultsByDelivery($delivery, $columns, $filter);
        $columnNames = array_reduce($columns, function ($carry, $item) {
            $carry[] = $item->label;
            return $carry;
        });
        $result = [];
        foreach ($rows as $row) {
            $rowResult = [];
            foreach ($row['cell'] as $rowKey => $rowVal) {
                $rowResult[$columnNames[$rowKey]] = $rowVal[0];
            }
            $result[] = $rowResult;
        }

        //If there are no executions yet, the file is exported but contains only the header
        if (empty($result)) {
            $result = [array_fill_keys($columnNames, '')];
        }

        $exporter = new CsvExporter($result);
        $exporter->export(true, true, ";");
    }

    /**
     * Relies on two optionnal parameters,
     * - filters (facet based query) ($this->hasRequestParameter('filter'))
     * - the list of columns currently selected on the frontend side ($this->hasRequestParameter('columns'))
     * @return void - a csv string is being sent out by parent class -> data method into the buffer
     */
    public function getCsvFile(){
        $filter =  $this->hasRequestParameter('filter') ? $this->getRequestParameter('filter') : array();
    	$columns = $this->hasRequestParameter('columns') ? $this->getColumns('columns') : array();

        $delivery = new \core_kernel_classes_Resource(tao_helpers_Uri::decode($this->getRequestParameter('uri')));
        $rows = $this->service->getResultsByDelivery($delivery, $columns, $filter);

        $encodedData = $this->dataToCsv($columns, $rows,';','"');

        header('Set-Cookie: fileDownload=true'); //used by jquery file download to find out the download has been triggered ...
        setcookie("fileDownload","true", 0, "/");
        header("Content-type: text/csv");
        header('Content-Disposition: attachment; filename=Data.csv');
        echo $encodedData;
    }

    /**
     * Returns the default column selection that contains the Result of Subject property (This has been removed from the other commodity function adding grades and responses)
     */
    public function getResultOfSubjectColumn()
    {
        echo json_encode(array(
                'columns' => $this->getTestTakerColumn(),
                'first'   => true
        ));
    }

    /**
     * Returns all columns with all responses pertaining to the current delivery results selection
     */
    public function getResponseColumns() {
        $filterData =  $this->hasRequestParameter('filter') ? $this->getRequestParameter('filter') : array();
        $delivery = new \core_kernel_classes_Resource(tao_helpers_Uri::decode($this->getRequestParameter('uri')));
        echo json_encode(array(
            'columns' => $this->service->getVariableColumns($delivery, \taoResultServer_models_classes_ResponseVariable::class, $filterData)
        ));
    }

    /** 
     * Returns all columns with all grades pertaining to the current delivery results selection
     */
     public function getGradeColumns() {
         $filterData =  $this->hasRequestParameter('filter') ? $this->getRequestParameter('filter') : array();
         $delivery = new \core_kernel_classes_Resource(tao_helpers_Uri::decode($this->getRequestParameter('uri')));
         echo json_encode(array(
             'columns' => $this->service->getVariableColumns($delivery, \taoResultServer_models_classes_OutcomeVariable::class, $filterData)
         ));
    }

    /**
     * @return array
     */
    private function getTestTakerColumn()
    {
        $testtaker = new tao_models_classes_table_PropertyColumn(new core_kernel_classes_Property(PROPERTY_RESULT_OF_SUBJECT));
        $arr[] = $testtaker->toArray();
        return $arr;
    }

    /**
     * @return string A csv file with the data table
     * @param columns an array of column objects including the property information and as it is used in the tao class.Table.php context
     */
    private function dataToCsv($columns, $rows, $delimiter, $enclosure){
       //opens a temporary stream rather than producing a file and get benefit of csv php helpers
        $handle = fopen('php://temp', 'r+');
        //print_r($this->columnsToFlatArray($columns));
       fputcsv($handle, $this->columnsToFlatArray($columns), $delimiter, $enclosure);
       foreach ($rows as $line) {
           $seralizedData = array();
           foreach ($line["cell"] as $cellData){

             if (!is_array($cellData)) {
                 $seralizedData[] = $cellData;
             } else {
                 $seralizedData[] = array_pop($cellData);
             }
               //$seralizedData[] = $this->cellDataToString($cellData);
           }
           fputcsv($handle, $seralizedData, $delimiter, $enclosure);
       }
       rewind($handle);
       //read the content of the csv
       $encodedData = "";
       while (!feof($handle)) {
           $encodedData .= fread($handle, 8192);
       }
       fclose($handle);
       return $encodedData;
    }

    /**
     * Returns a flat array with the list of column labels.
     * @param columns an array of column object including the property information and that is used within tao class.Table context
     */
    private function columnsToFlatArray($columns){
        $flatColumnsArray = array();
        foreach ($columns as $column){
            $flatColumnsArray[] = $column->label;
        }
        return $flatColumnsArray;
    }


    protected  function getColumns($identifier)
    {
        if (!$this->hasRequestParameter($identifier)) {
            throw new common_Exception('Missing parameter "'.$identifier.'" for getColumns()');
        }

        $dataProvider = new VariableDataProvider();
        $columns = array();
        $variables = json_decode($this->getRequest()->getRawParameters()[$identifier], true);
        foreach ($variables as $array) {
            if (isset($data['type']) && !is_subclass_of($data['type'], tao_models_classes_table_Column::class)) {
                throw new \common_exception_Error('Non column specified as column type');
            }

            $column = tao_models_classes_table_Column::buildColumnFromArray($array);
            if (!is_null($column)) {
                if ($column instanceof VariableColumn) {
                    $column->setDataProvider($dataProvider);
                }
            	$columns[] = $column;
            }
        }
        return $columns;
    }

    /**
     * Data provider for the table, returns json encoded data according to the parameter
     * @author Bertrand Chevrier, <taosupport@tudor.lu>,
     */
    public function data() {
       	$filterData =  $this->hasRequestParameter('filter') ? $this->getRequestParameter('filter') : array();
       	$deliveryUri =  \tao_helpers_Uri::decode($this->getRequestParameter('uri'));

        $columns = $this->hasRequestParameter('columns') ? $this->getColumns('columns') : array();
    	$page = $this->getRequestParameter('page');
        $limit = $this->getRequestParameter('rows');
        $sidx = $this->getRequestParameter('sidx');
        $sord = $this->getRequestParameter('sord');
        $start = $limit * $page - $limit;

        $options = array (
            'recursive'=>true, 
            'like' => false, 
            'offset' => $start, 
            'limit' => $limit, 
            'order' => $sidx, 
            'orderdir' => $sord  
        );
        $response = new \stdClass();

        $storage = $this->getServiceManager()->get(ResultServerService::SERVICE_ID)->getResultStorage($deliveryUri);
        $this->service->setImplementation($storage);
        
        $deliveryResults = $storage->getResultByDelivery(array($deliveryUri), $options);
        $counti = $storage->countResultByDelivery(array($deliveryUri));
        $results = array();
        foreach($deliveryResults as $deliveryResult){
            $results[] = $deliveryResult['deliveryResultIdentifier'];
        }

        $dpmap = array();
        foreach ($columns as $column) {
            $dataprovider = $column->getDataProvider();
            $found = false;
            foreach ($dpmap as $k => $dp) {
                if ($dp['instance'] == $dataprovider) {
                    $found = true;
                    $dpmap[$k]['columns'][] = $column;
                }
            }
            if (!$found) {
                $dpmap[] = array(
                    'instance'	=> $dataprovider,
                    'columns'	=> array(
                            $column
                    )
                );
            }
        }

        foreach ($dpmap as $arr) {
            $arr['instance']->prepare($results, $arr['columns']);
        }

        /** @var DeliveryExecution $result */
        foreach($results as $result) {
            $data = array(
                'id' => $result
            );
            foreach ($columns as $column) {
                $key = null;
                if($column instanceof tao_models_classes_table_PropertyColumn){
                    $key = $column->getProperty()->getUri(); 
                } else  if ($column instanceof VariableColumn) {
                    $key =  $column->getContextIdentifier() . '_' . $column->getIdentifier();
                }
                if(!is_null($key)){
                    if (count($column->getDataProvider()->cache) > 0) {
                        $data[$key] = ResultsService::filterCellData(
                            $column->getDataProvider()->getValue(new core_kernel_classes_Resource($result), $column),
                            $filterData
                        );
                    } else {
                        $data[$key] = ResultsService::filterCellData(
                            (string)$this->service->getTestTaker($result)->getOnePropertyValue(new \core_kernel_classes_Property(PROPERTY_USER_LOGIN)),
                            $filterData
                        );
                    }
                }
                else {
                    \common_Logger::w('KEY IS NULL');
                }
            }
            $response->data[] = $data;
        }

        $response->page = (int)$page;
        if ($limit!=0) {
            $response->total = ceil($counti / $limit);
        } else {
            $response->total = 1;
        }
        $response->records = count($results);

        $this->returnJSON($response);
    }
}
