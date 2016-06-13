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
use \core_kernel_classes_Class;
use \core_kernel_classes_Property;
use \core_kernel_classes_Resource;
use \tao_models_classes_table_Column;
use \tao_models_classes_table_PropertyColumn;
use oat\taoOutcomeUi\model\ResultsService;
use oat\taoOutcomeUi\model\table\GradeColumn;
use oat\taoOutcomeUi\model\table\ResponseColumn;
use oat\taoOutcomeUi\model\table\VariableColumn;
use oat\taoOutcomeRds\model\RdsResultStorage;
use tao_helpers_Uri;
use oat\taoDeliveryRdf\model\DeliveryAssemblyService;
use oat\taoOutcomeUi\model\table\VariableDataProvider;

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
     */
    public function index()
    {
        $deliveryService = DeliveryAssemblyService::singleton();
        if($this->getRequestParameter('classUri') !== $deliveryService->getRootClass()->getUri()) {
            $filter = $this->getRequestParameter('filter');
            $uri = $this->getRequestParameter('uri');
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
     * Relies on two optionnal parameters,
     * - filters (facet based query) ($this->hasRequestParameter('filter'))
     * - the list of columns currently selected on the frontend side ($this->hasRequestParameter('columns'))
     * @return void - a csv string is being sent out by parent class -> data method into the buffer
     */
    public function getCsvFile(){
        $rows = array();

        $filter =  $this->hasRequestParameter('filter') ? $this->getRequestParameter('filter') : array();
    	$columns = $this->hasRequestParameter('columns') ? $this->getColumns('columns') : array();

        $delivery = new \core_kernel_classes_Resource(tao_helpers_Uri::decode($this->getRequestParameter('uri')));
        $implementation = $this->service->getReadableImplementation($delivery);
        $this->service->setImplementation($implementation);
    	
        $delivery = array();
        if($this->hasRequestParameter('uri')){
            $delivery[] = \tao_helpers_Uri::decode($this->getRequestParameter('uri'));
        }

    	//The list of delivery Results matching the current selection filters
        $results = array();
        foreach($this->service->getImplementation()->getResultByDelivery($delivery) as $result){
            $results[] = \taoDelivery_models_classes_execution_ServiceProxy::singleton()->getDeliveryExecution($result['deliveryResultIdentifier']);
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

        /** @var \taoDelivery_models_classes_execution_DeliveryExecution $result */
        foreach($results as $result) {
            $cellData = array();
            foreach ($columns as $column) {
                if (count($column->getDataProvider()->cache) > 0) {
                    $cellData[]=self::filterCellData($column->getDataProvider()->getValue(new core_kernel_classes_Resource($result->getIdentifier()), $column), $filter);
                } else {
                    $cellData[]=self::filterCellData(
                        (string)$this->service->getTestTaker($result)->getOnePropertyValue(new \core_kernel_classes_Property(PROPERTY_USER_LOGIN)),
                        $filter);
                }
            }
            $rows[] = array(
                    'id' => $result->getIdentifier(),
                    'cell' => $cellData
            );
        }

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
    public function getResultOfSubjectColumn(){

		$testtaker = new tao_models_classes_table_PropertyColumn(new core_kernel_classes_Property(PROPERTY_RESULT_OF_SUBJECT));
		$arr[] = $testtaker->toArray();
        echo json_encode(array(
                'columns' => $arr,
                'first'   => true
        ));
    }

    /** 
     * Returns all columns with all responses pertaining to the current delivery results selection
     */
    public function getResponseColumns() {
	    $this->getVariableColumns(\taoResultServer_models_classes_ResponseVariable::class);
    }

    /** 
     * Returns all columns with all grades pertaining to the current delivery results selection
     */
     public function getGradeColumns() {
        $this->getVariableColumns(\taoResultServer_models_classes_OutcomeVariable::class);
    }

     /**
     * Retrieve the different variables columns pertainign to the current selection of results
     * Implementation note : it nalyses all the data collected to identify the different response variables submitted by the items in the context of activities
     */
    protected function getVariableColumns($variableClassUri) {

		$columns = array();
        $filter =  $this->hasRequestParameter('filter') ? $this->getRequestParameter('filter') : array();

        $delivery = new \core_kernel_classes_Resource(tao_helpers_Uri::decode($this->getRequestParameter('uri')));
        $implementation = $this->service->getReadableImplementation($delivery);
        $this->service->setImplementation($implementation);
		

        $delivery = array();
        if($this->hasRequestParameter('uri')){
            $delivery[] = \tao_helpers_Uri::decode($this->getRequestParameter('uri'));
        }

		//The list of delivery Results matching the current selection filters
        $results = $this->service->getImplementation()->getResultByDelivery($delivery, $filter);

		//retrieveing all individual response variables referring to the  selected delivery results
		$selectedVariables = array ();
		foreach ($results as $result){
            $de = \taoDelivery_models_classes_execution_ServiceProxy::singleton()->getDeliveryExecution($result["deliveryResultIdentifier"]);
            $variables = $this->service->getVariables($de);
            $selectedVariables = array_merge($selectedVariables, $variables);
		}
		//retrieving The list of the variables identifiers per activities defintions as observed
		$variableTypes = array();
		foreach ($selectedVariables as $variable) {
            if((!is_null($variable[0]->item) ||  !is_null($variable[0]->test))&& (get_class($variable[0]->variable) == \taoResultServer_models_classes_OutcomeVariable::class && $variableClassUri == \taoResultServer_models_classes_OutcomeVariable::class)
            || (get_class($variable[0]->variable) == \taoResultServer_models_classes_ResponseVariable::class && $variableClassUri == \taoResultServer_models_classes_ResponseVariable::class)){
                //variableIdentifier
                $variableIdentifier = $variable[0]->variable->identifier;
                $uri = (!is_null($variable[0]->item))? $variable[0]->item : $variable[0]->test;
                $object = new core_kernel_classes_Resource($uri);
                if (get_class($object) == "core_kernel_classes_Resource") {
                $contextIdentifierLabel = $object->getLabel();
                $contextIdentifier = $object->getUri(); // use the callId/itemResult identifier
                }
                else {
                    $contextIdentifierLabel = $object->__toString();
                    $contextIdentifier = $object->__toString();
                }
                $variableTypes[$contextIdentifier.$variableIdentifier] = array("contextLabel" => $contextIdentifierLabel, "contextId" => $contextIdentifier, "variableIdentifier" => $variableIdentifier);
            }
        }
		foreach ($variableTypes as $variable){
		    switch ($variableClassUri){
                case \taoResultServer_models_classes_OutcomeVariable::class :
                    $columns[] = new GradeColumn($variable["contextId"], $variable["contextLabel"], $variable["variableIdentifier"]);
                    break;
		        case \taoResultServer_models_classes_ResponseVariable::class :
                    $columns[] = new ResponseColumn($variable["contextId"], $variable["contextLabel"], $variable["variableIdentifier"]);
                    break;
	            default:
                    $columns[] = new ResponseColumn($variable["contextId"], $variable["contextLabel"], $variable["variableIdentifier"]);
			}
		}
		$arr = array();
		foreach ($columns as $column) {
			$arr[] = $column->toArray();
		}
    	echo json_encode(array(
    		'columns' => $arr
    	));
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
        foreach ($this->getRequestParameter($identifier) as $array) {
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

        $delivery = new \core_kernel_classes_Resource($deliveryUri);
        $implementation = $this->service->getReadableImplementation($delivery);
        $this->service->setImplementation($implementation);
        
        $deliveryResults = $this->service->getImplementation()->getResultByDelivery(array($deliveryUri), $options);
        $counti = $this->service->getImplementation()->countResultByDelivery(array($deliveryUri));
        $results = array();
        foreach($deliveryResults as $deliveryResult){
            $results[] = \taoDelivery_models_classes_execution_ServiceProxy::singleton()->getDeliveryExecution($deliveryResult['deliveryResultIdentifier']);
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

        /** @var \taoDelivery_models_classes_execution_DeliveryExecution $result */
        foreach($results as $result) {
            $data = array(
                'id' => $result->getIdentifier()
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
                        $data[$key] = self::filterCellData(
                            $column->getDataProvider()->getValue(new core_kernel_classes_Resource($result->getIdentifier()), $column),
                            $filterData
                        );
                    } else {
                        $data[$key] = self::filterCellData(
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

    private static function filterCellData($observationsList, $filterData){
        //if the cell content is not an array with multiple entries, do not filter

        if (!(is_array($observationsList))){
            return $observationsList;

        }
        //takes only the alst or the first observation
            if (
                ($filterData=="lastSubmitted" or $filterData=="firstSubmitted")
                and
                (is_array($observationsList))
            ){
            $returnValue = array();

            //sort by timestamp observation
           uksort($observationsList, "oat\\taoOutcomeUi\\model\\ResultsService::sortTimeStamps" );
           $filteredObservation = ($filterData=='lastSubmitted') ? array_pop($observationsList) : array_shift($observationsList);
            $returnValue[]= $filteredObservation[0];

            } else {
               $cellData = "";
               foreach ($observationsList as $observation) {
                   $cellData.= $observation[0].$observation[1].'
                       ';
               }
                $returnValue = $cellData;
            }
        return $returnValue;
    }
}
?>
