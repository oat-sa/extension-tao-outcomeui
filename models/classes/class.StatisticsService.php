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

/**
 * TAO - taoResults/models/classes/class.StatisticsService.php
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


class taoResults_models_classes_StatisticsService

     extends taoResults_models_classes_ResultsService
{
	/**
	* returns  a data set containing results data using and using an associative array
	* with basic statistics related to a delivery class
	* the returned dataset may be used to build different type of reports
	* TODO : delegate the low level source data extraction based on the model of results storage
	*/
	public function extractDeliveryDataSet($deliveryClass){
			
	$deliveryDataSet = array(
	"nbExecutions" => 0, //Number of collected executions of the delivery
	"nbMaxExpectedExecutions"=>0, //Number of Test Takers
	"nbMaxExecutions"=>0, //Number of Executions tokens granted
	"statisticsPerVariable"=>array(), //an array containing variables as keys, collected and computed data 					["statisticsPerTest"]=>array()
	"statisticsPerDelivery"=>array()
	);
       
	//strong assumption that the delivery results are not moved, etc. another cpu demanding way could be implemented asking explicitely for the related delivery uri
	 $deliveryResults =  $deliveryClass->getInstances(false);
         $deliveryDataSet["nbExecutions"] = sizeOf($deliveryResults);

        $statisticsGroupedPerVariable = array();
	 /**
        * The results model for storage in TAO has evolved over time, this dataset extractions is based on Younes Simple Mode$
        */
        foreach ($deliveryResults as $deliveryResult){
		
		$scoreVariables = $this->getScoreVariables($deliveryResult);
		
                foreach ($scoreVariables as $variable){
			
			$variableData = $this->getVariableData($variable);
                        
			
			$variableIDentifier = $variableData["item"]->getUri().$variableData["variableIdentifier"];
			
			// we should parametrize if we consider multiple executions of the same test taker or not
                        $statisticsGroupedPerVariable[$variableIDentifier]["data"][]=$variableData["value"];
			$statisticsGroupedPerVariable[$variableIDentifier]["sum"]+= $variableData["value"];
			$statisticsGroupedPerVariable[$variableIDentifier]["#"]+= 1;
			
			$statisticsGroupedPerDelivery["data"][]=$variableData["value"];
                        $statisticsGroupedPerDelivery["sum"]+= $variableData["value"];
                        $statisticsGroupedPerDelivery["#"]+= 1;
			
                 }
        }
		
		 //compute basic statistics
                $statisticsGroupedPerDelivery["avg"] =  $statisticsGroupedPerDelivery["sum"]/ $statisticsGroupedPerDelivery["#"];
		//number of different type of variables collected
		$statisticsGroupedPerDelivery["numberVariables"] = sizeOf( $statisticsGroupedPerVariable);		
		
		//compute the deciles scores for the complete delivery
		$statisticsGroupedPerDelivery= $this->computeQuantiles($statisticsGroupedPerDelivery, 10);

		//computing average, std and distribution for every single variable
		foreach ($statisticsGroupedPerVariable as $variableIdentifier => $data) {
		//compute the total populationa verage score for this variable		
		$statisticsGroupedPerVariable[$variableIdentifier]["avg"] = $statisticsGroupedPerVariable[$variableIdentifier]["sum"]/$statisticsGroupedPerVariable[$variableIdentifier]["#"];
		$statisticsGroupedPerVariable[$variableIdentifier] = $this->computeQuantiles($statisticsGroupedPerVariable[$variableIdentifier], 10);
		}
		$deliveryDataSet["statisticsPerDelivery"] = $statisticsGroupedPerDelivery;
		$deliveryDataSet["statisticsPerVariable"] = $statisticsGroupedPerVariable;

		return $deliveryDataSet;
		}

	public function computeQuantiles($statisticsGroupedPerDelivery, $split = 10){
		//computing average, std and distribution for the delivery 
		 //TODO  search for some PHP stats extension
                $slotSize = $statisticsGroupedPerDelivery["#"] / $split; //number of observations per slot
		sort($statisticsGroupedPerDelivery["data"]);		                      
			//sum all values for the slotsize
                        $slot = 0 ; 
			$i=1;
                        foreach ($statisticsGroupedPerDelivery["data"] as $key => $value){
				
                                if (($i) > $slotSize && (!($slot+1==$split))) {$slot++;$i=1;}
                                if (!(isset($statisticsGroupedPerDelivery["splitData"][$slot]))) {
                                $statisticsGroupedPerDelivery["splitData"][$slot] = array("sum" => 0, "avg" =>0);
                                }
                                $statisticsGroupedPerDelivery["splitData"][$slot]["sum"] += $value;
				 $statisticsGroupedPerDelivery["splitData"][$slot]["#"] ++;
				$i++;
                        }
			
                        //compute the average
                        foreach ( $statisticsGroupedPerDelivery["splitData"] as $slot => $struct){
                                $statisticsGroupedPerDelivery["splitData"][$slot]["avg"] = 
                                $statisticsGroupedPerDelivery["splitData"][$slot]["sum"] /  $statisticsGroupedPerDelivery["splitData"][$slot]["#"];
                        }
		
		return $statisticsGroupedPerDelivery;	
		}
	
	//flatten the structure returned by the results data set extractor into a flat array for the graphics computation
	public function flattenQuantiles($quantiles, $criteria = "avg"){
		$flatDecileAverages = array();		
		foreach ($quantiles as $quantile){
		$flatDecileAverages[]= $quantile[$criteria];		
		}
		return $flatDecileAverages;
	}	


} /* end of class taoResults_models_classes_ResultsService */

?>