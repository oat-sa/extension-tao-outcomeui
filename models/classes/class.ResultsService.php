<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API -
 *
 * $Id$
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 14.09.2009, 15:14:24 with ArgoUML PHP module 
 * (last revised $Date: 2008-04-19 08:22:08 +0200 (Sat, 19 Apr 2008) $)
 *
 * @author Bertrand Chevrier, <taosupport@tudor.lu>
 * @package taoResults
 * @subpackage models_classes
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * The Service class is an abstraction of each service instance. 
 * Used to centralize the behavior related to every servcie instances.
 *
 * @author Bertrand Chevrier, <taosupport@tudor.lu>
 */
require_once('tao/models/classes/class.Service.php');

/* user defined includes */
// section 10-13-1-45-792423e0:12398d13f24:-8000:00000000000017F0-includes begin
// section 10-13-1-45-792423e0:12398d13f24:-8000:00000000000017F0-includes end

/* user defined constants */
// section 10-13-1-45-792423e0:12398d13f24:-8000:00000000000017F0-constants begin
// section 10-13-1-45-792423e0:12398d13f24:-8000:00000000000017F0-constants end

/**
 * Short description of class taoResults_models_classes_ResultsService
 *
 * @access public
 * @author Bertrand Chevrier, <taosupport@tudor.lu>
 * @package taoResults
 * @subpackage models_classes
 */
class taoResults_models_classes_ResultsService
    extends tao_models_classes_Service
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method getResultsByGroup
     *
     * @access protected
     * @author Bertrand Chevrier, <taosupport@tudor.lu>
     * @param  Resource group
     * @return core_kernel_classes_ContainerCollection
     */
    protected function getResultsByGroup( core_kernel_classes_Resource $group)
    {
        $returnValue = null;

        // section 10-13-1-45-792423e0:12398d13f24:-8000:00000000000017F3 begin
        // section 10-13-1-45-792423e0:12398d13f24:-8000:00000000000017F3 end

        return $returnValue;
    }

    /**
     * Short description of method getResults
     *
     * @access public
     * @author Bertrand Chevrier, <taosupport@tudor.lu>
     * @param  array options
     * @return core_kernel_classes_ContainerCollection
     */
    public function getResults($options)
    {
        $returnValue = null;

        // section 10-13-1-45-792423e0:12398d13f24:-8000:00000000000017CB begin
        // section 10-13-1-45-792423e0:12398d13f24:-8000:00000000000017CB end

        return $returnValue;
    }

    /**
     * Short description of method analyseResult
     *
     * @access public
     * @author Bertrand Chevrier, <taosupport@tudor.lu>
     * @param  Resource result
     * @param  Resource analyseFilter
     * @return core_kernel_classes_Resource
     */
    public function analyseResult( core_kernel_classes_Resource $result,  core_kernel_classes_Resource $analyseFilter)
    {
        $returnValue = null;

        // section 10-13-1-45-792423e0:12398d13f24:-8000:00000000000017F9 begin
        // section 10-13-1-45-792423e0:12398d13f24:-8000:00000000000017F9 end

        return $returnValue;
    }

    /**
     * Short description of method deleteResult
     *
     * @access public
     * @author Bertrand Chevrier, <taosupport@tudor.lu>
     * @param  Resource result
     * @return boolean
     */
    public function deleteResult( core_kernel_classes_Resource $result)
    {
        $returnValue = (bool) false;

        // section 10-13-1-45-792423e0:12398d13f24:-8000:00000000000017F6 begin
        // section 10-13-1-45-792423e0:12398d13f24:-8000:00000000000017F6 end

        return (bool) $returnValue;
    }

} /* end of class taoResults_models_classes_ResultsService */

?>