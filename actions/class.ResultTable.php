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
class taoResults_actions_ResultTable extends tao_actions_TaoModule {

    /**
     * constructor: initialize the service and the default data
     * @return Results
     */
    public function __construct() {

        parent::__construct();
    }

    protected function getRootClass() {
    	throw new common_exception_Error('getRootClass should never be called');
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
    public function index() {
    }
}
?>