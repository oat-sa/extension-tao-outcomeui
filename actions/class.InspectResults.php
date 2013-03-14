<?php
/*  
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
?>
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
class taoResults_actions_InspectResults extends tao_actions_TaoModule {

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
		
		$filter = $this->getFilterState('filter');
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
			$item = taoCoding_models_classes_CodingService::singleton()->getItemByActivityExecution($ae);
			$variables[$origin]['label'] = $item->getLabel();
		}
		
        $this->setData('myForm', $myForm->render());
        $this->setData('variables', $variables);
        $this->setView('viewResult.tpl');
	}
	
    /*
     * conveniance methods
     */


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