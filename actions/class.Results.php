<?php

/**
 * Results Controller provide actions performed from url resolution
 * 
 * @author Bertrand Chevrier, <taosupport@tudor.lu>
 * @package taoResults
 * @subpackage actions
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * 
 */
class taoResults_actions_Results extends tao_actions_TaoModule {

	private $resultGridOptions;
	
    /**
     * constructor: initialize the service and the default data
     * @return Results
     */
    public function __construct() {

        parent::__construct();

        //the service is initialized by default
        $this->service = taoResults_models_classes_ResultsService::singleton();
        $this->resultGridOptions = array(
			'columns' => array(
				RDFS_LABEL						=> array('weight'=>2),
				PROPERTY_RESULT_OF_DELIVERY 	=> array('weight'=>2),
				PROPERTY_RESULT_OF_SUBJECT	 	=> array('weight'=>2)
			)
		);
        $this->defaultData();
    }

    
	public function index() {
		//Class to filter on
		$clazz = new core_kernel_classes_Class(TAO_DELIVERY_RESULT);
		
		//Properties to filter on
		$properties = array();
		$properties[] = new core_kernel_classes_Property(PROPERTY_RESULT_OF_DELIVERY);
		$properties[] = new core_kernel_classes_Property(PROPERTY_RESULT_OF_SUBJECT);
		
		//Monitoring grid
		$processMonitoringGrid = new taoResults_helpers_DeliveryResultGrid(array(), $this->resultGridOptions);
		$grid = $processMonitoringGrid->getGrid();
		$model = $grid->getColumnsModel();
		
		//Filtering data
		$this->setData('clazz', $clazz);
		$this->setData('properties', $properties);
		
		//Monitoring data
		$this->setData('model', json_encode($model));
		$this->setData('data', $processMonitoringGrid->toArray());
		
		$this->setView('resultList.tpl');
	}
	
	public function getResults() {
		$returnValue = array();
		$filter = null;
		
		//get the filter
		if($this->hasRequestParameter('filter')){
			$filter = $this->getRequestParameter('filter');
			$filter = $filter == 'null' || empty($filter) ? null : $filter;
            if(is_array($filter)){
                foreach($filter as $propertyUri=>$propertyValues){
                    foreach($propertyValues as $i=>$propertyValue){
                        $propertyDecoded = tao_helpers_Uri::decode($propertyValue);
                        if(common_Utils::isUri($propertyDecoded)){
                            $filter[$propertyUri][$i] = $propertyDecoded;
                        }
                    }
                }
            }
		}
		//get the processes uris
		$processesUri = $this->hasRequestParameter('processesUri') ? $this->getRequestParameter('processesUri') : null;
		
		$clazz = new core_kernel_classes_Class(TAO_DELIVERY_RESULT);
		if(!is_null($filter)){
			$results = $clazz->searchInstances($filter, array ('recursive'=>true));
		}
		else if(!is_null($processesUri)){
			foreach($processesUri as $processUri){
				$results[$processUri] = new core_kernel_classes_resource($processUri);
			}
		}
		else{
			$results = $clazz->getInstances();
		}
		
		$data = array();
		foreach ($results as $res) {
			$props = $res->getPropertiesValues(array(
				PROPERTY_RESULT_OF_DELIVERY,
				PROPERTY_RESULT_OF_SUBJECT
			));
			$data[$res->getUri()] = array(
				RDFS_LABEL					=> $res->getLabel(),
				PROPERTY_RESULT_OF_DELIVERY => array_shift($props[PROPERTY_RESULT_OF_DELIVERY]),
				PROPERTY_RESULT_OF_SUBJECT	=> array_shift($props[PROPERTY_RESULT_OF_SUBJECT])
			);
		}
		
		$resultsGrid = new taoResults_helpers_DeliveryResultGrid($data, $this->resultGridOptions);
		$data = $resultsGrid->toArray();
		//var_dump($data);
		
		echo json_encode($data);
	}
	
	public function viewResult() {
		$clazz = new core_kernel_classes_Class(TAO_DELIVERY_RESULT);
        $result = $this->getCurrentInstance();
		common_Logger::d('Viewing '.$result->getLabel().' of type '.$clazz->getLabel());
        
        $formContainer = new tao_actions_form_Instance($clazz, $result);
		$myForm = $formContainer->getForm();
		$myForm->setActions(array(), 'top');
		$myForm->setActions(array());
		
		$variables = array();
		foreach ($this->service->getVariables($result) as $variable) {
			$values = $variable->getPropertiesValues(array(
				new core_kernel_classes_Property(PROPERTY_VARIABLE_IDENTIFIER),
				new core_kernel_classes_Property(RDF_VALUE),
				new core_kernel_classes_Property(RDF_TYPE),
				new core_kernel_classes_Property(PROPERTY_VARIABLE_ORIGIN),
			));
			$origin = array_pop($values[PROPERTY_VARIABLE_ORIGIN])->getUri();
			if (!isset($variables[$origin])) {
				$variables[$origin] = array(
					'vars' => array()
				);
			}
			$variables[$origin]['vars'][] = $values;
		}
		foreach ($variables as $origin => $data) {
			$ae = new core_kernel_classes_Resource($origin);
		}
		
		common_Logger::d('Variables '.count($variables));
		
        $this->setData('myForm', $myForm->render());
        $this->setData('variables', $variables);
        $this->setView('viewResult.tpl', false);
	}
	
    /*
     * conveniance methods
     */

    /**
     * get the instancee of the current subject regarding the 'uri' and 'classUri' request parameters
     * @return core_kernel_classes_Resource the result instance
     */
    protected function getCurrentInstance() {

        $uri = tao_helpers_Uri::decode($this->getRequestParameter('uri'));
        if (is_null($uri) || empty($uri)) {
            throw new Exception("No valid uri found");
        }

        $clazz = $this->getCurrentClass();

        $result = $this->service->getResult($uri, 'uri', $clazz);
        if (is_null($result)) {
            throw new common_Exception("No result found for the uri {$uri}");
        }

        return $result;
    }

    /**
     * get the main class
     * @return core_kernel_classes_Classes
     */
    protected function getRootClass() {
        return new core_kernel_classes_Class(TAO_DELIVERY_RESULT);
    }

    /*
     * controller actions
     */

    /**
     * delete a subject or a subject model
     * called via ajax
     * @return void
     */
    public function delete() {
        if (!tao_helpers_Request::isAjax()) {
            throw new Exception("wrong request mode");
        }

        $deleted = false;
        if ($this->getRequestParameter('uri')) {
            $deleted = $this->service->deleteResult($this->getCurrentInstance());
        } else {
            $deleted = $this->service->deleteResultClass($this->getCurrentClass());
        }

        echo json_encode(array('deleted' => $deleted));
    }

}
?>