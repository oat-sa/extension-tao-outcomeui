<?php
require_once('tao/lib/jpgraph/src/jpgraph.php');
require_once ('tao/lib/jpgraph/src/jpgraph_bar.php');

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
	/**
	* returns  a data set containing results data using associative array
	* and basic statistics related to a delivery class
	* this structure may be used to build different type of reports
	* TODO : delegate the source data extraction based on the model of results storage
	*/
	private function extractDeliveryDataSet($deliveryClass){
		
		
		$deliveryDataSet = array(
					"nbExecutions" => 0, //Number of collected executions of the delivery
					"nbMaxExpectedExecutions"=>0, //Number of Test Takers
					"nbMaxExecutions"=>0, //Number of Executions tokens granted
					"statisticsPerVariable"=>array(), //an array containing variables as keys, collected and computed data 					["statisticsPerTest"]=>array()
					"statisticsPerDelivery"=>array()
		);

                $deliveryResults =  $deliveryClass->getInstances(false);
		//strong assumption that the delivery results are not moved, etc. another cpu demanding way could be implemented asking explicitely for the related delivery uri
                 $deliveryDataSet["nbExecutions"] = sizeOf($deliveryResults);

                $statisticsGroupedPerVariable = array();
		 /**
                * The results model for storage in TAO has evolved over time, this dataset extractions is based on Younes Simple Mode$
                */

                foreach ($deliveryResults as $uri => $deliveryResult){
                
                        $variables = $deliveryResult->getRdfTriples();
                        foreach ($variables->getIterator() as $triple){

                                //attempt to detect the nature of the collected data
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
		
		//compute basic statistics per delivery

		 //compute basic statistics
                $statisticsGroupedPerDelivery["avg"] =  $statisticsGroupedPerDelivery["sum"]/ $statisticsGroupedPerDelivery["#"];
		//number of different type of variables collected
		$statisticsGroupedPerDelivery["numberVariables"] = sizeOf( $statisticsGroupedPerVariable);		
		
		//computing average, std and distribution for the delivery 
		 //TODO  search for some PHP stats extension
                $split = 10;
                $slotSize = $statisticsGroupedPerDelivery["#"] / $split; //number of observations per slot
                        //sum all values for the slotsize
                        $slot = 0 ; 

                        foreach ($statisticsGroupedPerDelivery["data"] as $key => $value){

                                if (($key+1) > $slotSize) $slot++;

                                if (!(isset($statisticsGroupedPerDelivery["splitData"][$slot]))) {
                                $statisticsGroupedPerDelivery["splitData"][$slot] = array("sum" => 0, "avg" =>0);
                                }
                                $statisticsGroupedPerDelivery["splitData"][$slot]["sum"] =  
                                        $data["splitData"][$slot]["sum"] 
                                                + $value ;
                        }
                        //compute the average
                        foreach ( $statisticsGroupedPerDelivery["splitData"] as $slot => $struct){
                                $statisticsGroupedPerDelivery["splitData"][$slot]["avg"] = 
                                        $data["splitData"][$slot]["sum"] / $slotSize;
                        }


		
		//computing average, std and distribution for every single variable
		foreach ($statisticsGroupedPerVariable as $predicate => $data) {
		$statisticsGroupedPerVariable[$predicate]["avg"] = $data["sum"]/$data["#"];
		
		//sort the data set in order to compute the deciles/percentiles
		$statisticsGroupedPerVariable[$predicate]["data"] = ksort($data["data"]);
		
		//TODO  search for some PHP stats extension
		$split = 10;
		$slotSize = $data["#"] / $split; //number of observations per slot
			//sum all values for the slotsize
			$slot = 0 ; 

			foreach ($data["data"] as $key => $value){
				
				if (($key+1) > $slotSize) $slot++;
				
				if (!(isset($statisticsGroupedPerVariable[$predicate]["splitData"][$slot]))) {
				$statisticsGroupedPerVariable[$predicate]["splitData"][$slot] = array("sum" => 0, "avg" =>0);
				}
				$statisticsGroupedPerVariable[$predicate]["splitData"][$slot]["sum"] =  
					$data["splitData"][$slot]["sum"] 
						+ $value ;
			}
		 	//compute the average
			foreach ( $statisticsGroupedPerVariable[$predicate]["splitData"] as $slot => $struct){
				$statisticsGroupedPerVariable[$predicate]["splitData"][$slot]["avg"] = 
					$data["splitData"][$slot]["sum"] / $slotSize;
			}
			
			
		}
		 $deliveryDataSet["statisticsPerVariable"] = $statisticsGroupedPerVariable;

		 $deliveryDataSet["statisticsPerDelivery"] = $statisticsGroupedPerDelivery;
                
		return $deliveryDataSet;
		}
	/**
	*compute a bar chart PNG picture and return its url
	*/
	private function computebarChart($data){
//$data1y=$data;
$data1y = array(10,20,30,15,45,65,30);
// Create the graph. These two calls are always required
$graph = new Graph(350,200,'auto');
$graph->SetScale("textlin");

$theme_class=new UniversalTheme;
$graph->SetTheme($theme_class);

$graph->yaxis->SetTickPositions(array(0,30,60,90,120,150), array(15,45,75,105,135));
$graph->SetBox(false);

$graph->ygrid->SetFill(false);
$graph->xaxis->SetTickLabels(array('D1','D2','D3','D4','D5','D6','D7','D8','D9','D10'));
$graph->yaxis->HideLine(false);
$graph->yaxis->HideTicks(false,false);

		// Create the bar plots
		$b1plot = new BarPlot($data1y);

		// Create the grouped bar plot
		$gbplot = new GroupBarPlot(array($b1plot));
		// ...and add it to the graPH
		$graph->Add($gbplot);

		$b1plot->SetColor("white");
		$b1plot->SetFillColor("#cc1111");

		$graph->title->Set("Bar Plots");
		$url = "taoResults/views/img/".rand(0,30000).'.png';
		// Display the graph
		$graph->Stroke(ROOT_PATH.$url);
		return ROOT_URL.$url;
		}
	private function buildSimpleReport($dataSet){
			
			$urlDeliverybarChart = $this->computeBarChart($dataSet);
			$this->setData('deliveryBarChart',$dataSet["statisticsPerDelivery"]["data"]);		
		}
	public function build(){
	
		$selectedDelivery = $this->getCurrentClass();
		$selectedDeliveryLabel = $selectedDelivery->getlabel();
		

		$deliveryDataSet = $this->extractDeliveryDataSet($selectedDelivery);
		//var_dump($deliveryDataSet);
		//add the required graphics computation and textual information for this particular report
		
		$this->buildSimpleReport($deliveryDataSet);
		
		$this->setData('reportTitle', 'Statistical Report ('.$selectedDeliveryLabel.')');
		$this->setData('average',  $deliveryDataSet["statisticsPerDelivery"]["avg"]);
		$this->setData('std',  $deliveryDataSet["statisticsPerDelivery"]["std"]);
		$this->setData('nbExecutions',  $deliveryDataSet["nbExecutions"]);

		$this->setData('#',  $deliveryDataSet["statisticsPerDelivery"]["#"]);
		$this->setData('numberVariables',  $deliveryDataSet["statisticsPerDelivery"]["numberVariables"]);
		

		$this->setView('simple_form.tpl');
    }  


 }

?>
