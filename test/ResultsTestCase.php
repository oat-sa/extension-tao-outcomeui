<?php
require_once dirname(__FILE__) . '/../../tao/test/TestRunner.php';
include_once dirname(__FILE__) . '/bootstrap.php';

/**
 *
 * @author Bertrand Chevrier, <taosupport@tudor.lu>
 * @package taoResults
 * @subpackage test
 */
class ResultsTestCase extends UnitTestCase {
	
	/**
	 * 
	 * @var taoResults_models_classes_ResultsService
	 */
	protected $resultsService = null;
	
	/**
	 * tests initialization
	 */
	public function setUp(){		
		TestRunner::initTest();
	}
	
	/**
	 * Test the user service implementation
	 * @see tao_models_classes_ServiceFactory::get
	 * @see taoResults_models_classes_ResultsService::__construct
	 */
	public function testService(){
		
		$resultsService = tao_models_classes_ServiceFactory::get('Results');
		$this->assertIsA($resultsService, 'tao_models_classes_Service');
		$this->assertIsA($resultsService, 'taoResults_models_classes_ResultsService');
		
		$this->resultsService = $resultsService;
	}
	
	public function testDtis(){
		
		$dtisUris = array();
		$dtisUris["TAO_PROCESS_EXEC_ID"] 	= "http://localhost/middleware/taoqti__rdf#iproc3";
		$dtisUris["TAO_DELIVERY_ID"] 		= "http://localhost/middleware/taoqti__rdf#delivery2";
		$dtisUris["TAO_TEST_ID"] 			= "http://localhost/middleware/taoqti__rdf#test1";
		$dtisUris["TAO_ITEM_ID"] 			= "http://localhost/middleware/taoqti__rdf#item1";
		$dtisUris["TAO_SUBJECT_ID"]		= "http://localhost/middleware/taoqti__rdf#subject1";
		// the variable infos
		$key = "test";
		$value = "test";
	
		$instance = $this->resultsService->addResultVariable($dtisUris, $key, $value);
	
		$this->assertNotNull($instance);
		$this->assertIsA($instance, 'core_kernel_classes_Resource');
		
		$this->assertTrue($instance->delete());
	}
	
}
?>