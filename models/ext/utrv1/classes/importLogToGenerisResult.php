<?php
/* 
 * 
 */
// dans ce scipt nous allon,s fournir les po


//tester un peu le import selon le xpath
define ('inputFile','test1.xml');

//require_once($_SERVER['DOCUMENT_ROOT'].'/taoResults/models/ext/utrv1/classes/RegCommon.php');

require_once($_SERVER['DOCUMENT_ROOT']."generis/common/inc.extension.php");
require_once($_SERVER['DOCUMENT_ROOT']."taoResults/includes/common.php");

define ("CLASS_TEST",'TEST');
define ("ID_TEST",'ID_TEST');

define ("RESULT_NS",'http://127.0.0.1/middleware/demo.rdf#');

//
class importLog {
    public $domRusult;
    public $resultDom;

    public function __construct($domSom) {//our dev teame Som
        $logDom = new DOMDocument();
        //$logDom->load(inputFile);
        $logDom = $domSom;

        $this->resultDom = $logDom;
        $this->createResultInstance();

        return 'OK';

    }

    //    public function createDOM() {
    //    //declarer le dom
    //
    //        $logDom = new DOMDocument();
    //        $logDom->load(inputFile);
    //        return $logDom;
    //    }
    //
    //    public function getAttValueByXpath($nodePath, $att) {
    //        $dom = new DOMDocument();
    //        //get the actual dom
    //        $dom = $this->domRusult;//$this->createDOM(inputFile);
    //        //intialize th xpath
    //        $xp = new DOMXPath($dom);
    //        $node=$xp->query($nodePath);
    //        $val = $node->item(0)->getAttribute($att);
    //        return $val;
    //
    //    }
    //
    //    //get attribute value by tag name
    //
    //    public function getValueByNodeName() {
    //
    //
    //    }
    //
    //    public function getAttValueDom($nodeName,$att) {
    //        $dom = new DOMDocument();
    //        $dom = $this->createDOM(inputFile);
    //        $node = $dom->getElementsByTagName($nodeName);
    //        $val = $node->item(0)->getAttribute($att);
    //
    //
    //        return $val;
    //
    //    }
    //    //Create an instance for the test class

    public function createTestInstances() {


    }

    //The bigest method, to create the whole instances of all classes in the result model
    public function createResultInstance() {
        $dom = $this->resultDom;
        //explecit connect to generis with black door hhhhh
        define('API_LOGIN','generis');
        define('API_PASSWORD',md5('g3n3r1s'));
        core_control_FrontController::connect(API_LOGIN, API_PASSWORD, DATABASE_NAME);

        //Get test attribute value
        $testDom= $dom->getElementsByTagName("TEST")->item(0);

        $ID_TEST =          $testDom->getAttribute("rdfid");

        $LABEL_TEST=        $testDom->getAttribute("rdfs:Label");
        $COMMENT_TEST=      $testDom->getAttribute("rdfs:Comment");
        //*******************
        $testDom= $dom->getElementsByTagName("HASSCORINGMETHOD")->item(0);
        $HASSCORINGMETHOD_NAME=$testDom->getAttribute("tao:NAME");
        //**********************
        $testDom= $dom->getElementsByTagName("SCORE")->item(0);
        $SCORE_VALUE=       $testDom->getAttribute("tao:VALUE");
        //*******************
        $testDom= $dom->getElementsByTagName("CUMULMODEL")->item(0);
        $CUMULMODEL_NAME=   $testDom->getAttribute("tao:NAME");
        //**********************
        $testDom= $dom->getElementsByTagName("SUBJECT")->item(0);
        $SUBJECT_ID =       $testDom->getAttribute("rdfid");
        $SUBJECT_LABEL =    $testDom->getAttribute("rdfs:Label");
        $TESTBEHAVIOR= '';     // puted after adding the instance in testbehavir class

        //Create instance and property values

        $class = new core_kernel_classes_Class(RESULT_NS."TEST_CLASS");
        $instanceTest = $class->createInstance("test of student");
        //put property values /**** test with 3 properties only

        $propTest = new core_kernel_classes_Property(RESULT_NS."ID_TEST");
        $instanceTest->setPropertyValue($propTest,$ID_TEST );

        $propTest = new core_kernel_classes_Property(RESULT_NS."HASSCORINGMETHOD_NAME");
        $instanceTest->setPropertyValue($propTest,$HASSCORINGMETHOD_NAME );

        $propTest = new core_kernel_classes_Property(RESULT_NS."SUBJECT_ID");
        $instanceTest->setPropertyValue($propTest,$SUBJECT_ID );



        // echo $ID_TEST. " -- ". $CUMULMODEL_NAME. '--- '.$SUBJECT_LABEL;


        $CITEM= '';            // puted after adding the instance to citem class


        //Get Citem attribute
        $citemDomList = $dom->getElementsByTagName("CITEM");//

        foreach($citemDomList as $citemDom) {
        //get attributes values
            $WEIGHT = $citemDom->getAttribute("tao:WEIGHT");

            $MODEL=$citemDom->getAttribute("tao:MODEL");

            $DEFINITIONFILE=$citemDom->getAttribute("tao:DEFINITIONFILE");

            $SEQUENCE=$citemDom->getAttribute("tao:SEQUENCE");

            $ENDORSMENT=$citemDom->getAttribute("tao:ENDORSMENT");

            $ITEMUSAGE=$citemDom->getAttribute("tao:ITEMUSAGE");

            $ITEMBEHAVIOR='';//puted after adding the itemBehavior class
            //*****************************************************

            //Create instance of citem
            $class = new core_kernel_classes_Class(RESULT_NS."CITEM_CLASS");
            $instanceCitem = $class->createInstance("test of CITEM");
            //put property values /**** test with 3 properties only

            $propCitem = new core_kernel_classes_Property(RESULT_NS."WEIGHT");
            $instanceCitem->setPropertyValue($propCitem,$WEIGHT );

            $propCitem = new core_kernel_classes_Property(RESULT_NS."SEQUENCE");
            $instanceCitem->setPropertyValue($propCitem,$SEQUENCE );

            //*** The rage value of Test Instance/ THE Citem property
            $CITEM = $instanceCitem->uriResource;
            $propTest = new core_kernel_classes_Property(RESULT_NS."CITEM");
            $instanceTest->setPropertyValue($propTest,$CITEM );


            //echo "<br>".$WEIGHT."- ".$DEFINITIONFILE. " - ".$SEQUENCE;
            //*******************************************************************************
            //Get itemBehavior attribut values of the actual Citem

            $itemBehaviorDomList = $citemDom->getElementsByTagName("ITEMBEHAVIOR");
            foreach($itemBehaviorDomList as $itemBehaviorDom) {

                $LISTENERNAME=$itemBehaviorDom->getAttribute("tao:LISTENERNAME");
                $LISTENERVALUE=$itemBehaviorDom->getAttribute("tao:LISTENERNAME");

                //Create Instances of itemBehavior
                //Create instance of citem
                $class = new core_kernel_classes_Class(RESULT_NS."ITEMBEHAVIOR_CLASS");
                $instanceIB = $class->createInstance("test of ItemBehavior");

                $propIB = new core_kernel_classes_Property(RESULT_NS."LISTENERNAME");
                $instanceIB->setPropertyValue($propIB,$LISTENERNAME );

                $propIB = new core_kernel_classes_Property(RESULT_NS."LISTENERVALUE");
                $instanceIB->setPropertyValue($propIB,$LISTENERVALUE );
                //The range of Citem instance / The ITEMBEHAVIOR
                $ITEMBEHAVIOR = $instanceIB->uriResource;
                $propCitem = new core_kernel_classes_Property(RESULT_NS."ITEMBEHAVIOR");
                $instanceCitem->setPropertyValue($propCitem,$ITEMBEHAVIOR );


            //echo "<br>***** ". $LISTENERNAME;

            }



        }

    //Get itemBehavior attribut value
    //        $LISTENERNAME=$itemBehaviorDom->getAttribute("tao:LISTENERNAME");
    //
    //        $LISTENERVALUE=$itemBehaviorDom->getAttribute("tao:LISTENERNAME");

    }


}
//This class build the result model, by creatin the differents classes abd properties,
//to have the name space of the uri, we can cretae a FASTOCHE resource, get its uri and get also, the name space delimited by #
//name space#uriuri

class ResultModelBuilder {
//create class with all properties that are included in the paramaters
    public $uriRootClassOfResult ="http://www.tao.lu/Ontologies/TAOResult.rdf#Result";

    public function createModel($domSom) {
        $domResult = new DOMDocument();
        $domResult->load('model.xml');
        //get class info

        $listClasses = $domResult->getElementsByTagName("class");


        foreach($listClasses as $class) {
        //labelClass = $class->getAttribute("label");
            $uriClass = $class->getAttribute("uri");
            //make the case upper, more
            $uriClass = strtoupper($uriClass);

            $labelClass = "label on ".$uriClass;

            //For the actual class we buuil the array of properties
            $listProperties = $class->getElementsByTagName("property");
            $t= array();
            foreach($listProperties as $propertyDescription) {
                $propDescription=array();

                $uriProp = $propertyDescription->getAttribute("uri");
                //Make the case upper
                $uriProp = strtoupper($uriProp);

                $labelProp =$propertyDescription->getAttribute("label");

                $propDescription['labelProperty']='label '.$uriProp;
                $propDescription['commentProperty']='comment test '.$uriProp;
                $propDescription['uriProperty']='#'.$uriProp;
                $t[]=$propDescription;

            // echo "----------".$labelProp.'<br>';

            }
            //Create the class
            $this->createClassWithProperties($this->uriRootClassOfResult, '#'.$uriClass, $labelClass, '$commentClass', $t);

        }

    }

    public function createClassWithProperties($uriRootClass,$uriClass,$labelClass,$commentClass,$tabProperties) {
        $r = new RegCommon();
        $r->regConnect();

        //create the class
        $rdfClass = new core_kernel_classes_Class(RDF_CLASS);
        $resourceClass = $rdfClass->createInstance($labelClass,$commentClass,$uriClass);
        //now, create the php object Class to be used in trhe creation of the properties
        //This class should be linked to the URI already created
        $trClass =new core_kernel_classes_Class($resourceClass->uriResource);
        //now we have the php object linked to the class in the model
        //echo $resourceClass->uriResource . " <br>";

        //Create the property with a specific uri
        foreach( $tabProperties as $propDescription) {
        //get property infos
            $labelProperty = $propDescription['labelProperty'];
            $commentProperty =  $propDescription['commentProperty'];
            $uriProperty = $propDescription['uriProperty'];

            //Created the property with informations extracted

            $rdfProperty = new core_kernel_classes_Class(RDF_PROPERTY);
            $resourceProperty = $rdfProperty->createInstance($labelProperty,$commentProperty,$uriProperty);
            //Create the pho pbject property and link it with the uri
            $trProperty = new core_kernel_classes_Property($resourceProperty->uriResource);
            //Link the property with the class
            $trClass->setProperty($trProperty);


        }//end for each add properties

        ///add range properties
        $prop = new core_kernel_classes_Property(RESULT_NS."CITEM");
        $class = new core_kernel_classes_Class(RESULT_NS."CITEM_CLASS");
        $prop->setRange($class);

        $prop = new core_kernel_classes_Property(RESULT_NS."ITEMBEHAVIOR");
        $class = new core_kernel_classes_Class(RESULT_NS."ITEMBEHAVIOR_CLASS");
        $prop->setRange($class);

        //Put the class as subclass of root
        $rootResultClass = new core_kernel_classes_Class($uriRootClass);
        $trClass->setSubClassOf($rootResultClass);
    //end


    }
}
$logDom = new DOMDocument();
//get the dom from delivery
$xml = urldecode($_GET['resultxml']);
$logDom->loadXML($xml);

unset($_GET['resultxml']);
//$logDom->load(inputFile);
//echo $_SERVER['DOCUMENT_ROOT']."generis/common/inc.extension.php";

$p = new importLog($logDom);




?>
