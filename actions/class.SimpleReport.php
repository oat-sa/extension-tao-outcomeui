<?php
require_once('tao/lib/jpgraph/jpgraph.php');
require_once ('tao/lib/jpgraph/jpgraph_bar.php');
require_once ('tao/lib/jpgraph/jpgraph_radar.php');


class taoResults_actions_SimpleReport extends tao_actions_TaoModule {
 
    public function __construct() {

        parent::__construct();
        $this->service = taoResults_models_classes_ResultsService::singleton();
        $this->defaultData();
    }


    /**
     * get the main class
     * @return core_kernel_classes_Classes
     */
	protected function getRootClass() {
        return new core_kernel_classes_Class(RESULT_ONTOLOGY . "#" . "TAO_DELIVERY_RESULTS");
    }
	public function build(){
	
	$selectedDelivery = $this->getCurrentClass();
	$deliveryDataSet = $this->extractDeliveryDataSet($selectedDelivery);
	//add the required graphics computation and textual information for this particular report
	$this->buildSimpleReport($deliveryDataSet);
	//and select the corresponding view structure		
	$this->setView('simple_form.tpl');
    }  

	/**
	* returns  a data set containing results data using associative array
	* and basic statistics related to a delivery class
	* the returned dataset may be used to build different type of reports
	* TODO : delegate the low level source data extraction based on the model of results storage
	*/
	private function extractDeliveryDataSet($deliveryClass){
			
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
        foreach ($deliveryResults as $uri => $deliveryResult){
        
                $variables = $deliveryResult->getRdfTriples();
                foreach ($variables->getIterator() as $triple){

                        //attempt to detect the nature of the collected data, ROFL
			if (!((strpos($triple->predicate,"_SCORE")===false))){
			// we should parametrize if we consider multiple executions of the same test taker or not
                        $statisticsGroupedPerVariable[$triple->predicate]["data"][]=$triple->object;
			$statisticsGroupedPerVariable[$triple->predicate]["sum"]+= $triple->object;
			$statisticsGroupedPerVariable[$triple->predicate]["#"]+= 1;
			
			$statisticsGroupedPerDelivery["data"][]=$triple->object;
                        $statisticsGroupedPerDelivery["sum"]+= $triple->object;
                        $statisticsGroupedPerDelivery["#"]+= 1;
			}
                 }
        }
		
		 //compute basic statistics
                $statisticsGroupedPerDelivery["avg"] =  $statisticsGroupedPerDelivery["sum"]/ $statisticsGroupedPerDelivery["#"];
		//number of different type of variables collected
		$statisticsGroupedPerDelivery["numberVariables"] = sizeOf( $statisticsGroupedPerVariable);		
		
		//compute the deciles scores for the complete delivery
		$statisticsGroupedPerDelivery= $this->computeQuantiles($statisticsGroupedPerDelivery, 10);

		//computing average, std and distribution for every single variable
		foreach ($statisticsGroupedPerVariable as $predicate => $data) {
		//compute the total populationa verage score for this variable		
		$statisticsGroupedPerVariable[$predicate]["avg"] = $statisticsGroupedPerVariable[$predicate]["sum"]/$statisticsGroupedPerVariable[$predicate]["#"];
		$statisticsGroupedPerVariable[$predicate] = $this->computeQuantiles($statisticsGroupedPerVariable[$predicate], 10);
		}
		$deliveryDataSet["statisticsPerDelivery"] = $statisticsGroupedPerDelivery;
		$deliveryDataSet["statisticsPerVariable"] = $statisticsGroupedPerVariable;

		return $deliveryDataSet;
		}

	private function computeQuantiles($statisticsGroupedPerDelivery, $split = 10){
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
	private function flattenQuantiles($quantiles, $criteria = "avg"){
		$flatDecileAverages = array();		
		foreach ($quantiles as $quantile){
		$flatDecileAverages[]= $quantile[$criteria];		
		}
		return $flatDecileAverages;
	}	

	/**
	* TODO should be moved in a helper 
	*compute a bar chart PNG picture, stores it and return its url
	*/
	private function computebarChart($dataSet, $title){
		
		$data1y = $this->flattenQuantiles($dataSet, "avg");
		$data2y = $this->flattenQuantiles($dataSet, "sum");
		// Create the graph. These two calls are always required
		$graph = new Graph(550,200,'auto');
		$graph->SetScale("textlin");

		$theme_class=new UniversalTheme;
		$graph->SetTheme($theme_class);
		$graph->SetBox(false);

		$graph->ygrid->SetFill(false);
		$graph->xaxis->SetTickLabels(array('0-10 %',' 0-20 %','20-30 %','30-40 %','40-50 %','50-60 %','60-70 %','70-80 %','80-90 %','90-100 %'));
		$graph->yaxis->HideLine(false);
		$graph->yaxis->HideTicks(false,false);

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
	/**
	*TODO attempt to get a unique file name
	*/
	private function getUniqueMediaFileName($fileExtension="")
		{
			return "taoResults/views/img/".microtime().'.'.fileExtension;
		}
	private function buildSimpleReport($deliveryDataSet){	

		$selectedDelivery = $this->getCurrentClass();
		$selectedDeliveryLabel = $selectedDelivery->getlabel();
		$urlDeliverybarChart = $this->computeBarChart($deliveryDataSet["statisticsPerDelivery"]["splitData"], "Average and Total Scores by deciles of the population (".$selectedDeliveryLabel.")");
		
		$this->setData('deliveryBarChart', $urlDeliverybarChart);
		$this->setData('reportTitle', 'Statistical Report ('.$selectedDeliveryLabel.')');
		$this->setData('average',  $deliveryDataSet["statisticsPerDelivery"]["avg"]);
		$this->setData('std',  $deliveryDataSet["statisticsPerDelivery"]["std"]);
		$this->setData('nbExecutions',  $deliveryDataSet["nbExecutions"]);
		$this->setData('#',  $deliveryDataSet["statisticsPerDelivery"]["#"]);
		$this->setData('numberVariables',  $deliveryDataSet["statisticsPerDelivery"]["numberVariables"]);	
		
		foreach ($deliveryDataSet["statisticsPerVariable"] as $predicateUri => $struct){
		$scoreVariable = new core_kernel_classes_Resource($predicateUri);
		$scoreVariableLabel = $scoreVariable->getlabel();
		//compute every single distribution for each variable
		$urlDeliveryVariablebarChart = $this->computeBarChart($deliveryDataSet["statisticsPerVariable"][$predicateUri]["splitData"], "Average and Total Scores by deciles of the population (".$scoreVariableLabel.")");
		
		//build UX data structure		
		$listOfVariables[]= array("label" => $scoreVariableLabel, "url" => $urlDeliveryVariablebarChart, "infos" => array("#" => $struct["#"], "sum" => $struct["sum"], "avg" => $struct["avg"]));
		
		//build parallel arrays to maintain values for the graph computation showing all variables
		$labels[] = $scoreVariableLabel;
		$sums[] = $struct["sum"];
		$avgs[] = $struct["avg"];
		}
		
		$this->setData('listOfVariables', $listOfVariables);	
		$urlDeliveryVariableRadarPlot = $this->computeRadarPlot($sums,$avgs,$labels, "Scores by variables");
		$this->setData('compareVariablesPlot', $urlDeliveryVariableRadarPlot);	
		}


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

	


 }

?>
