<?php
/**
 *
 *
 * @author Younes Djaghloul, CRP Henri Tudor
 * @package Result
 */

require_once($_SERVER['DOCUMENT_ROOT']."/generis/common/inc.extension.php");
require_once($_SERVER['DOCUMENT_ROOT']."/taoResults/includes/common.php");


class ImportDtisResult {



    public function __construct() {//our dev teame Som

    }

    // add new dtis Instance. the inputed array has this structure
    // array["TAO_DELIVERY_ID"]=" value "; the key of the array is the uri of propeties of dtis ResultClas
    // There is 6 variables TAO_DELIVERY_ID , TAO_TEST_ID, TAO_ITEM_ID, TAO_SUBJECT_ID, TAO_ITEM_VARIABLE_ID, TAO_ITEM_VARIABLE_VALUE


    public function addDtisResult($arrayOfDtisVariables) {


        //connect to the class of dtis Result Class
        $resultNS = core_kernel_classes_Session::getNameSpace();
        $itemResultClassURI = $resultNS."#"."TAO_ITEM_RESULTS";
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
        $dtisLabel = $deliveryLabel."_".$testLabel."_".$itemLabel."_".$subjectLabel."_".date("Y/m/d_H:i:s");// todo label of dtis + date
        $dtisComment = "Result Recieved the : ".date("Y/m/d_H:i:s");
        $dtisInstance = $dtisResultClass->createInstance($dtisLabel, $dtisComment);
         // set the value of properties
        foreach ($arrayOfDtisVariables as $dtisUri=>$dtisValue) {
            // Create the property Object
            $dtisProp = new core_kernel_classes_Property($resultNS."#".$dtisUri);
            // Set values of the instance
            $dtisInstance->setPropertyValue($dtisProp,$dtisValue );

        }

    }
}

//        define('API_LOGIN','generis');
//        define('API_PASSWORD',md5('g3n3r1s'));
//        core_control_FrontController::connect(API_LOGIN, API_PASSWORD, DATABASE_NAME);
//        $d = new ImportDtisResult();
//        $dtisArray["TAO_DELIVERY_ID"] = "delivery Test";
//        $dtisArray["TAO_TEST_ID"] = "test ";
//        $dtisArray["TAO_ITEM_ID"] = "item";
//        $dtisArray["TAO_SUBJECT_ID"] = "subject";
//        $dtisArray["TAO_ITEM_VARIABLE_ID"] = "variable id";
//        $dtisArray["TAO_ITEM_VARIABLE_VALUE"] = "var value";
//
//        $d->addDtisResult($dtisArray);




?>
