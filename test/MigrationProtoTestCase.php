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
 * Copyright (c) 2008-2010 (original work) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 * 
 */
?>
<?php
require_once dirname(__FILE__) . '/../../tao/test/TaoTestRunner.php';
include_once dirname(__FILE__) . '/../includes/raw_start.php';

/**
 *
 * @author Patrick plichart, <patrick@taotesting.com>
 * @package taoResults
 * @subpackage test
 */
class MigrationProtoTestCase extends UnitTestCase {
	public function setUp(){		
		TaoTestRunner::initTest();
	}
	public function testMigrateAllResults(){
       
		$oldResultClass = new core_kernel_classes_Class("http://www.tao.lu/Ontologies/TAOResult.rdf#DeliveryResult");
        $oldResults = $oldResultClass->getInstances();
        foreach ($oldResults as $oldResult) {
                $this->migrate($oldResult);
            }
        }
    private function migrate(core_kernel_classes_Resource $oldResult){
        //$testTaker = $oldResult();
        //delivery
            //PROPERTY_RESULT_OF_DELIVERY
        $oldVariableClass = new core_kernel_classes_Class("http://www.tao.lu/Ontologies/TAOResult.rdf#Variable");
        $oldVariableInstances = $oldVariableClass->searchInstances(
            array('http://www.tao.lu/Ontologies/TAOResult.rdf#memberOfDeliveryResult' => $oldResult->getUri()),
            array("recursive" => true)
            );
        foreach ($oldVariableInstances as $oldVariableInstance) {
            $this->migrateVariable($oldResult, $oldVariableInstance);
        }
    }
    private function migrateVariable(core_kernel_classes_Resource $oldResult, $oldVariableInstance){
       
        //No changes brought to
        //RDF_VALUE						=> $itemVariable->getValue(),
        //PROPERTY_VARIABLE_EPOCH		=> microtime()

        //to be set
        
         $CallIdItem = $this->getRelatedItemAndCallId($oldVariableInstance);

         $itemResult = $this->getItemResult($oldResult,$CallIdItem[0], $CallIdItem[1]);
        var_dump( $itemResult);
        //links the variable to the itemResult
         $oldVariableInstance->editPropertyValues(new core_kernel_classes_Property(PROPERTY_RELATED_ITEM_RESULT), array($itemResult->getUri()) );


         //changed from http://www.tao.lu/Ontologies/TAOResult.rdf#variableIdentifier to PROPERTY_IDENTIFIER
        $oldIdentifier = $oldVariableInstance->getUniquePropertyValue(new core_kernel_classes_Property('http://www.tao.lu/Ontologies/TAOResult.rdf#variableIdentifier'));
        $oldVariableInstance->editPropertyValues(new core_kernel_classes_Property(PROPERTY_IDENTIFIER), array($oldIdentifier));

        $oldVariableInstance->removePropertyValues(new core_kernel_classes_Property('http://www.tao.lu/Ontologies/TAOResult.rdf#variableOrigin'));
        $oldVariableInstance->removePropertyValues(new core_kernel_classes_Property('http://www.tao.lu/Ontologies/TAOResult.rdf#memberOfDeliveryResult'));
        $oldVariableInstance->removePropertyValues(new core_kernel_classes_Property('http://www.tao.lu/Ontologies/TAOResult.rdf#variableIdentifier'));

        //set default values
        //PROPERTY_VARIABLE_CARDINALITY   => $itemVariable->getCardinality(),
        //PROPERTY_VARIABLE_BASETYPE      => $itemVariable->getBaseType(),

        //set outcome default values
        //PROPERTY_OUTCOME_VARIABLE_NORMALMAXIMUM => $itemVariable->getNormalMaximum(),
        //PROPERTY_OUTCOME_VARIABLE_NORMALMINIMUM => $itemVariable->getNormalMinimum(),

         //set response default
        //PROPERTY_RESPONSE_VARIABLE_CORRECTRESPONSE => $isCorrect,

         //useless
         //PROPERTY_RESPONSE_VARIABLE_CANDIDATERESPONSE=> $itemVariable->getCandidateResponse(),



    }

    private function getRelatedItemAndCallId(core_kernel_classes_Resource $oldVariableInstance){
            $activityInstanceOrigin = $oldVariableInstance->getUniquePropertyValue(new core_kernel_classes_Property("http://www.tao.lu/Ontologies/TAOResult.rdf#variableOrigin"));
            $callID= $activityInstanceOrigin->getUri();
            $activity  = $activityInstanceOrigin->getUniquePropertyValue(new core_kernel_classes_Property("http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivityExecutionsExecutionOf"));
            //$callInteractiveService = $activity->getUniquePropertyValue(new core_kernel_classes_Property("http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivitiesInteractiveServices"));
            //check all interactive services:
            $returnValue = "ItemRemoved";
			foreach ($activity->getPropertyValuesCollection(new core_kernel_classes_Property("http://www.tao.lu/middleware/wfEngine.rdf#PropertyActivitiesInteractiveServices"))->getIterator() as $iService){
				if($iService instanceof core_kernel_classes_Resource){
                    $serviceDefinition = $iService->getUniquePropertyValue(new core_kernel_classes_Property("http://www.tao.lu/middleware/wfEngine.rdf#PropertyCallOfServicesServiceDefinition"));
					if(!is_null($serviceDefinition)){

						if($serviceDefinition->getUri() == "http://www.tao.lu/Ontologies/TAODelivery.rdf#ServiceItemRunner"){
							foreach($iService->getPropertyValuesCollection(new core_kernel_classes_Property("http://www.tao.lu/middleware/wfEngine.rdf#PropertyCallOfServicesActualParameterin"))->getIterator() as $actualParam){
								
								$formalParam = $actualParam->getUniquePropertyValue(new core_kernel_classes_Property("http://www.tao.lu/middleware/wfEngine.rdf#PropertyActualParametersFormalParameter"));
                                try {
                                    if($formalParam->getUniquePropertyValue(new core_kernel_classes_Property("http://www.tao.lu/middleware/wfEngine.rdf#PropertyFormalParametersName")) == 'itemUri'){
                                        $item = $actualParam->getOnePropertyValue(new core_kernel_classes_Property("http://www.tao.lu/middleware/wfEngine.rdf#PropertyActualParametersConstantValue"));

                                        if(!is_null($item)){
                                            $returnValue = $item->getUri();
                                            break(2);
                                        }
                                    }
                                } catch (exception $e) {
                                    //the current formal aprameter does not reference the Item 
                                }
							}

						}
					}

				}
            }
            return array($callID, $returnValue);
    }
    private function getItemResult(core_kernel_classes_Resource $result, $callId, $item) {
        return taoResults_models_classes_ResultsService::singleton()->getItemResult($result, $callId, "tao2.4 Test", $item);
    }
}   
?>