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
 * Copyright (c) 2002-2008 (original work) Public Research Centre Henri Tudor & University of Luxembourg (under the project TAO & TAO2);
 *               2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 * 
 */
?>
<?php

error_reporting(E_ALL);

/**
 * TAO - taoResults/models/classes/class.ResultsService.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 20.08.2012, 15:22:19 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package taoResults
 * @subpackage models_classes
 */


/**
 * Service methods to manage the Results business models using the RDF API.
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
 */
//being deprecated
//require_once('taoResults/models/classes/class.LegacyResultsService.php');

/* user defined includes */
// section 127-0-1-1-16e239f7:13925739ce2:-8000:0000000000003B72-includes begin
// section 127-0-1-1-16e239f7:13925739ce2:-8000:0000000000003B72-includes end

/* user defined constants */
// section 127-0-1-1-16e239f7:13925739ce2:-8000:0000000000003B72-constants begin
// section 127-0-1-1-16e239f7:13925739ce2:-8000:0000000000003B72-constants end

/**
 * Short description of class taoResults_models_classes_ResultsService
 *
 * @access public
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package taoResults
 * @subpackage models_classes
 */
class taoResults_models_classes_ResultsService
     extends tao_models_classes_ClassService
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---
    public function getRootClass()
    {	
		return new core_kernel_classes_Class(TAO_DELIVERY_RESULT);
    }
    /**
     * Short description of method getVariables
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  Resource deliveryResult
     * @return array
     */
    public function getVariables( core_kernel_classes_Resource $deliveryResult)
    {
        $returnValue = array();

        // section 127-0-1-1-16e239f7:13925739ce2:-8000:0000000000003B74 begin
    	$type = new core_kernel_classes_Class(TAO_RESULT_VARIABLE); //TAO_RESULT_GRADE
        $returnValue = $type->searchInstances(
        	array(PROPERTY_MEMBER_OF_RESULT	=> $deliveryResult->getUri()),
        	array('recursive' => true, 'like' => false)
        );
        // section 127-0-1-1-16e239f7:13925739ce2:-8000:0000000000003B74 end

        return (array) $returnValue;
    }
    /**
     * Return the corresponding delivery 
     * @param core_kernel_classes_Resource $deliveryResult
     * @return core_kernel_classes_Resource delviery
     * @author Patrick Plichart, <patrick@taotesting.com>
     */
    public function getDelivery(core_kernel_classes_Resource $deliveryResult){
	return $deliveryResult->getUniquePropertyValue(new core_kernel_classes_Property(PROPERTY_RESULT_OF_DELIVERY));
    }
    /**
     * Retrieves all score variables pertaining to the deliveryResult
     *
     * @access public
     * @author Patrick Plichart, <patrick.plichart@taotesting.com>
     * @param  Resource deliveryResult
     * @return array
     */
    public function getScoreVariables( core_kernel_classes_Resource $deliveryResult)
    {
        $returnValue = array();

        // section 127-0-1-1-16e239f7:13925739ce2:-8000:0000000000003B74 begin
    	$type = new core_kernel_classes_Class(TAO_RESULT_GRADE); //TAO_RESULT_GRADE
        $returnValue = $type->searchInstances(
        	array(PROPERTY_MEMBER_OF_RESULT	=> $deliveryResult->getUri()),
		array('recursive' => true, 'like' => false)
        );
        // section 127-0-1-1-16e239f7:13925739ce2:-8000:0000000000003B74 end

        return (array) $returnValue;
    }
    
    /**
     * 
     */
    /**
     * Retrieves information about the variable, including the related item
     *      * @access public
     * @author Patrick Plichart, <patrick.plichart@taotesting.com>
     * @param  Resource variable
     * @return array simple associative$returnValue = taoTests_models_classes_TestAuthoringService::singleton()->getItemByActivity($activityClass);
     */
    public function getVariableData( core_kernel_classes_Resource $variable)
    {
        $returnValue = array();
	$returnValue["value"] = $variable->getUniquePropertyValue(new core_kernel_classes_Property(RDF_VALUE))->__toString();
	$returnValue["variableIdentifier"] = $variable->getUniquePropertyValue(new core_kernel_classes_Property(PROPERTY_VARIABLE_IDENTIFIER));

	//$returnValue["epoch"] = $variable->getUniquePropertyValue(new core_kernel_classes_Property(PROPERTY_VARIABLE_EPOCH));

	//identify the item related to the score Variable
	$variableOrigin = $variable->getUniquePropertyValue(new core_kernel_classes_Property(PROPERTY_VARIABLE_ORIGIN));
	$activityDefinition = $variableOrigin->getUniquePropertyValue(new core_kernel_classes_Property(PROPERTY_ACTIVITY_EXECUTION_ACTIVITY));
	$returnValue["item"] = taoTests_models_classes_TestAuthoringService::singleton()->getItemByActivity($activityDefinition);
	
        return (array) $returnValue;
    }
    
    
    /**
     * returns the test taker related to the delivery
     *
     * @author Patrick Plichart, <patrick.plichart@taotesting.com>
     */
    public function getTestTaker( core_kernel_classes_Resource $deliveryResult)
    {
        $returnValue = array();
	
	$propResultOfSubject = new core_kernel_classes_Property(PROPERTY_RESULT_OF_SUBJECT);
	$testTaker = $deliveryResult->getPropertyValues($propResultOfSubject);
	
       return new core_kernel_classes_Resource(array_pop($testTaker));
    }
    /**
     * Short description of method storeVariable
     *
     * @access protected
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  Class type
     * @param  Resource deliveryResult
     * @param  Resource activityExecution
     * @param  string identifier
     * @param  string value
     * @return core_kernel_classes_Resource$returnValue = taoTests_models_classes_TestAuthoringService::singleton()->getItemByActivity($activityClass);
     */
    protected function storeVariable( core_kernel_classes_Class $type,  core_kernel_classes_Resource $deliveryResult,  core_kernel_classes_Resource $activityExecution, $identifier, $value)
    {
        $returnValue = null;

        // section 127-0-1-1-6befba6b:1394401f373:-8000:0000000000003B77 begin
        $returnValue = $type->createInstanceWithProperties(array(
			PROPERTY_MEMBER_OF_RESULT		=> $deliveryResult,
			PROPERTY_VARIABLE_ORIGIN		=> $activityExecution,
			PROPERTY_VARIABLE_IDENTIFIER	=> $identifier,
			RDF_VALUE						=> $value,
			PROPERTY_VARIABLE_EPOCH		=> time()
		));
        // section 127-0-1-1-6befba6b:1394401f373:-8000:0000000000003B77 end

        return $returnValue;
    }
    
    /**
     * Short description of method storeResponse
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  Resource deliveryResult
     * @param  Resource activityExecution
     * @param  string identifier
     * @param  string value
     * @return core_kernel_classes_Resource
     */
    public function storeResponse( core_kernel_classes_Resource $deliveryResult,  core_kernel_classes_Resource $activityExecution, $identifier, $value)
    {
        $returnValue = null;

        // section 127-0-1-1-6befba6b:1394401f373:-8000:0000000000003B7E begin
        $returnValue = $this->storeVariable(
        	new core_kernel_classes_Class(TAO_RESULT_RESPONSE)
        	, $deliveryResult, $activityExecution, $identifier, $value
        );
        // section 127-0-1-1-6befba6b:1394401f373:-8000:0000000000003B7E end

        return $returnValue;
    }

    /**
     * Short description of method storeGrade
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  Resource deliveryResult
     * @param  Resource activityExecution
     * @param  string identifier
     * @param  string value
     * @return core_kernel_classes_Resource
     */
    public function storeGrade( core_kernel_classes_Resource $deliveryResult,  core_kernel_classes_Resource $activityExecution, $identifier, $value)
    {
        $returnValue = null;

        // section 127-0-1-1-6befba6b:1394401f373:-8000:0000000000003B80 begin
        $returnValue = $this->storeVariable(
        	new core_kernel_classes_Class(TAO_RESULT_GRADE)
        	, $deliveryResult, $activityExecution, $identifier, $value
        );
        // section 127-0-1-1-6befba6b:1394401f373:-8000:0000000000003B80 end

        return $returnValue;
    }
	
	/**
     * Short description of method deleteResult
     *
   
     */
    public function deleteResult( core_kernel_classes_Resource $result)
    {
        $returnValue = (bool) false;

        // section 10-13-1-39-5129ca57:1276133a327:-8000:000000000000204D begin
		if(!is_null($result)){
			$returnValue = $result->delete();
		}
        // section 10-13-1-39-5129ca57:1276133a327:-8000:000000000000204D end

        return (bool) $returnValue;
    }

    /**
     * Short description of method deleteResultClass
     *
     */
    public function deleteResultClass( core_kernel_classes_Class $clazz)
    {
        $returnValue = (bool) false;
     
		if(!is_null($clazz)){
				$returnValue = $clazz->delete();
		}
       return (bool) $returnValue;
    }
	

} /* end of class taoResults_models_classes_ResultsService */

?>