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

            // Create the label of the new instance
            $dtisLabel = $processLabel . "_" . $deliveryLabel . "_" . $testLabel . "_" . $itemLabel . "_" . $subjectLabel . "_" . date("Y/m/d_H:i:s"); // todo label of dtis + date
            $dtisComment = "Result Recieved the : " . date("Y/m/d_H:i:s");
            $dtisInstance = $dtisResultClass->createInstance($dtisLabel, $dtisComment);
            //Add the uri of the variable and its value to the array
            //Put the name only without the name space part
            $dtisUris['TAO_ITEM_VARIABLE_ID'] = $key;
            $dtisUris['TAO_ITEM_VARIABLE_VALUE'] = $value;
            //print_r($dtisUris);
            // Create the property Object
            foreach ($dtisUris as $dtisUri => $dtisValue) {
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
        $TAO_DELIVERY_RESULTS_CLASS = $resultNS . "#" . "TAO_DELIVERY_RESULTS"; // to modify in the last version
        $deliveryResultClass = new core_kernel_classes_Class($TAO_DELIVERY_RESULTS_CLASS);
        $listOfDeliveryClasses = $deliveryResultClass->getSubClasses();
        //compaiason
        $uri = $dtisUris["TAO_DELIVERY_ID"];
        list($ns, $lastpartUriDeliveryOfResult) = explode("#", $uri);
        //use the last part as uri for the search
        $localNS = core_kernel_classes_Session::getNameSpace();
        $uriDelivery = $localNS . "#" . $lastpartUriDeliveryOfResult;
        //check of not existance => create it with the same last part uri as the delivery
        echo " <br>test $uriDelivery <br>";
        print_r($listOfDeliveryClasses);
        if (array_key_exists($uriDelivery, $listOfDeliveryClasses===FALSE)) {
            echo "exist";
            $rdfClass = new core_kernel_classes_Class(RDF_CLASS);
            $labelClass = $deliveryLabel;
            $commentClass = " This result class contains all results for the " . $deliveryLabel . "delivery";
            $uriClass = '#' . $lastpartUriDeliveryOfResult;
            $resourceClass = $rdfClass->createInstance($labelClass, $commentClass, $uriClass);
            //now, create the php object Class to be used in trhe creation of the properties
            //This class should be linked to the URI already created
            $drClass = new core_kernel_classes_Class($resourceClass->uriResource);
            print_r($drClass);
            //set this class as sub class of TAO_DELIVERY_RESULTS ($deliveryResultClass)
            $drClass->setSubClassOf($deliveryResultClass);
        }


        



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
$dtisArray["TAO_PROCESS_EXEC_ID"] = "process ID";
$dtisArray["TAO_DELIVERY_ID"] = "gfgdfgferkjbkbnjkbnjkn#i1289486521036569309";
$dtisArray["TAO_TEST_ID"] = "test 2";
$dtisArray["TAO_ITEM_ID"] = "item2";
$dtisArray["TAO_SUBJECT_ID"] = "subject2fg";
// the variable infos
$key = " variable idfg";
$value = "var valuefgdf f dfsd1111";

$d->addResultVariable($dtisArray, $key, $value);
?>
