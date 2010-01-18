<?php
/**
 * Permits to add a new intance in the result class and subclass ACCORDING TO
 * RESULT FILE SUBMITED AFTER THE TEST
 *
 * @author Younes Djaghloul, CRP Henri Tudor
 * @package Result
 */



require_once($_SERVER['DOCUMENT_ROOT']."/generis/common/inc.extension.php");
require_once($_SERVER['DOCUMENT_ROOT']."/taoResults/includes/common.php");
/**
 * Permits to add a new intance in the result class and subclass ACCORDING TO
 * RESULT FILE SUBMITED AFTER THE TEST
 *
 * @access public
 * @author Younes Djaghloul, CRP Henri Tudor
 * @package Result
 */

class ImportLogToGenerisResult {
    public $domResult;
    public $resultDom;

     /**
     * Based on the dom, this method invokes thecreateResultInstance to create a
     * instance of the class Result and its sub class
     *
     * @access public
     * @author Younes Djaghloul, CRP Henri Tudor
     * @param  Object $domSom
     * @return String
     */

    public function __construct($domSom) {//our dev teame Som
        //get the name space



        $logDom = new DOMDocument();
        //$logDom->load(inputFile);
        $logDom = $domSom;

        $this->resultDom = $logDom;
        $this->createResultInstance();

        return 'OK';

    }

    


    /**
     * adds the instance of result according to the dom of the result
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return void
     */

    public function createResultInstance() {
        $dom = $this->resultDom;
        //explecit connect to generis with black door hhhhh
        define('API_LOGIN','generis');
        define('API_PASSWORD',md5('g3n3r1s'));
        core_control_FrontController::connect(API_LOGIN, API_PASSWORD, DATABASE_NAME);
        //Get the NameSpace of in order to add the instances
        $RESULT_NS = core_kernel_classes_Session::getNameSpace();
        $RESULT_NS = $RESULT_NS.'#';

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
        $TESTBEHAVIOR= '';     // puted after adding the instance in testbehavir class TODO

        //Create instance and property values

        $class = new core_kernel_classes_Class($RESULT_NS."TEST_CLASS");
        $instanceTest = $class->createInstance("Test...");
        //put property values /**** test with 3 properties only

        $propTest = new core_kernel_classes_Property($RESULT_NS."ID_TEST");
        $instanceTest->setPropertyValue($propTest,$ID_TEST );

        $propTest = new core_kernel_classes_Property($RESULT_NS."LABEL_TEST");
        $instanceTest->setPropertyValue($propTest,$LABEL_TEST );
        
        $propTest = new core_kernel_classes_Property($RESULT_NS."HASSCORINGMETHOD_NAME");
        $instanceTest->setPropertyValue($propTest,$HASSCORINGMETHOD_NAME );

        $propTest = new core_kernel_classes_Property($RESULT_NS."SCORE_VALUE");
        $instanceTest->setPropertyValue($propTest,$SCORE_VALUE );

        $propTest = new core_kernel_classes_Property($RESULT_NS."CUMULMODEL_NAME");
        $instanceTest->setPropertyValue($propTest,$CUMULMODEL_NAME );


        $propTest = new core_kernel_classes_Property($RESULT_NS."SUBJECT_ID");
        $instanceTest->setPropertyValue($propTest,$SUBJECT_ID );

        $propTest = new core_kernel_classes_Property($RESULT_NS."SUBJECT_LABEL");
        $instanceTest->setPropertyValue($propTest,$SUBJECT_LABEL );
        //TODO ADD TESTBEHAVIOR


        //----------------------------
        
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

            //Create instance of Citem
            $class = new core_kernel_classes_Class($RESULT_NS."CITEM_CLASS");
            $instanceCitem = $class->createInstance("Test of CITEM");
            //put property values /**** test with 3 properties only

            $propCitem = new core_kernel_classes_Property($RESULT_NS."WEIGHT");
            $instanceCitem->setPropertyValue($propCitem,$WEIGHT );

            $propCitem = new core_kernel_classes_Property($RESULT_NS."MODEL");
            $instanceCitem->setPropertyValue($propCitem,$MODEL );

            $propCitem = new core_kernel_classes_Property($RESULT_NS."DEFINITIONFILE");
            $instanceCitem->setPropertyValue($propCitem,$DEFINITIONFILE );
            

            $propCitem = new core_kernel_classes_Property($RESULT_NS."SEQUENCE");
            $instanceCitem->setPropertyValue($propCitem,$SEQUENCE );

            $propCitem = new core_kernel_classes_Property($RESULT_NS."ENDORSMENT");
            $instanceCitem->setPropertyValue($propCitem,$ENDORSMENT );

            $propCitem = new core_kernel_classes_Property($RESULT_NS."ITEMUSAGE");
            $instanceCitem->setPropertyValue($propCitem,$ITEMUSAGE );


            //*** The rage value of Test Instance/ THE Citem property
            $CITEM = $instanceCitem->uriResource;
            $propTest = new core_kernel_classes_Property($RESULT_NS."CITEM");
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
                $class = new core_kernel_classes_Class($RESULT_NS."ITEMBEHAVIOR_CLASS");
                $instanceIB = $class->createInstance("test of ItemBehavior");

                $propIB = new core_kernel_classes_Property($RESULT_NS."LISTENERNAME");
                $instanceIB->setPropertyValue($propIB,$LISTENERNAME );

                $propIB = new core_kernel_classes_Property($RESULT_NS."LISTENERVALUE");
                $instanceIB->setPropertyValue($propIB,$LISTENERVALUE );
                //The range of Citem instance / The ITEMBEHAVIOR
                $ITEMBEHAVIOR = $instanceIB->uriResource;
                $propCitem = new core_kernel_classes_Property($RESULT_NS."ITEMBEHAVIOR");
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
//CREATE THE DOM
$logDom = new DOMDocument();
//get the path of the xml result from delivery SERVICE
$xmlPath = $_GET['resultxml'];
$logDom->load($xmlPath);

unset($_GET['resultxml']);


$p = new importLogToGenerisResult($logDom);


?>
