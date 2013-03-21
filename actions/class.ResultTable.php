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


/**
 * Results Controller provide actions performed from url resolution
 * 
 * @author Joel Bout <joel@taotesting.com>
 * @author Patrick Plichart <patrick@taotesting.com>
 * @package taoResults
 * @subpackage actions
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * 
 */
class taoResults_actions_ResultTable extends tao_actions_Table {

    /**
     * constructor: initialize the service and the default data
     * @return Results
     */
    public function __construct() {

        parent::__construct();
    }

    /**
     * get the main class
     * @return core_kernel_classes_Classes
     */
    public function index() {
    	$filter = $this->getRequestParameter('filter');
		$this->setData('filter', $filter);
		$this->setView('resultTable.tpl');
    }
    
    /**
     * Relies on two optionnal parameters, 
     * - filters (facet based query) ($this->hasRequestParameter('filter'))
     * - the list of columns currently selected on the frontend side ($this->hasRequestParameter('columns'))
     * @return void - a csv string is being sent out by parent class -> data method into the buffer
     */
    public function getCsvFile(){
        //This action 
         $this->data("csv");
    }
    public function getGradeColumns() {
		
                $columns = array();
		
		$filter = $this->getFilterState('filter');
    	$clazz		= new core_kernel_classes_Class(TAO_DELIVERY_RESULT);
		$results	= $clazz->searchInstances($filter, array ('recursive'=>true));
		
		$deliveries = array();
		foreach ($results as $result) {
			$deliveries[] = $result->getUniquePropertyValue(new core_kernel_classes_Property(PROPERTY_RESULT_OF_DELIVERY))->getUri();
		}
		$deliveries = array_unique($deliveries);
		
		foreach ($deliveries as $deliveryUri) {
			$delivery = new core_kernel_classes_Resource($deliveryUri);
			$procDef = $delivery->getUniquePropertyValue(new core_kernel_classes_Property(TAO_DELIVERY_PROCESS));
			$activities = $procDef->getPropertyValues(new core_kernel_classes_Property(PROPERTY_PROCESS_ACTIVITIES));
			
			foreach ($activities as $activityUri) {
				// build label
				$activity = new core_kernel_classes_Resource($activityUri);
				$item = taoTests_models_classes_TestAuthoringService::singleton()->getItemByActivity($activity);
				$measurements = taoItems_models_classes_ItemsService::singleton()->getItemMeasurements($item);
				foreach ($measurements as $measurement) {
					$columns[] = new taoResults_models_classes_table_GradeColumn($activity, $measurement->getIdentifier());
				}
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
     * Returns the default column selection that contains the Result of Subject property (This has been removed from the other commodity function adding grades and responses) 
     */
    public function getResultOfSubjectColumn(){
        
		$testtaker = new tao_models_classes_table_PropertyColumn(new core_kernel_classes_Property(PROPERTY_RESULT_OF_SUBJECT));
		$arr[] = $testtaker->toArray();
                echo json_encode(array(
                        'columns' => $arr
                ));
    }
    /**
     * under development
     */
    public function getResponseColumns() {
		
		
		$columns = array();
		
		$filter = $this->getFilterState('filter');
    	$clazz		= new core_kernel_classes_Class(TAO_DELIVERY_RESULT);
		$results	= $clazz->searchInstances($filter, array ('recursive'=>true));
		
		$deliveries = array();
		foreach ($results as $result) {
			$deliveries[] = $result->getUniquePropertyValue(new core_kernel_classes_Property(PROPERTY_RESULT_OF_DELIVERY))->getUri();
		}
		$deliveries = array_unique($deliveries);
		
		foreach ($deliveries as $deliveryUri) {
			$delivery = new core_kernel_classes_Resource($deliveryUri);
			$procDef = $delivery->getUniquePropertyValue(new core_kernel_classes_Property(TAO_DELIVERY_PROCESS));
			$activities = $procDef->getPropertyValues(new core_kernel_classes_Property(PROPERTY_PROCESS_ACTIVITIES));
			
			foreach ($activities as $activityUri) {
				$activity = new core_kernel_classes_Resource($activityUri);
				$columns[] = new taoResults_models_classes_table_ResponseColumn($activity, 'RESPONSE');
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
}
?>