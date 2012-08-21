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

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * Service methods to manage the Results business models using the RDF API.
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
 */
require_once('taoResults/models/classes/class.LegacyResultsService.php');

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
    extends taoResults_models_classes_LegacyResultsService
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

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
     * Short description of method storeVariable
     *
     * @access protected
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  Class type
     * @param  Resource deliveryResult
     * @param  Resource activityExecution
     * @param  string identifier
     * @param  string value
     * @return core_kernel_classes_Resource
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

} /* end of class taoResults_models_classes_ResultsService */

?>