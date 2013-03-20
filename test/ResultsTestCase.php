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
class ResultsTestCase extends UnitTestCase {
	
	/**
	 * 
	 * @var taoResults_models_classes_ResultsService
	 */
	protected $resultsService = null;
	
	//a stored response
	protected $grade = null;
	/**
	 * tests initialization
	 */
	public function setUp(){		
		TaoTestRunner::initTest();
		
		$resultsService = taoResults_models_classes_ResultsService::singleton();
		$this->resultsService = $resultsService;
		
		$activityExecution = new core_kernel_classes_Resource("#MyActivityExecution");
		$deliveryResult = new core_kernel_classes_Resource("#MyDeliveryResult");
		$variableIDentifier = "GRADE";
		$value = 0.4;
		$this->grade = $this->resultsService->storeGrade($deliveryResult,$activityExecution, $variableIDentifier, $value);

	}
	
	/**
	 * Test the user service implementation
	 * @see tao_models_classes_ServiceFactory::get
	 * @see taoResults_models_classes_ResultsService::__construct
	 */
	public function testService(){
		
		
		$this->assertIsA($this->resultsService, 'tao_models_classes_Service');
		$this->assertIsA($this->resultsService, 'taoResults_models_classes_ResultsService');
		
		
	}
		
	public function testStoreVariable(){
	     $this->assertIsA($this->grade, 'core_kernel_classes_Resource');
	     //$this->fail("Not implemented yet");
	    
	}
	
	public function testGetScoreVariables(){
	    
	    
	    $deliveryResult = new core_kernel_classes_Resource("#MyDeliveryResult");
	    
	    
	    $scoreVariables = $this->resultsService->getScoreVariables($deliveryResult);
	    
	    //tricky if the unit test fails, it probably means that there is some ghost data not correctly removed from previous executions 
	    $this->assertEqual(count($scoreVariables),1);
	     $variable = array_pop($scoreVariables);
	    
	     $this->assertIsA($variable, 'core_kernel_classes_Resource');
	    
	   
	    
	    $value = $variable->getUniquePropertyValue(new core_kernel_classes_Property(RDF_VALUE));
	    $variableIdentifier = $variable->getUniquePropertyValue(new core_kernel_classes_Property(PROPERTY_VARIABLE_IDENTIFIER));
    	    $variableOrigin = $variable->getUniquePropertyValue(new core_kernel_classes_Property(PROPERTY_VARIABLE_ORIGIN));
	    
	    $this->assertEqual($value,"0.4");
	    $this->assertEqual($variableIdentifier,"GRADE");
	    $this->assertEqual($variableOrigin->getUri(),"#MyActivityExecution");
	}
	
	public function testGetVariables(){
	    
	    
	    $deliveryResult = new core_kernel_classes_Resource("#MyDeliveryResult");
	    
	    
	    $scoreVariables = $this->resultsService->getVariables($deliveryResult);
	    
	    //tricky if the unit test fails, it probably means that there is some ghost data not correctly removed from previous executions 
	    $this->assertEqual(count($scoreVariables),1);
	     $variable = array_pop($scoreVariables);
	    
	     $this->assertIsA($variable, 'core_kernel_classes_Resource');
	    
	   
	    
	    $value = $variable->getUniquePropertyValue(new core_kernel_classes_Property(RDF_VALUE));
	    $variableIdentifier = $variable->getUniquePropertyValue(new core_kernel_classes_Property(PROPERTY_VARIABLE_IDENTIFIER));
    	    $variableOrigin = $variable->getUniquePropertyValue(new core_kernel_classes_Property(PROPERTY_VARIABLE_ORIGIN));
	    
	    $this->assertEqual($value,"0.4");
	    $this->assertEqual($variableIdentifier,"GRADE");
	    $this->assertEqual($variableOrigin->getUri(),"#MyActivityExecution");
	}
	
	public function testGetTestTaker(){
		 $deliveryResult = new core_kernel_classes_Resource("#MyDeliveryResult");
		 $deliveryResult->setPropertyValue(new core_kernel_classes_Property(PROPERTY_RESULT_OF_SUBJECT),"#Patrick");
		$testTaker = $this->resultsService->getTestTaker($deliveryResult);
		 $this->assertIsA($testTaker, 'core_kernel_classes_Resource');
		 $this->assertEqual($testTaker->getUri(),"#Patrick");
	}
	public function testGetVariableData(){
	    /*
	     * Needs to build an activity execution along the grade, etc.
	    $variableData = $this->resultsService->getVariableData($this->grade);
	    print_r($variableData);
	     * 
	    */
	    
	}
	
	
	public function tearDown(){
	    $this->assertTrue($this->grade->delete());
	}
}   
?>