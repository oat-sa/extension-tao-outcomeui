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

//todo ppl move the setup to an helper
class SimpleReportTestCase extends ResultsTestCase {
	
	/**
	 * 
	 * @var taoResults_models_classes_StatisticsService
	 */
	private $statsService = null;
	
	/**
	 * 
	 * @var taoResults_models_classes_ReportService
	 */
	private $reportService = null;
	
	/**
	 * the data set produced by the statistics service
	 */
	private $dataSet;
	/**
	 * tests initialization
	 */
	public function setUp(){		
		TaoTestRunner::initTest();
		$this->statsService = taoResults_models_classes_StatisticsService::singleton();
		$this->reportService = taoResults_models_classes_ReportService::singleton();
		// The unit test initiate a delivery with a grade and a response, should move to an helper, lokos like the fw is running again all tests with reflection
		parent::setUp();

	}
	
	/**
	** @see tao_models_classes_ServiceFactory::get
	 * @see taoResults_models_classes_ResultsService::__construct
	 */
	public function testService(){
		$this->assertIsA($this->statsService, 'taoResults_models_classes_StatisticsService');
		$this->assertIsA($this->reportService, 'taoResults_models_classes_ReportService');
	}
		
	public function testExtractDeliveryDataSet(){
		$dataSet = $this->statsService->extractDeliveryDataSet($this->subClass);
		$this->dataSet = $dataSet;
		$this->assertEqual($dataSet["nbExecutions"],1);
		$this->assertEqual(count($dataSet["statisticsPerVariable"]),1);
		$this->assertEqual(count($dataSet["statisticsPerVariable"]["GRADE"]),6);
		$this->assertEqual($dataSet["statisticsPerVariable"]["GRADE"]["sum"],0.4);
		$this->assertEqual($dataSet["statisticsPerVariable"]["GRADE"]["#"],1);
		$this->assertEqual($dataSet["statisticsPerVariable"]["GRADE"]["data"],array(0.4));
		$this->assertEqual($dataSet["statisticsPerVariable"]["GRADE"]["naturalid"], " (GRADE)");
		$this->assertEqual($dataSet["statisticsPerVariable"]["GRADE"]["avg"], 0.4);
		$this->assertEqual(count($dataSet["statisticsPerVariable"]["GRADE"]["splitData"]), 1);
		$this->assertEqual(count($dataSet["statisticsPerVariable"]["GRADE"]["splitData"][1]), 3);
		$this->assertEqual($dataSet["statisticsPerVariable"]["GRADE"]["splitData"][1]["sum"], 0.4);
		$this->assertEqual($dataSet["statisticsPerVariable"]["GRADE"]["splitData"][1]["avg"], 0.4);
		$this->assertEqual($dataSet["statisticsPerVariable"]["GRADE"]["splitData"][1]["#"], 1);
		
		$this->assertEqual($dataSet["statistics"]["sum"],0.4);
		$this->assertEqual($dataSet["statistics"]["#"],1);
		$this->assertEqual($dataSet["statistics"]["data"],array(0.4));
		$this->assertEqual(count($dataSet["statistics"]["splitData"]), 1);
		$this->assertEqual(count($dataSet["statistics"]["splitData"][1]), 3);
		$this->assertEqual($dataSet["statistics"]["splitData"][1]["sum"], 0.4);
		$this->assertEqual($dataSet["statistics"]["splitData"][1]["avg"], 0.4);
		$this->assertEqual($dataSet["statistics"]["splitData"][1]["#"], 1);
		$this->assertEqual($dataSet["statistics"]["avg"], 0.4);
		
	}
	
	public function testSetDataSet(){
	$this->reportService->setDataSet($this->dataSet);
	    
	}
	
	public function testBuildSimpleReport(){
	    //problem with the graph generation, not tested for the moment
	    //$report = $this->reportService->buildSimpleReport();
	    
	}
	
	
	
	
	public function tearDown(){
	    parent::tearDown();
	}
	
	}
}   
?>