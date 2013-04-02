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

  
   
    public function getResponseColumns() {
	$this->getVariableColumns(TAO_RESULT_RESPONSE);
    }
    /** Returns all columns with all grades pertaining to the current delivery results selection
     */
     public function getGradeColumns() {
              $this->getVariableColumns(TAO_RESULT_GRADE);
    }
      /**Retrieve the different variables columns pertainign to the current selection of results
     * Implementation note : it nalyses all the data collected to identify the different response variables submitted by the items in the context of activities
     */
    public function getVariableColumns($variableClassUri) {

		$columns = array();
		$filter = $this->getFilterState('filter');
		$deliveryResultClass	= new core_kernel_classes_Class(TAO_DELIVERY_RESULT);

		//The list of delivery Results matching the current selection filters
		$results	= $deliveryResultClass->searchInstances($filter, array ('recursive'=>true));

		//retrieveing all individual response variables referring to the  selected delivery results
		$selectedResponseVariables = array ();
		foreach ($results as $result){
		$responseVariableClass = new core_kernel_classes_Class($variableClassUri);
		$responseVariables = $responseVariableClass->searchInstances( array(PROPERTY_MEMBER_OF_RESULT=> $result->getUri()));
		$selectedResponseVariables = array_merge($selectedResponseVariables, $responseVariables);
		}
		//retrieving The list of the variables identifiers per activities defintions as observed
		$variableTypesPerActivity = array();
		foreach ($selectedResponseVariables as $responseVariable)
		    {
			//activityExecution
			$activityExecutionOriginProperty = new core_kernel_classes_Property(PROPERTY_VARIABLE_ORIGIN);
			$activityExecution = $responseVariable->getUniquePropertyValue($activityExecutionOriginProperty);
			$activityDefinitionProperty = new core_kernel_classes_Property(PROPERTY_ACTIVITY_EXECUTION_ACTIVITY);
			$activityDefinition = $activityExecution->getUniquePropertyValue($activityDefinitionProperty);

			//variableIdentifier
			$variableIdentifierProperty = new core_kernel_classes_Property(PROPERTY_VARIABLE_IDENTIFIER);
			$variableIdentifier = $responseVariable->getUniquePropertyValue($variableIdentifierProperty);

			//feeding our list of variables per activities, and merge them.
			$variableTypesPerActivity[$activityDefinition->getUri()."_".$variableIdentifier] = array("activityDefinition" => $activityDefinition, "variableIdentifier" => $variableIdentifier->literal);

			/*
			$measurements = taoItems_models_classes_ItemsService::singleton()->getItemMeasurements($item);
				foreach ($measurements as $measurement) {
					$columns[] = new taoResults_models_classes_table_GradeColumn($activity, $measurement->getIdentifier());
				}
			 * 
			 */
		    }

		foreach ($variableTypesPerActivity as $variable){
		    //should be fine grained at the level of the variable
		    switch ($variableClassUri){
			case TAO_RESULT_RESPONSE:{ $columns[] = new taoResults_models_classes_table_ResponseColumn($variable["activityDefinition"], $variable["variableIdentifier"]);break;}
			case TAO_RESULT_GRADE: { $columns[] = new taoResults_models_classes_table_GradeColumn($variable["activityDefinition"], $variable["variableIdentifier"]);break;}
			default:{$columns[] = new taoResults_models_classes_table_ResponseColumn($variable["activityDefinition"], $variable["variableIdentifier"]);}
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