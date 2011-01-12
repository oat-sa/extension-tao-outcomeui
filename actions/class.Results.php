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
class taoResults_actions_Results extends tao_actions_TaoModule {

    /**
     * constructor: initialize the service and the default data
     * @return Results
     */
    public function __construct() {

        parent::__construct();

        //the service is initialized by default
        $this->service = tao_models_classes_ServiceFactory::get('Results');
        $this->defaultData();
    }

    /*
     * conveniance methods
     */

    /**
     * get the instancee of the current subject regarding the 'uri' and 'classUri' request parameters
     * @return core_kernel_classes_Resource the subject instance
     */
    protected function getCurrentInstance() {

        $uri = tao_helpers_Uri::decode($this->getRequestParameter('uri'));
        if (is_null($uri) || empty($uri)) {
            throw new Exception("No valid uri found");
        }

        $clazz = $this->getCurrentClass();

        $result = $this->service->getResult($uri, 'uri', $clazz);
        if (is_null($result)) {
            throw new Exception("No subject found for the uri {$uri}");
        }

        return $result;
    }

    /**
     * get the main class
     * @return core_kernel_classes_Classes
     */
    protected function getRootClass() {
        return $this->service->getResultClass();
    }

    /*
     * controller actions
     */

    /**
     * edit an subject instance
     * @return void
     */
    public function editResult() {
        $clazz = $this->getCurrentClass();
        $result = $this->getCurrentInstance();

        $formContainer = new tao_actions_form_Instance($clazz, $result);
		$myForm = $formContainer->getForm();
		
		$disabledProperties = array(
			PROP_RESULT_RESULTCONTENT,
			PROP_RESULT_PROCESS_EXEC_ID,
			PROP_RESULT_DELIVERY_ID,
			PROP_RESULT_TEST_ID,
			PROP_RESULT_ITEM_ID,
			PROP_RESULT_SUBJECT_ID,
			PROP_RESULT_ITEM_VARIABLE_ID,
			PROP_RESULT_ITEM_VARIABLE_VALUE
		);
		
		$readOnlyProperties = array(
			PROP_RESULT_ITEM_VARIABLE_ID,
			PROP_RESULT_ITEM_VARIABLE_VALUE
		);
		
		//disable all input fields:
		foreach($disabledProperties as $disabledPropertyUri){
			$elementName = tao_helpers_Uri::encode($disabledPropertyUri);
			$element = $myForm->getElement($elementName);
			if(!is_null($element)){
				//disable element:
				if(in_array($disabledPropertyUri, $readOnlyProperties)){
					$element->setAttribute('readonly', 'true');
				}else{
					$element->setAttribute('disabled', 'disabled');
				}
				$myForm->removeElement($elementName);
				$myForm->addElement($element);
			}
		}

        if ($myForm->isSubmited()) {
            if ($myForm->isValid()) {

                $result = $this->service->bindProperties($result, $myForm->getValues());

                $this->setData('message', __('Result saved'));
                $this->setData('reload', true);
            }
        }

        $this->setSessionAttribute("showNodeUri", tao_helpers_Uri::encode($result->uriResource));

        $this->setData('formTitle', __('Edit result'));
        $this->setData('myForm', $myForm->render());
        $this->setView('form.tpl', true);
    }

    /**
     * add a subject model (subclass Result)
     * @return void
     */
    public function addResultClass() {
        if (!tao_helpers_Request::isAjax()) {
            throw new Exception("wrong request mode");
        }
        $clazz = $this->service->createResultClass($this->getCurrentClass());
        if (!is_null($clazz) && $clazz instanceof core_kernel_classes_Class) {
            echo json_encode(array(
                'label' => $clazz->getLabel(),
                'uri' => tao_helpers_Uri::encode($clazz->uriResource)
            ));
        }
    }

    /**
     * Edit a subject model (edit a class)
     * @return void
     */
    public function editResultClass() {
        $clazz = $this->getCurrentClass();

        if ($this->hasRequestParameter('property_mode')) {
            $this->setSessionAttribute('property_mode', $this->getRequestParameter('property_mode'));
        }

        $myForm = $this->editClass($clazz, $this->service->getResultClass());
        if ($myForm->isSubmited()) {
            if ($myForm->isValid()) {
                if ($clazz instanceof core_kernel_classes_Resource) {
                    $this->setSessionAttribute("showNodeUri", tao_helpers_Uri::encode($clazz->uriResource));
                }
                $this->setData('message', __('Class saved'));
                $this->setData('reload', true);
            }
        }
        $this->setData('formTitle', __('Edit result class'));
        $this->setData('myForm', $myForm->render());
        $this->setView('form.tpl', true);
    }

    /**
     * delete a subject or a subject model
     * called via ajax
     * @return void
     */
    public function delete() {
        if (!tao_helpers_Request::isAjax()) {
            throw new Exception("wrong request mode");
        }

        $deleted = false;
        if ($this->getRequestParameter('uri')) {
            $deleted = $this->service->deleteResult($this->getCurrentInstance());
        } else {
            $deleted = $this->service->deleteResultClass($this->getCurrentClass());
        }

        echo json_encode(array('deleted' => $deleted));
    }

    /**
     * create data table
     * @return void
     */
    public function createTable() {

        $_SESSION['instances'] = array();
        

        $index = 0;
        $clazz = $this->getCurrentClass();
        foreach ($clazz->getInstances(false) as $resource) {
            $_SESSION['instances'][$resource->uriResource] = 'uri_' . $index;
            $index++;
        }

        $this->setView("create_table.tpl");
    }
    public function createDirectTable(){
        $_SESSION['instances'] = array();

        $index = 0;
        $clazz = $this->getCurrentClass();
        foreach ($clazz->getInstances(true) as $resource) {
            $_SESSION['instances'][$resource->uriResource] = 'uri_' . $index;
            $index++;
        }
        //add information of the class
        $listProperties = $clazz->getProperties(true);
        $listUrisWithoutFilter = array_keys($listProperties);
        //do a first filter to gremove the RDF property that are not important
        $listUris=$this->filterPropertyList($listUrisWithoutFilter);


        $propertiesOfSimpleTable = array();
        
        foreach ($listUris as $uriProp){
            $propInstance = $trProperty = new core_kernel_classes_Property($uriProp);
            $label = $propInstance->getLabel();
            $propertiesOfSimpleTable[$uriProp] = $label;
        }
        $_SESSION['utrListOfProperties'] = $propertiesOfSimpleTable;

        $this->setView("create_table.tpl");

    }
    private function filterPropertyList($listProperties){
        //http://www.w3.org/2000/01/rdf-schema#isDefinedBy
        //If the name space of the property is http://www.w3.org/2000/01/rdf-schema#isDefinedBy
        //Then delete from list
        $listProperties = array_flip($listProperties);
        
        $finalProp = $listProperties;
        $blockedProperties = array();

        $blockedProperties[]= 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type';
        $blockedProperties[]= 'http://www.w3.org/1999/02/22-rdf-syntax-ns#subject';
        $blockedProperties[]= 'http://www.w3.org/1999/02/22-rdf-syntax-ns#predicate';
        $blockedProperties[]= 'http://www.w3.org/1999/02/22-rdf-syntax-ns#object';
        $blockedProperties[]= 'http://www.w3.org/1999/02/22-rdf-syntax-ns#value';
        $blockedProperties[]= 'http://www.w3.org/2000/01/rdf-schema#subPropertyOf';
        $blockedProperties[]= 'http://www.w3.org/2000/01/rdf-schema#comment';
        $blockedProperties[]= 'http://www.w3.org/2000/01/rdf-schema#seeAlso';
        $blockedProperties[]= 'http://www.w3.org/2000/01/rdf-schema#isDefinedBy';
        $blockedProperties[]= 'http://www.w3.org/2000/01/rdf-schema#member';
        $blockedProperties[]= 'http://www.tao.lu/middleware/Interview.rdf#i122354397139712';
        $blockedProperties[]= "http://www.tao.lu/middleware/Interview.rdf#i12191501768574";
        $blockedProperties[]= "http://www.tao.lu/Ontologies/TAOResult.rdf#ResultContent";
        foreach ($listProperties as $uri=>$obj ) {

            if (in_array($uri,$blockedProperties)===true) {
               
                unset($finalProp[$uri]);
            }
            //for the specific uri like SCORE

        }
        return array_flip($finalProp);
        
    }

    public function createScoreTable(){
        $_SESSION['instances'] = array();


        $index = 0;
        $clazz = $this->getCurrentClass();
        foreach ($clazz->getInstances(true) as $resource) {
            $_SESSION['instances'][$resource->uriResource] = 'uri_' . $index;
            $index++;
        }
        //add information of the class
        $listProperties = $clazz->getProperties(true);
        $listUrisWithoutFilter = array_keys($listProperties);
        //do a first filter to gremove the RDF property that are not important
        $listUris=$this->filterPropertyList($listUrisWithoutFilter);

        $propertiesOfSimpleTable = array();
        $scoreID = SCORE_ID;

        //add fixed properties ( the DTIS )
        //$allowedProperty [] = "http://www.tao.lu/Ontologies/TAOResult.rdf#TAO_PROCESS_EXEC_ID";
        $allowedProperty [] = "http://www.tao.lu/Ontologies/TAOResult.rdf#TAO_DELIVERY_ID";
        $allowedProperty [] = "http://www.tao.lu/Ontologies/TAOResult.rdf#TAO_SUBJECT_ID";

        foreach ($listUris as $uriProp){
                 //filter the property
            
            if ((!strpos($uriProp,$scoreID)===FALSE)|| (in_array($uriProp, $allowedProperty))){
            $propInstance = $trProperty = new core_kernel_classes_Property($uriProp);
            $label = $propInstance->getLabel();
            $propertiesOfSimpleTable[$uriProp] = $label;
                
            }
            
        }

        $_SESSION['utrListOfProperties'] = $propertiesOfSimpleTable;

        $this->setView("create_table.tpl");

    }
    public function templateUtr13(){
        $this->setView("utr13.tpl");
    }
}


//    public function sum($a, $b) {
//        echo " la somme est :". ($a + $b);
//    }
//
//    public function paramSum() {
//        /// get the parameters by http
//        $a = $this->getRequestParameter('a');
//        $b = $this->getRequestParameter('b');
//        echo 'somme http :' . ($a + $b);
//    }
//
//    public function sumModel($a, $b) {
//        //echo "inprogress + access au truc";
//        $eService = tao_models_classes_ServiceFactory::get('taoResults_models_classes_Clacul');
//        //echo "<br>";
// //       $res = $eService->sumsum($a,$b);
// //
////        $this->setData('a',$a);
////        $this->setData('b',$b);
////        $this->setData('result',$res);
////        //$this->setView('sum.tpl');
//        $pa = $this->getRequestParameter('a');
//        $pb = $this->getRequestParameter('b');
//        $res = $eService->sumsum($pa+1000,$pb);
//        $clas = new core_kernel_classes_Class(TAO_ITEM_RESULTS_CLASS);
//        $label = $clas->getLabel();
//            //****Remove from the last version
//        $resultNS = core_kernel_classes_Session::getNameSpace();
//
//        //$label = $clazz->getLabel();
//        $result["valeur"]= $res;
//        $result["label"] = $label;
//
//        echo json_encode($result);
//
//    }
//    public function manage (){
//        $this->setData("message",'<br> hhhhhuiohjkh hjkh jk <br>');
//        $this->setData ("path",TAOVIEW_PATH);
//        $this->setView('manage.tpl');
//
//    }
//
//}
?>