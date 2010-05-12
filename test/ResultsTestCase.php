<?php
require_once dirname(__FILE__) . '/../../tao/test/TestRunner.php';

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
	
}
?>