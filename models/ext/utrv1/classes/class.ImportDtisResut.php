<?php

/**
 *
 *
 * @author Younes Djaghloul, CRP Henri Tudor
 * @package Result
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/generis/common/inc.extension.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/taoResults/includes/common.php");

class ImportDtisResult {

    public function __construct() {//our dev teame Som
    }

    /**
     * Short description of method addResultVariable
     *
     * @access public
     * @author Younes Dajghloul, Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  array dtisUris
     * @param  string key
     * @param  string value
     * @return core_kernel_classes_Resource
     */
    public function addResultVariable($dtisUris, $key, $value) {
        $returnValue = null;

        // section 127-0-1-1-3fc126b2:12c350e4297:-8000:0000000000002886 begin
        //get the name space of the class

        $resultNS = 'http://www.tao.lu/Ontologies/TAOResult.rdf';

        if (is_array($dtisUris) && !empty($key)) {

            //connect to the class of dtis Result Class

            $dtisResultClass = new core_kernel_classes_Class(TAO_ITEM_RESULTS_CLASS);
            //****Remove from the last version
            $resultNS = core_kernel_classes_Session::getNameSpace(); // to remove in the final version
            $dtisResultClass = new core_kernel_classes_Class($resultNS . '#' . "TAO_ITEM_RESULTS");
            //****
            // Create the instance of DTIS_Result
            //one begins by create the label; label of DTIS + date
            // label of Delivery
            if (!isset($dtisUris["TAO_PROCESS_EXEC_ID"])) {
                throw new Exception('TAO_PROCESS_EXEC_ID must reference a process execution uri');
            }
            $uri = $dtisUris["TAO_PROCESS_EXEC_ID"];
            $inst = new core_kernel_classes_Resource($uri);
            $processLabel = $inst->getLabel();

            // label of Delivery
            if (!isset($dtisUris["TAO_DELIVERY_ID"])) {
                throw new Exception('TAO_DELIVERY_ID must reference a delivery uri');
            }
            $uri = $dtisUris["TAO_DELIVERY_ID"];
            $inst = new core_kernel_classes_Resource($uri);
            $deliveryLabel = $inst->getLabel();

            // label of Test
            if (!isset($dtisUris["TAO_TEST_ID"])) {
                throw new Exception('TAO_TEST_ID must reference a test uri');
            }
            $uri = $dtisUris["TAO_TEST_ID"];
            $inst = new core_kernel_classes_Resource($uri);
            $testLabel = $inst->getLabel();

            // label of Item
            if (!isset($dtisUris["TAO_ITEM_ID"])) {
                throw new Exception('TAO_ITEM_ID must reference an item uri');
            }
            $uri = $dtisUris["TAO_ITEM_ID"];
            $inst = new core_kernel_classes_Resource($uri);
            $itemLabel = $inst->getLabel();

            //label of Subject
            if (!isset($dtisUris["TAO_SUBJECT_ID"])) {
                throw new Exception('TAO_SUBJECT_ID must reference a subject uri');
            }
            $uri = $dtisUris["TAO_SUBJECT_ID"];
            $inst = new core_kernel_classes_Resource($uri);
            $subjectLabel = $inst->getLabel();
            //-----To remove in the last version
            list($ns,$processLabel)= explode("#",$dtisUris["TAO_PROCESS_EXEC_ID"]);
            list($ns,$deliveryLabel)= explode("#",$dtisUris["TAO_DELIVERY_ID"]);

            list($ns,$testLabel)= explode("#",$dtisUris["TAO_TEST_ID"]);
            echo "<br> test : $testLabel";
            list($ns,$itemLabel)= explode("#",$dtisUris["TAO_ITEM_ID"]);
            list($ns,$subjectLabel)= explode("#",$dtisUris["TAO_SUBJECT_ID"]);

            // Create the label of the new instance
            $dtisLabel = $deliveryLabel."_".$processLabel. "_" . $testLabel . "_" . $itemLabel . "_" . $subjectLabel . "_" . date("Y/m/d_H:i:s"); // todo label of dtis + date
            $dtisComment = "Result Recieved the : " . date("Y/m/d_H:i:s");
            $dtisInstance = $dtisResultClass->createInstance($dtisLabel, $dtisComment);
            //Add the uri of the variable and its value to the array
            //Put the name only without the name space part

            $dtisUrisAll['TAO_ITEM_VARIABLE_ID'] = $key;
            $dtisUrisAll['TAO_ITEM_VARIABLE_VALUE'] = $value;
            $dtisUrisAll = array_merge($dtisUris,$dtisUrisAll);
            //print_r($dtisUris);
            // Create the property Object
            foreach ($dtisUrisAll as $dtisUri => $dtisValue) {
                // Create the property Object
                $dtisProp = new core_kernel_classes_Property($resultNS . "#" . $dtisUri);
                // Set values of the instance
                $dtisInstance->setPropertyValue($dtisProp, $dtisValue);
            }
        }

        //put the instance as returned value
        $returnValue = $dtisInstance;

        //**********************************************************
        //Now we will add the method that create the instance in the delivery classe
        //the delivery classes wils be created and dynamically, as well as thier properties
        //Regarding to each new result, we creat or no the class and the property,
        //its important to check the existance of the instance also to add the propertyvamue for the appropriate instance
        //get the appropriate Delivery Result Class according to the delivery of the result
        //one begins by geting all classes of TAO_DELIVERY_RESULTS
        //connect to the class TAO_DELIVERY_RESULTS
        $TAO_DELIVERY_RESULTS_CLASS = $resultNS . "#" . "TAO_DELIVERY_RESULTS"; // to modify in the last version and put a contant
        $deliveryResultClass = new core_kernel_classes_Class($TAO_DELIVERY_RESULTS_CLASS);
        $listOfDeliveryClasses = $deliveryResultClass->getSubClasses();
        //compaiason
        $uri = $dtisUris["TAO_DELIVERY_ID"];
        list($ns, $lastpartUriDeliveryOfResult) = explode("#", $uri);
        //use the last part as uri for the search
        $localNS = core_kernel_classes_Session::getNameSpace();
        $uriDelivery = $localNS . "#DR_" . $lastpartUriDeliveryOfResult;// add DR_ for the instance of the Delivery Result
        //check of not existance => create it with the same last part uri as the delivery
        //print_r($listOfDeliveryClasses);
        if (array_key_exists($uriDelivery, $listOfDeliveryClasses) === FALSE) {
            echo "Not exist<br>";
            $rdfClass = new core_kernel_classes_Class(RDF_CLASS);
            $labelClass = $deliveryLabel. "_Results";
            $commentClass = " This result class contains all results for the " . $deliveryLabel . " delivery";
            $uriClass = '#' ."DR_". $lastpartUriDeliveryOfResult;
            $resourceClass = $rdfClass->createInstance($labelClass, $commentClass, $uriClass);
            //This class should be linked to the URI already created
            $drClass = new core_kernel_classes_Class($resourceClass->uriResource);

            //set this class as sub class of TAO_DELIVERY_RESULTS ($deliveryResultClass)
            $drClass->setSubClassOf($deliveryResultClass);

            $uriDelivery = $drClass->uriResource;

            print_r($drClass->getParentClasses());
        }
        echo " <br> the class to be used is $uriDelivery <br>";
//        $drClass = new core_kernel_classes_Class($uriDelivery);
        //créer les 5 properties

//        foreach($dtisUris as $nameTaoVar=>$valueTaoVar){
//
//             //create the property
//            $rdfProperty = new core_kernel_classes_Class(RDF_PROPERTY);
//            $labelProperty = $nameTaoVar;
//
//            $resourceProperty = $rdfProperty->createInstance($labelProperty, $commentProperty, "#" . $nameTaoVar);
//            //Create the property and link it with the uri
//            $drProperty = new core_kernel_classes_Property($resourceProperty->uriResource);
//
//
//            $widgetProp = new core_kernel_classes_Property("http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget");
//            $drProperty->setPropertyValue($widgetProp, "http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox");
//
//            //Link the property with the class
//            $drClass->setProperty($drProperty);
//
//        }
//        $listProp = $drClass->getProperties();
//        print_r($listProp);

        //now, we have to check from all instances fo this class, if the instance exists
        //get the instances of the class of delivery result
        $drClass = new core_kernel_classes_Class($uriDelivery);
        $listDeliveryResults = $drClass->getInstances();
        $match = FALSE;
        $matchUri = '';
        foreach ($listDeliveryResults as $uri=>$resource) {
            $drInstance = new core_kernel_classes_Resource($uri);
            $propProcId = new core_kernel_classes_Property($resultNS . "#" . "TAO_PROCESS_EXEC_ID");
            //get the value of the property TAO_PROCESS_EXEC_ID
            $propVal = $drInstance->getPropertyValues($propProcId);
            // check if the the value of this property matchs with the TAO_PROCESS_EXEC_ID of the inputed result
            $propValue = $propVal[0];
            echo " <br> prop =  $propValue <br> dtis ==".  $dtisUris['TAO_PROCESS_EXEC_ID'];
            if ($propValue == $dtisUris["TAO_PROCESS_EXEC_ID"]) {
                // it matches
                $match = TRUE;
                //return the uriOf this instance
echo "<br> ****************************exist ";
                $matchUri = $uri;
            }

        }
//        foreach ($listDeliveryResults as $uri => $resource) {
//            $drInstance = new core_kernel_classes_Resource($uri);
//
//            foreach ($dtisUris as $nameTaoProperty => $VarValue) {
//                $prop = new core_kernel_classes_Property($localNS . "#" . $nameTaoProperty);
//                //get the property value
//                $propVal = $drInstance->getPropertyValues($prop);
//                //get only the first
//                $instancePropValues[$nameTaoProperty] = $propVal[0];
//            }
//            //now we have an array with 5 propeties value of the current instance
//            // we will compare it with the parameter
//            $flag = 0;
//
//            foreach ($instancePropValues as $nameVar => $valueProp) {
//
//                if ($instancePropValues[$nameVar] == $dtisUri[$nameVar]) {
//
//                    $flag++;
//                    echo ("<br> tt");
//
//                }
//            }
//            if ($flag == 5) {// the number of variable to check
//                $match = TRUE; // not match
//                // return the uri
//                $matchUri = $uri;
//                //exit the loop
//            }
//        }

        //if it doesn't match so create a new instance
        if (!$match) {
            //create a new instance of the current delivery class
            $label = "Res_".$deliveryLabel . "_" . date("Y/m/d_H:i:s");
            $comment = "Result of ".$subjectLabel;
            $newInstance = $drClass->createInstance($label, $comment);
            $matchUri = $newInstance->uriResource;
        }
        // now the $matchUri is the instance to use
        //******************** Get the uri of the property to use to save the value
        //verify if the variable exists as property in the current class
        //get all properties of the current class
        $listOfProperties = $drClass->getProperties();
        //create the last part of the uri test+item++variable name
        $testUri = $dtisUris["TAO_TEST_ID"];
        list($ns, $lpTestUri) = explode("#", $testUri);
        $itemUri = $dtisUris["TAO_ITEM_ID"];
        list($ns, $lpItemUri) = explode("#", $itemUri);
        $nameOfVariable = $key;
        $lastPartUriProperty = $lpTestUri . '_' . $lpItemUri . "_" . $nameOfVariable;
        //check the existance of the property
        $uriPropertyToCheck = $localNS . "#" . $lastPartUriProperty;
        if (array_key_exists($uriPropertyToCheck, $listOfProperties) === FALSE) {
            //create the property
            $rdfProperty = new core_kernel_classes_Class(RDF_PROPERTY);
            $labelProperty = $nameOfVariable . " of " . $itemLabel . '_' . $testLabel;
            $commentProperty = " This property match the variable ". $nameOfVariable . " of the Item :  " . $itemLabel . ' in the test :' . $testLabel;

            $resourceProperty = $rdfProperty->createInstance($labelProperty, $commentProperty, "#" . $lastPartUriProperty);
            //Create the property and link it with the uri
            $drProperty = new core_kernel_classes_Property($resourceProperty->uriResource);

            $widgetProp = new core_kernel_classes_Property("http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget");
            $drProperty->setPropertyValue($widgetProp, "http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox");

            //Link the property with the class
            $drClass->setProperty($drProperty);
            echo " <br>property ".$resourceProperty->uriResource;
        }
        //the Uri of the property is
        $uriOfVariable = $localNS . "#" . $lastPartUriProperty;

        //now we have the uri of the property + the uri of the instance
        //just "Inchallah" set the property values for TAO_PROCESS_EXEC_ID TAO_DELIVERY_ID TAO_SUBJECT_ID
        $deliveryResultInstance = new core_kernel_classes_Resource($matchUri);

        $varPro = new core_kernel_classes_Property($localNS."#"."TAO_PROCESS_EXEC_ID");
        $deliveryResultInstance->editPropertyValues($varPro, $dtisUris["TAO_PROCESS_EXEC_ID"]);


        $varPro = new core_kernel_classes_Property($resultNS."#"."TAO_DELIVERY_ID");
        $deliveryResultInstance->editPropertyValues($varPro, $dtisUris['TAO_DELIVERY_ID']);

        $varPro = new core_kernel_classes_Property($resultNS."#"."TAO_SUBJECT_ID");
        $deliveryResultInstance->editPropertyValues($varPro, $dtisUris["TAO_SUBJECT_ID"]);

        //set the value of the variable property
        $varPro = new core_kernel_classes_Property($uriOfVariable);
        $deliveryResultInstance->editPropertyValues($varPro, $value);
// the end

        // section 127-0-1-1-3fc126b2:12c350e4297:-8000:0000000000002886 end


        return $returnValue;
    }

    /**
     * Short description of method setScore
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  array dtisUris
     * @param  string scoreValue
     * @param  string minScoreValue
     * @param  string maxScoreValue
     * @return core_kernel_classes_Resource
     */
    public function setScore($dtisUris, $scoreValue, $minScoreValue = '', $maxScoreValue = '') {
        $returnValue = null;

        // section 127-0-1-1-3fc126b2:12c350e4297:-8000:000000000000288B begin

        $returnValue = $this->addResultVariable($dtisUris, SCORE_ID, $scoreValue);

        if (!empty($minScoreValue)) {
            $this->addResultVariable($dtisUris, SCORE_MIN_ID, $minScoreValue);
        }
        if (!empty($maxScoreValue)) {
            $this->addResultVariable($dtisUris, SCORE_MAX, $maxScoreValue);
        }

        // section 127-0-1-1-3fc126b2:12c350e4297:-8000:000000000000288B end

        return $returnValue;
    }

    // add new dtis Instance. the inputed array has this structure
    // array["TAO_DELIVERY_ID"]=" value "; the key of the array is the uri of propeties of dtis ResultClas
    // There is 6 variables TAO_DELIVERY_ID , TAO_TEST_ID, TAO_ITEM_ID, TAO_SUBJECT_ID, TAO_ITEM_VARIABLE_ID, TAO_ITEM_VARIABLE_VALUE


    public function addDtisResult($arrayOfDtisVariables) {

        $dtisInstance = null;

        //connect to the class of dtis Result Class
        $resultNS = 'http://www.tao.lu/Ontologies/TAOResult.rdf';

        $itemResultClassURI = $resultNS . "#" . "TAO_ITEM_RESULTS";
        $dtisResultClass = new core_kernel_classes_Class($itemResultClassURI);
        // Create the instance of DTIS_Result
        //one begins by create the label; label of DTIS + date
        // label of Delivery
        $uri = $arrayOfDtisVariables["TAO_DELIVERY_ID"];
        $inst = new core_kernel_classes_Resource($uri);
        $deliveryLabel = $inst->getLabel();
        // label of Test
        $uri = $arrayOfDtisVariables["TAO_TEST_ID"];
        $inst = new core_kernel_classes_Resource($uri);
        $testLabel = $inst->getLabel();
        // label of Item
        $uri = $arrayOfDtisVariables["TAO_ITEM_ID"];
        $inst = new core_kernel_classes_Resource($uri);
        $itemLabel = $inst->getLabel();
        //label of Subject
        $uri = $arrayOfDtisVariables["TAO_SUBJECT_ID"];
        $inst = new core_kernel_classes_Resource($uri);
        $subjectLabel = $inst->getLabel();

        // Create the label of the new instance
        $dtisLabel = $deliveryLabel . "_" . $testLabel . "_" . $itemLabel . "_" . $subjectLabel . "_" . date("Y/m/d_H:i:s"); // todo label of dtis + date
        $dtisComment = "Result Recieved the : " . date("Y/m/d_H:i:s");
        $dtisInstance = $dtisResultClass->createInstance($dtisLabel, $dtisComment);
        // set the value of properties
        foreach ($arrayOfDtisVariables as $dtisUri => $dtisValue) {
            // Create the property Object
            $dtisProp = new core_kernel_classes_Property($resultNS . "#" . $dtisUri);
            // Set values of the instance
            $dtisInstance->setPropertyValue($dtisProp, $dtisValue);
        }
        return $dtisInstance;
    }

}

define('API_LOGIN', 'generis');
define('API_PASSWORD', md5('g3n3r1s'));
core_control_FrontController::connect(API_LOGIN, API_PASSWORD, DATABASE_NAME);
$d = new ImportDtisResult();
$dtisArray["TAO_PROCESS_EXEC_ID"] = "http://localhost/middleware/taoqti__rdf#iproc3";
$dtisArray["TAO_DELIVERY_ID"] = "http://localhost/middleware/taoqti__rdf#delivery2";
$dtisArray["TAO_TEST_ID"] = "http://localhost/middleware/taoqti__rdf#test1";
$dtisArray["TAO_ITEM_ID"] = "http://localhost/middleware/taoqti__rdf#item1";
$dtisArray["TAO_SUBJECT_ID"] = "http://localhost/middleware/taoqti__rdf#subject1";
// the variable infos
$key = "Score3";
$value = "valeurs reinc333  ";//item 1 test 2";

$d->addResultVariable($dtisArray, $key, $value);
?>
