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
 *               2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);\n *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 * 
 */


require_once('tao/lib/pChart/pData.class');
require_once ('tao/lib/pChart/pChart.class');

/**
 * TAO - taoResults/models/classes/class.ReportService.php
 *
 * $Id$
 *
 *
 * Automatically generated on 20.08.2012, 15:22:19 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Patrick Plichart, <patrick.plichart@taotesting.com>
 * @package taoResults
 * @subpackage models_classes
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}


class taoResults_models_classes_ReportService
extends taoResults_models_classes_StatisticsService
{
	protected $deliveryDataSet = null;
	
	protected $contextClass;
	
	public function setDataSet($deliveryDataSet) {
	$this->deliveryDataSet = $deliveryDataSet;
	}
	
	public function setContextClass($contextClass) {
	$this->contextClass = $contextClass;
	}
	
	public function buildSimpleReport(){	

		$deliveryDataSet = $this->deliveryDataSet;
		//deprecated
		//$urlDeliverybarChart = $this->computeBarChart($this->deliveryDataSet["statisticsPerDelivery"]["splitData"], "Average and Total Scores by deciles of the population (".$this->contextClass->getLabel().")");
		
		$reportData['deliveryBarChart'] = $urlDeliverybarChart;
		
		$reportData['reportTitle'] = 'Statistical Report ('.$this->contextClass->getLabel().')';
		$reportData['average'] =  $this->deliveryDataSet["statisticsPerDelivery"]["avg"];
		$reportData['std'] =  $this->deliveryDataSet["statisticsPerDelivery"]["std"];
		$reportData['nbExecutions'] =  $this->deliveryDataSet["nbExecutions"];
		$reportData['#'] =  $this->deliveryDataSet["statisticsPerDelivery"]["#"];
		$reportData['numberVariables'] =  $this->deliveryDataSet["statisticsPerDelivery"]["numberVariables"];	
		$reportData['numberOfDistinctTestTaker'] =  count($this->deliveryDataSet["statisticsPerDelivery"]["distinctTestTaker"]);
		
		foreach ($this->deliveryDataSet["statisticsPerVariable"] as $variableIdentifier => $struct){
		
		$scoreVariableLabel = $struct["naturalid"];
		
		//compute every single distribution for each variable
		    //$urlDeliveryVariablebarChartQuantiles = $this->computeBarChart($this->deliveryDataSet["statisticsPerVariable"][$variableIdentifier]["splitData"], "Average and Total Scores by deciles of the population (".$scoreVariableLabel.")");
		
		$urlDeliveryVariablebarChartScores = $this->computeBarChartScores($this->deliveryDataSet["statisticsPerVariable"][$variableIdentifier]["data"], "Sorted Collected Scores for the variable : ".$scoreVariableLabel."");
		$urlDeliveryVariablebarChartScoresFequencies = $this->computebarChartScoresFrequencies($this->deliveryDataSet["statisticsPerVariable"][$variableIdentifier]["data"], "Grouped Scores Frequencies (".$scoreVariableLabel.")");
		
		
		//build UX data structure		
		$listOfVariables[]= array("label" => $scoreVariableLabel, "urlFrequencies"=>$urlDeliveryVariablebarChartScoresFequencies, "urlScores"=> $urlDeliveryVariablebarChartScores, "urlQuantileDistrib" => $urlDeliveryVariablebarChartQuantiles, "infos" => array("#" => $struct["#"], "sum" => $struct["sum"], "avg" => $struct["avg"]));
		
		//build parallel arrays to maintain values for the graph computation showing all variables
		$labels[] = $scoreVariableLabel;
		$sums[] = $struct["sum"];
		$avgs[] = $struct["avg"];
		}
		
		$reportData['listOfVariables'] =  $listOfVariables;	
		//$urlDeliveryVariableRadarPlot = $this->computeRadarPlot($sums,$avgs,$labels, "Scores by variables");
		//$this->setData('compareVariablesPlot', $urlDeliveryVariableRadarPlot);
		return $reportData;
		
	}
	/**
	 * @author Patrick plichart
	 * @param array $dataSet array of scores 
	 * @param string $title
	 * @return string the url of the generated graph
	 */
	private function computebarChartScores($dataSet, $title){
	    $datay = $dataSet;
	    $datax = array(); for ($i=0; $i < count($dataSet); $i++) {$datax[] = "#";}
	    $legendTitle = __("Score per Observation");
	    return $this->getChart($datax, array($legendTitle => $datay), $title, "Observations", "Score");
	}
	
	/**
	 * @author Patrick plichart
	 * @param array $dataSet array of scores 
	 * @param string $title
	 * @return string the url of the generated graph
	 */
	private function computebarChartScoresFrequencies($dataSet, $title){
	     
	    $datax = array();
	    $datay = array();
	    //thanks php
	    $frequencies = array_count_values($dataSet);
	    foreach ($frequencies as $value => $frequency){
		$datax[] = $value;
		$datay[] = $frequency;
	    }
	    $legendTitle = __("Frequency per Score");
	    return $this->getChart($datax, array($legendTitle => $datay), $title, "Score","Frequency (#)");
	}
	/**
	 * @author Patrick plichart
	 * @param array $datax	a flat sery of x labels
	 * @param array $setOfySeries	an array of y series to be drawn (needs to be consistent with xsery), keys indicates the legend title
	 * @param string $title the title of the graph
	 * @return string the url of the generated graph
	 */
	
	private function getChart($datax, $setOfySeries, $title, $xAxisLabel = "", $yAxisLabel=""){
	    $font = ROOT_PATH."/tao/lib/pChart/Fonts/pf_arma_five.ttf";
	  // Dataset definition 
	$DataSet = new pData;
	foreach ($setOfySeries as $legend => $ysery ){
	    $DataSet->AddPoint($ysery,$legend);
	    $DataSet->SetSerieName($legend,$legend);
	}
	$DataSet->AddAllSeries();
	$DataSet->AddPoint($datax,"xLabels");
	
	$DataSet->SetYAxisName($yAxisLabel);
	$DataSet->SetXAxisName($xAxisLabel);
	
	$DataSet->SetAbsciseLabelSerie("xLabels");
	// Initialise the graph
	$Test = new pChart(655,260);
	$Test->setFontProperties($font,10);
	
	$Test->setGraphArea(65,40,580,200);
	//draw the background rectangle
	$Test->drawFilledRoundedRectangle(7,7,655,253,5,240,240,240);
	
	$Test->drawRoundedRectangle(5,5,655,225,5,230,230,230);
	$Test->drawGraphArea(255,255,255,TRUE);
	$Test->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_NORMAL,150,150,150,TRUE,0,2,TRUE);   
	$Test->drawGrid(4,TRUE,230,230,230,50);

	// Draw the 0 line
	$Test->setFontProperties($font,6);
	$Test->drawTreshold(0,143,55,72,TRUE,TRUE);

	// Draw the bar graph
	$Test->drawBarGraph($DataSet->GetData(),$DataSet->GetDataDescription(),TRUE);

	// Finish the graph
	$Test->setFontProperties($font,8);
	$Test->drawLegend(480,220,$DataSet->GetDataDescription(),255,255,255);
	$Test->setFontProperties($font,10);
	$Test->drawTitle(50,30,$title,50,80,50,585);
	      $url = $this->getUniqueMediaFileName("png");
	      $Test->Render(ROOT_PATH.$url);
	      return ROOT_URL.$url;
	    
	    /*
	    
	    $graph = new Graph(550,200,'auto');
	    $graph->SetScale("textlin");
	    $graph->SetBox(false);
	    $graph->xaxis->SetTickLabels($datax);
	    $plots = array();
	    foreach ($setOfySeries as $legend => $ySery){
	    $b1plot = new BarPlot($ySery);
	    $b1plot->SetColor($colors[0]);
	    $b1plot->SetFillColor($colors[1]);
	    $b1plot->SetLegend($legend);
	    $plots[] = $b1plot;
	    }
	    $gbplot = new GroupBarPlot($plots);
	    $graph->Add($gbplot);
	    
           $graph->title->Set($title);
		$url = $this->getUniqueMediaFileName("png");
		// Display the graph
		$graph->Stroke(ROOT_PATH.$url);
		return ROOT_URL.$url;*/
	}
	
	/**
	*TODO move to an helper, attempt to get a unique file name
	*/
	private function getUniqueMediaFileName($fileExtension="")
		{	//rofl
			$id = rand(0,65535);
			$fileName = base64_encode("sid_".session_id()."c_".$this->contextClass->getUri()).$id.'.'.$fileExtension;
			return "taoResults/views/genpics/".$fileName;
		}
	/**
	* deprecated
	
	private function computebarChart($dataSet, $title){
		
		$data1y = $this->flattenQuantiles($dataSet, "avg");
		//print_r($data1y);
		$data2y = $this->flattenQuantiles($dataSet, "sum");
		// Create the graph. These two calls are always required
		$graph = new Graph(550,200,'auto');
		$graph->SetScale("textlin");
		$graph->SetBox(false);
		$graph->xaxis->SetTickLabels(array('0-10 %',' 0-20 %','20-30 %','30-40 %','40-50 %','50-60 %','60-70 %','70-80 %','80-90 %','90-100 %'));
		// Create the bar plots
		$b1plot = new BarPlot($data1y);
		// Create the bar plots
		$b2plot = new BarPlot($data2y);
		// Create the grouped bar plot
		$gbplot = new GroupBarPlot(array($b1plot, $b2plot));
		// ...and add it to the graPH
		$graph->Add($gbplot);
		$b1plot->SetColor("white");
		$b1plot->SetFillColor("#cc1111");
		$b2plot->SetColor("white");
		$b2plot->SetFillColor("#1111cc");
		$b1plot->SetLegend("Average Score for each decile");
		$b2plot->SetLegend("Total Score for each decile");

		$graph->title->Set($title);
		$url = $this->getUniqueMediaFileName("png");
		// Display the graph
		$graph->Stroke(ROOT_PATH.$url);
		return ROOT_URL.$url;
		}
		
	*/
	/*
	 * deprecated
	 
		
	private function computeRadarPlot($sums,$avgs, $labels, $title)
		{
		// Some data
		$data1 = $sums ;
		$data2 = $avgs ;
		
		// Setup a basic radar graph
		$graph = new RadarGraph(880,400,'auto');

		// Add a title to the graph
		$graph->title->Set('Total and average Score for each variables');
		 
		// Create the first radar plot with formatting
		$plot1 = new RadarPlot($data1);
		$plot1->SetLegend('Total Score');
		$plot1->SetColor('red', 'lightred');
		 
		$plot2 = new RadarPlot($data2);
		$plot2->SetLegend('Average Score');
		$plot2->SetColor('blue', 'lightblue');
		 
		// Add the plots to the graph
		$graph->Add($plot1);
		$graph->Add($plot2);
		 $graph->SetTitles($labels);

		$graph->title->Set($title);
		$url = $this->getUniqueMediaFileName("png");
		// Display the graph
		$graph->Stroke(ROOT_PATH.$url);
		return ROOT_URL.$url;

		}
	 * 
	 */

} /* end of class taoResults_models_classes_ResultsService */

?>