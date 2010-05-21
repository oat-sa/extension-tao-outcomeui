<?php

/**
 This class manage the reviewinf facilities of an item
 *
 *
 * @author djaghloul
 */

require_once('class.RegCommon.php');

class ReviewResult {
    public function  __construct() {
// A supprimer lors du deploiment final
        define('API_LOGIN','younes');
        define('API_PASSWORD',md5('123456'));
        core_control_FrontController::connect(API_LOGIN, API_PASSWORD, DATABASE_NAME);

        $p = new  RegCommon();
        $p->regConnect();

    }

    /**
     * Get the endorsement value for according to Uri
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  String UriIB
     *
     */

    public function getIbEndorsemenInformationValues($uriIB) {

        $uri = $uriIB;
        $ibInformationValues = array();// the returned array
        //get the uri of the property LISTENERVALUE
        $RESULT_NS = core_kernel_classes_Session::getNameSpace();

        //Create property connexion
        $uriListnerValueProp = $RESULT_NS.'#'.'LISTENERVALUE';
        $uriListenerNameProp = $RESULT_NS.'#'.'LISTENERNAME';
        $uriIDTestProp = $RESULT_NS.'#'.'ID_TEST';
        $uriSubjectProp = $RESULT_NS.'#'.'SUBJECT_ID';
        $uriItemIdProp = $RESULT_NS.'#'.'ITEM_ID';
        //reviwer information
        $uriRevId_1Prop = $RESULT_NS.'#'.'REVIEWER1_ID'.
        $uriRevComment_1Prop = $RESULT_NS.'#'.'REVIEWER1_COMMENT';
        $uriRevEndorsement_1Prop = $RESULT_NS.'#'.'REVIWER1_ENDORSEMENT';

        $uriRevId_2Prop = $RESULT_NS.'#'.'REVIEWER2_ID'.
        $uriRevComment_2Prop = $RESULT_NS.'#'.'REVIEWER2_COMMENT';
        $uriRevEndorsement_2Prop = $RESULT_NS.'#'.'REVIWER2_ENDORSEMENT';

        $uriRevComment_FinalProp = $RESULT_NS.'#'.'FINAL_COMMENT';
        $uriRevEndorsement_FinalProp = $RESULT_NS.'#'.'FINAL_ENDORSEMENT';


        //create the property LISTENERVALUE
        $ibEndorsmentListnerValue = new core_kernel_classes_Property($uriListnerValueProp);
        //get the valu of the instance uriIB for the the property LISTENERVALUE
        $utrResource = new core_kernel_classes_Resource($uri);
        $endorsement = $utrResource->getPropertyValues($ibEndorsmentListnerValue);

        $listenerName = $utrResource->getPropertyValues(new core_kernel_classes_Property($uriListenerNameProp));
        $idTest = $utrResource->getPropertyValues(new core_kernel_classes_Property($uriIDTestProp));
        $subjectId = $utrResource->getPropertyValues(new core_kernel_classes_Property($uriSubjectProp));
        $itemId = $utrResource->getPropertyValues(new core_kernel_classes_Property($uriItemIdProp));

        $revId_1 = $utrResource->getPropertyValues(new core_kernel_classes_Property($uriRevId_1Prop));
        $revComment_1 = $utrResource->getPropertyValues(new core_kernel_classes_Property($uriRevComment_1Prop));
        $revEndorsement_1 = $utrResource->getPropertyValues(new core_kernel_classes_Property($uriRevEndorsement_1Prop));
        
        $revId_2 = $utrResource->getPropertyValues(new core_kernel_classes_Property($uriRevId_2Prop));
        $revComment_2 = $utrResource->getPropertyValues(new core_kernel_classes_Property($uriRevComment_2Prop));
        $revEndorsement_2 = $utrResource->getPropertyValues(new core_kernel_classes_Property($uriRevEndorsement_2Prop));
        
        $revComment_Final = $utrResource->getPropertyValues(new core_kernel_classes_Property($uriRevComment_FinalProp));
        $revEndorsement_Final = $utrResource->getPropertyValues(new core_kernel_classes_Property($uriRevEndorsement_FinalProp));


        
        

        //test the variables
        $idTestValue = '';
        if (isset($idTest[0])){
            $idTestValue = $idTest[0];
        }

        $subjectIdValue = '';
        if (isset($subjectId[0])){
            $subjectIdValue = $subjectId[0];
        }

        $itemIdValue = '';
        if (isset($itemId[0])){
            $itemIdValue = $itemId[0];
        }
        $ibInformationValues['uriPassedItem']= $uriIB;
        $ibInformationValues['endorsement']= $endorsement[0];
        $ibInformationValues['listenerName']= $listenerName[0];
        $ibInformationValues['iDTest']= $idTestValue;
        $ibInformationValues['subjectId']= $subjectIdValue;
        $ibInformationValues['itemId']= $itemIdValue;

        $ibInformationValues['revId_1']= $revId_1[0];
        $ibInformationValues['revComment_1']= $revComment_1[0];
        $ibInformationValues['revEndorsement_1']= $revEndorsement_1[0];

        $ibInformationValues['revId_2']= $revId_2[0];
        $ibInformationValues['revComment_2']= $revComment_2[0];
        $ibInformationValues['revEndorsement_2']= $revEndorsement_2[0];

        $ibInformationValues['revComment_Final']= $revComment_Final[0];
        $ibInformationValues['revEndorsement_Final']= $revEndorsement_Final[0];
        
        return $ibInformationValues;

    }

    /**
     * Get instances of itemBehavior and filter the according to endorssement and other criterais
     *
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param
     *
     */

    public function getItemBehaviorInstances() {
        $RESULT_NS = core_kernel_classes_Session::getNameSpace();
        $uriItemBehavior = $RESULT_NS.'#'.'ITEMBEHAVIOR_CLASS';
        $utrClass = new core_kernel_classes_Class($uriItemBehavior);

        $listOfItemBehavior =$utrClass->getInstances(true);

        //filter the list according to have only the endorsement and other criterias
        return ($listOfItemBehavior);

    }

    /**
     * Get the endorsement values of inputed instances
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param
     *
     */

    public function getItermBehaviorInformation() {
        $listIbInstances = $this->getItemBehaviorInstances();
        $listEndorsementValues = array();

        foreach($listIbInstances as $uriIB=>$resource ) {
            $endorsementValues =$this->getIbEndorsemenInformationValues($uriIB);
            $listEndorsementValues[] = $endorsementValues;

        }

        return $listEndorsementValues;

    }

    public function dispatch(){
        if (isset($_POST['revOp'])){
            

            //get itemBehavior information
            if ($_POST['revOp'] == 'getItermBehaviorInformation'){
                //get the filter options. Otherwise one uses all itemBehavior instances

                $list= $this->getItermBehaviorInformation();
                echo (json_encode($list));
                

            }
         
        }
    }

}

//session_destroy();
$r = new ReviewResult();

error_reporting(0);
$r->dispatch();
error_reporting(-1);
/*$uriIB = "http://localhost/middleware/tao3.rdf#i1274357746084423200";
$r->getIbEndorsmentValue($uriIB);*/

?>
