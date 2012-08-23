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
    	$filter = $this->getRequestParameter('filter');
		$this->setData('filter', $filter);
		$this->setView('resultTable.tpl');
    }
    
    public function data() {
    	$filter = $this->getRequestParameter('filter');
    	$page = $this->getRequestParameter('page');
		$limit = $this->getRequestParameter('rows');
		$sidx = $this->getRequestParameter('sidx');
		$sord = $this->getRequestParameter('sord');
		$searchField = $this->getRequestParameter('searchField');
		$searchOper = $this->getRequestParameter('searchOper');
		$searchString = $this->getRequestParameter('searchString');
		$start = $limit * $page - $limit;
		
    	$response = new stdClass();
    	
    	if(!is_null($filter)){
    		
    		$clazz = new core_kernel_classes_Class(TAO_DELIVERY_RESULT);
    		$results	= $clazz->searchInstances($filter, array ('recursive'=>true));
    		$counti		= $clazz->countInstances($filter, array ('recursive'=>true));
    		
			foreach($results as $result){
				$cellData = array();
				$response->rows[] = array(
					'id' => tao_helpers_Uri::encode($result->uriResource),
					'cell' => $cellData
				);
			}
		}
		
		$response->page = $page;
		$response->total = ceil($counti / $limit);//$total_pages;
		$response->records = count($results);

		echo json_encode($response); 
    }
}
?>