<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/

/**
 * Description of classrevItemCapacity
 *
 * @author User
 */

require_once($_SERVER['DOCUMENT_ROOT']."/generis/common/inc.extension.php");
require_once($_SERVER['DOCUMENT_ROOT']."/taoResults/includes/common.php");
define ('RIC_URI','ricTQ5');

class classrevItemCapacity {
    private $namespace;
    public function  __construct() {
        // core_control_FrontController::connect($_SESSION['login'], $_SESSION['password'], DATABASE_NAME);
		
		define('API_LOGIN','tao');
        define('API_PASSWORD',md5('tao'));
		// core_control_FrontController::connect(API_LOGIN, API_PASSWORD, DATABASE_NAME);
		
		$this->namespace = core_kernel_classes_Session::getNameSpace().'#';
    }
    //put your code here

    public function buildModel() {

        $rdfClass = new core_kernel_classes_Class(RDF_CLASS);
        $resourceClasse = $rdfClass->createInstance("RIC 5 Class", '$comment', '#'.RIC_URI);
        $riCapacity = new core_kernel_classes_Class($resourceClasse->uriResource);

        //create properties

        $comment = "";

        $rdfProperty = new core_kernel_classes_Class(RDF_PROPERTY);
        $resourceProperty = $rdfProperty->createInstance("ideReviewer", $comment, "#idReviewer");
        $ricProperty = new core_kernel_classes_Property($resourceProperty->uriResource);

        $riCapacity->setProperty($ricProperty);

        $rdfProperty = new core_kernel_classes_Class(RDF_PROPERTY);
        $resourceProperty = $rdfProperty->createInstance("idTest", $comment, "#idTest");
        $ricProperty = new core_kernel_classes_Property($resourceProperty->uriResource);
        $riCapacity->setProperty($ricProperty);

        $rdfProperty = new core_kernel_classes_Class(RDF_PROPERTY);
        $resourceProperty = $rdfProperty->createInstance("idItem", $comment, "#idItem");
        $ricProperty = new core_kernel_classes_Property($resourceProperty->uriResource);

        $riCapacity->setProperty($ricProperty);

        $rdfProperty = new core_kernel_classes_Class(RDF_PROPERTY);
        $resourceProperty = $rdfProperty->createInstance("capacity", $comment, "#capacity");
        $ricProperty = new core_kernel_classes_Property($resourceProperty->uriResource);

        $riCapacity->setProperty($ricProperty);

        $rdfProperty = new core_kernel_classes_Class(RDF_PROPERTY);
        $resourceProperty = $rdfProperty->createInstance("comment", $comment, "#comment");
        $ricProperty = new core_kernel_classes_Property($resourceProperty->uriResource);

        $riCapacity->setProperty($ricProperty);

        //put as subclasse of result
        $rootRIC = new core_kernel_classes_Class("http://www.tao.lu/Ontologies/TAOResult.rdf#Result");
        $riCapacity->setSubClassOf($rootRIC);

    }
    // create ric Instance
    public function createRicInstance($idRev,$idTest,$idItem,$capacity,$comment) {


        $ricClass = new core_kernel_classes_Class($this->namespace.RIC_URI);
        $ricInstance = $ricClass->createInstance("inst1");



        $idRevProp = new core_kernel_classes_Property($this->namespace."idReviewer");
        $commentProp = new core_kernel_classes_Property($this->namespace."comment");
        $capacityProp = new core_kernel_classes_Property($this->namespace."capacity");
        $idItemProp = new core_kernel_classes_Property($this->namespace."idItem");
        $idTestProp = new core_kernel_classes_Property($this->namespace."idTest");

        $ricInstance->editPropertyValues($idRevProp, $idRev);
        $ricInstance->editPropertyValues($idTestProp, $idTest);
        $ricInstance->editPropertyValues($idItemProp, $idItem);
        $ricInstance->editPropertyValues($capacityProp, $capacity);
        $ricInstance->editPropertyValues($commentProp, $comment);

        return $ricInstance;
        //$ricInstance->edi


    }

    //get ricInformation
    // create ric Instance
    public function getRicInformation($idRev,$idTest,$idItem) {


        $ricClass = new core_kernel_classes_Class($this->namespace.RIC_URI);
        $listRic = $ricClass->getInstances();

        $ricMatched = '';
        foreach($listRic as $uri=>$resource) {
            $ricInstance = new core_kernel_classes_Resource($uri);

            $idRevProp = new core_kernel_classes_Property($this->namespace."idReviewer");
            $commentProp = new core_kernel_classes_Property($this->namespace."comment");
            $capacityProp = new core_kernel_classes_Property($this->namespace."capacity");
            $idItemProp = new core_kernel_classes_Property($this->namespace."idItem");
            $idTestProp = new core_kernel_classes_Property($this->namespace."idTest");
            // get property values as tab
            $idRevVal = $ricInstance->getPropertyValues($idRevProp);
            $idTestVal = $ricInstance->getPropertyValues($idTestProp);
            $idItemVal = $ricInstance->getPropertyValues($idItemProp);
            $capacityVal = $ricInstance->getPropertyValues($capacityProp);
            $commentVal = $ricInstance->getPropertyValues($commentProp);
            //print_r($commentVal);

            // get the first and the unique value of the property
            $idRevFinalValue = $idRevVal[0];
            $idTestFinalValue = $idTestVal[0];
            $idItemFinalValue = $idItemVal[0];
            $capacityFinalValue = $capacityVal[0];
            $commentFinalValue = $commentVal[0];

            //find the matched instance
            if (($idRevFinalValue==$idRev)&&($idTestFinalValue==$idTest)&&($idItemFinalValue == $idItem)) {
                $ricMatched = $uri;
                break;
            }


        }// end foreach
        $ricUri = $ricMatched;

        if ($ricMatched=='') {// add new instance

            $capacityFinalValue='no';
            $commentFinalValue = 'No comment !';
            $newRicInstance = $this->createRicInstance($idRev, $idTest, $idItem, $capacityFinalValue, $commentFinalValue);
            $ricUri = $newRicInstance->uriResource;

        }
        $res['uriRic']=$ricUri;
        $res['capacity']=$capacityFinalValue;
        $res['comment']= $commentFinalValue;

        return $res;

    }
    // set the value of capacity and commment of the inputed instance
    public function setRicInformation($uriRic,$capacity,$comment) {

        $ricInstance = new core_kernel_classes_Resource($uriRic);
        // connect the properties
        $commentProp = new core_kernel_classes_Property($this->namespace."comment");
        $capacityProp = new core_kernel_classes_Property($this->namespace."capacity");
        // set values
        $ricInstance->editPropertyValues($capacityProp, $capacity);
        $ricInstance->editPropertyValues($commentProp, $comment);


    }


    public function getListOfRic() {
        $ricClass = new core_kernel_classes_Class($this->namespace.RIC_URI);
        $t=$ricClass->getInstances();
        return $t;

    }
    // get rc of all reviewers
    public function getRicAllReviewers($idTest,$idItem) {

        $ricClass = new core_kernel_classes_Class($this->namespace.RIC_URI);
        $listRic = $ricClass->getInstances();
        $listRicFinal = array();


        foreach($listRic as $uri=>$resource) {
            $ricInstance = new core_kernel_classes_Resource($uri);

            $idRevProp = new core_kernel_classes_Property($this->namespace."idReviewer");
            $commentProp = new core_kernel_classes_Property($this->namespace."comment");
            $capacityProp = new core_kernel_classes_Property($this->namespace."capacity");
            $idItemProp = new core_kernel_classes_Property($this->namespace."idItem");
            $idTestProp = new core_kernel_classes_Property($this->namespace."idTest");
            // get property values as tab
            $idRevVal = $ricInstance->getPropertyValues($idRevProp);
            $idTestVal = $ricInstance->getPropertyValues($idTestProp);
            $idItemVal = $ricInstance->getPropertyValues($idItemProp);
            $capacityVal = $ricInstance->getPropertyValues($capacityProp);
            $commentVal = $ricInstance->getPropertyValues($commentProp);
            //print_r($commentVal);

            // get the first and the unique value of the property
            $idRevFinalValue = $idRevVal[0];
            $idTestFinalValue = $idTestVal[0];
            $idItemFinalValue = $idItemVal[0];
            $capacityFinalValue = $capacityVal[0];
            $commentFinalValue = $commentVal[0];

            //find the matched instance
            if (($idTestFinalValue==$idTest)&&($idItemFinalValue == $idItem)) {

                $res = new core_kernel_classes_Property($idRevFinalValue);
                $label = $res->getLabel();

                $ricInfo['idRev'] = $idRevFinalValue;
                $ricInfo['capacity'] = $capacityFinalValue;
                $ricInfo['comment']= $commentFinalValue;
                $ricInfo['labelRev'] = $label;

                $listRicFinal[]=$ricInfo;
            }


        }// end foreach
        return $listRicFinal;
    }
    //the dispatch
    public function dispatch() {

        if (isset($_POST)) {


            if ($_POST['revOp']=='getRicInformation') {
                $idRev = $_POST['ricRev'];
                $idTest = $_POST['ricTest'];
                $idItem = $_POST['ricItem'];
                $t = $this->getRicInformation($idRev, $idTest, $idItem);


                echo(json_encode($t));

            }
            // set ric inf
            if ($_POST['revOp']=='setRicInformation') {
                $ricUri = $_POST['ricUriS'];
                $capacity = $_POST['ricCapacityS'];
                $comment = $_POST['ricCommentS'];
                $this->setRicInformation($ricUri, $capacity, $comment);
                

                echo 'ok';



            }
            // get the final ric
            if ($_POST['revOp']=='getRicAllReviewers') {
// send only if we are in revFinal
                if ($_SESSION['revType'] == 'revFinal'){
                $idTest = $_SESSION['revTestId'];
                $idItem = $_SESSION['revItemId'];
                $t = $this->getRicAllReviewers($idTest, $idItem);
                echo(json_encode($t));
                }

            }



        }
    }



}
$ric = new classrevItemCapacity();
$ric->dispatch();
//$ric->buildModel();
//$ric->createRicInstance("nedjmafgf", "test", "item", "N", " walou walou");

//$ric->createRicInstance("ramadhan", "test2", "item2", "yes", " walou sdqsdqsds");


//$ric->getListOfRic();
//$t = $ric->getRicInformation("younes", "test1", "item3");
//print_r($t);
//$ric->setRicInformation("http://localhost/middleware/tao.rdf#i1281804459016561800", "nono", "je ne peu pas ");

?>
