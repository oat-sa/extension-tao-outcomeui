<?php

/**
 This class manage the reviewinf facilities of an item
 *
 *
 * @author djaghloul
 */

require_once('class.RegCommon.php');



class ReviewResult {
    private $revType;
    private $revIdCurrent;
    private $revTestId;
    private $revSubjectId;
    private $revItemId;


    public function  __construct() {
// A supprimer lors du deploiment final
        define('API_LOGIN','djaghloul');
        define('API_PASSWORD',md5('djaghloul'));
        core_control_FrontController::connect(API_LOGIN, API_PASSWORD, DATABASE_NAME);

        $p = new  RegCommon();
        $p->regConnect();
        //init the variables
        $this->revType = $_SESSION['revType'];
        $this->revIdCurrent=$_SESSION['revIdCurrent'];
        $this->revTestId=$_SESSION['revTestId'];
        $this->revSubjectId=$_SESSION['revSubjectId'];
        $this->revItemId=$_SESSION['revItemId'];


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
//echo ' hhl=kml';
        //Create property connexion

        $uriListnerValueProp = $RESULT_NS.'#'.'LISTENERVALUE';
        $uriListenerNameProp = $RESULT_NS.'#'.'LISTENERNAME';
        $uriIDTestProp = $RESULT_NS.'#'.'ID_TEST';
        $uriSubjectProp = $RESULT_NS.'#'.'SUBJECT_ID';
        $uriItemIdProp = $RESULT_NS.'#'.'ITEM_ID';
        //reviwer information
        $uriRevId_1Prop = $RESULT_NS.'#'.'REVIEWER1_ID';

        $uriRevComment_1Prop = $RESULT_NS.'#'.'REVIEWER1_COMMENT';

        $uriRevEndorsement_1Prop = $RESULT_NS.'#'.'REVIEWER1_ENDORSEMENT';

        $uriRevId_2Prop = $RESULT_NS.'#'.'REVIEWER2_ID';
        $uriRevComment_2Prop = $RESULT_NS.'#'.'REVIEWER2_COMMENT';
        $uriRevEndorsement_2Prop = $RESULT_NS.'#'.'REVIEWER2_ENDORSEMENT';

        $uriRevId_3Prop = $RESULT_NS.'#'.'REVIEWER3_ID';
        $uriRevComment_3Prop = $RESULT_NS.'#'.'REVIEWER3_COMMENT';
        $uriRevEndorsement_3Prop = $RESULT_NS.'#'.'REVIEWER3_ENDORSEMENT';

        $uriRevId_4Prop = $RESULT_NS.'#'.'REVIEWER4_ID';
        $uriRevComment_4Prop = $RESULT_NS.'#'.'REVIEWER4_COMMENT';
        $uriRevEndorsement_4Prop = $RESULT_NS.'#'.'REVIEWER4_ENDORSEMENT';


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

        //print_r($revId_1);

        $revComment_1 = $utrResource->getPropertyValues(new core_kernel_classes_Property($uriRevComment_1Prop));
        $revEndorsement_1 = $utrResource->getPropertyValues(new core_kernel_classes_Property($uriRevEndorsement_1Prop));

        $revId_2 = $utrResource->getPropertyValues(new core_kernel_classes_Property($uriRevId_2Prop));
        $revComment_2 = $utrResource->getPropertyValues(new core_kernel_classes_Property($uriRevComment_2Prop));
        $revEndorsement_2 = $utrResource->getPropertyValues(new core_kernel_classes_Property($uriRevEndorsement_2Prop));

        $revId_3 = $utrResource->getPropertyValues(new core_kernel_classes_Property($uriRevId_3Prop));
        $revComment_3 = $utrResource->getPropertyValues(new core_kernel_classes_Property($uriRevComment_3Prop));
        $revEndorsement_3 = $utrResource->getPropertyValues(new core_kernel_classes_Property($uriRevEndorsement_3Prop));

        $revId_4 = $utrResource->getPropertyValues(new core_kernel_classes_Property($uriRevId_4Prop));
        $revComment_4 = $utrResource->getPropertyValues(new core_kernel_classes_Property($uriRevComment_4Prop));
        $revEndorsement_4 = $utrResource->getPropertyValues(new core_kernel_classes_Property($uriRevEndorsement_4Prop));

        
        $revComment_Final = $utrResource->getPropertyValues(new core_kernel_classes_Property($uriRevComment_FinalProp));
        $revEndorsement_Final = $utrResource->getPropertyValues(new core_kernel_classes_Property($uriRevEndorsement_FinalProp));


        //print_r($revId_1);



        //test the variables
        $idTestValue = '';
        if (isset($idTest[0])) {
            $idTestValue = $idTest[0];
        }

        $subjectIdValue = '';
        if (isset($subjectId[0])) {
            $subjectIdValue = $subjectId[0];
        }

        $itemIdValue = '';
        if (isset($itemId[0])) {
            $itemIdValue = $itemId[0];
        }

        //put the reviewer id


        $ibInformationValues['uriPassedItem']= $uriIB;
        $ibInformationValues['endorsement']= $endorsement[0];
        $ibInformationValues['listenerName']= $listenerName[0];
        $ibInformationValues['iDTest']= $idTestValue;
        $ibInformationValues['subjectId']= $subjectIdValue;
        $ibInformationValues['itemId']= $itemIdValue;
        // put either the id in the ontology or the sent revId by the workflow and chose the appropriate reviewer


        $ibInformationValues['revId_1']= $revId_1[0];
        $ibInformationValues['revComment_1']=  $revComment_1[0];
        $ibInformationValues['revEndorsement_1']=  $revEndorsement_1[0];



        $ibInformationValues['revId_2']= $revId_2[0];
        $ibInformationValues['revComment_2']=  $revComment_2[0];
        $ibInformationValues['revEndorsement_2']=  $revEndorsement_2[0];

        $ibInformationValues['revId_3']= $revId_3[0];
        $ibInformationValues['revComment_3']=  $revComment_3[0];
        $ibInformationValues['revEndorsement_3']=  $revEndorsement_3[0];

        $ibInformationValues['revId_4']= $revId_4[0];
        $ibInformationValues['revComment_4']=  $revComment_4[0];
        $ibInformationValues['revEndorsement_4']=  $revEndorsement_4[0];


//in reviewer process
        if ($this->revType=='reviewer') {



            if ($revId_1[0]=='') {
                $ibInformationValues['revId_1']=  $this->revIdCurrent;
                $ibInformationValues['revNumber'] = 'rev1';
            }else {

            }
//second reviewer
            if (($revId_2[0]=='')&&($revId_1[0]!='')) {
                $ibInformationValues['revId_2']=  $this->revIdCurrent;
                $ibInformationValues['revNumber'] = 'rev2';
            }else {

            }

            if (($revId_3[0]=='')&&($revId_2[0]!='')) {
                $ibInformationValues['revId_3']=  $this->revIdCurrent;
                $ibInformationValues['revNumber'] = 'rev3';
            }else {

            }

            if (($revId_4[0]=='')&&($revId_3[0]!='')) {
                $ibInformationValues['revId_4']=  $this->revIdCurrent;
                $ibInformationValues['revNumber'] = 'rev4';
            }else {

            }

        }

        //in final reviwe process
        if ($this->revType=='revFinal') {
            $ibInformationValues['revNumber'] = 'revf';
        }
        $ibInformationValues['revComment_Final']=  $revComment_Final[0];
        $ibInformationValues['revEndorsement_Final']=  $revEndorsement_Final[0];

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

        //filter the list  to have only the endorsement and other criterias


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

    public function getItermBehaviorInformation($idTest,$idSubject,$idItem) {
        $listIbInstances = $this->getItemBehaviorInstances();

        $listEndorsementValues = array();

        foreach($listIbInstances as $uriIB=>$resource ) {

            $endorsementValues =$this->getIbEndorsemenInformationValues($uriIB);
            //do the filter
            if ((  $endorsementValues['listenerName']=='inquiryEndorsment')&& ($endorsementValues['iDTest'] ==$idTest) && ($endorsementValues['subjectId']==$idSubject) && ($endorsementValues['itemId']==$idItem)) {
                $listEndorsementValues[] = $endorsementValues;
            }

        }
//print_r($listEndorsementValues);
        return $listEndorsementValues;
    }



    /**
     * Set the review input
     *
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param      * $uriItemReviewed,$revId,$revComment,$revEndorsement
     *
     */

    public function setReviewInformation($uriItemReviewed,$revId,$revComment,$revEndorsement,$revNumber) {
        //put the reviewer's ni the ontology
        // create the link to the instance

        // get the property uris'
        //echo $uriItemReviewed.'<br>'.$revId;
        $RESULT_NS = core_kernel_classes_Session::getNameSpace();
        if ($revNumber =='rev1') {

            $uriRevIdProp = $RESULT_NS.'#'.'REVIEWER1_ID';
            $uriRevCommentProp = $RESULT_NS.'#'.'REVIEWER1_COMMENT';
            $uriRevEndorsementProp = $RESULT_NS.'#'.'REVIEWER1_ENDORSEMENT';
        }

        if ($revNumber =='rev2') {

            $uriRevIdProp = $RESULT_NS.'#'.'REVIEWER2_ID';
            $uriRevCommentProp = $RESULT_NS.'#'.'REVIEWER2_COMMENT';
            $uriRevEndorsementProp = $RESULT_NS.'#'.'REVIEWER2_ENDORSEMENT';
        }

        if ($revNumber =='rev3') {

            $uriRevIdProp = $RESULT_NS.'#'.'REVIEWER3_ID';
            $uriRevCommentProp = $RESULT_NS.'#'.'REVIEWER3_COMMENT';
            $uriRevEndorsementProp = $RESULT_NS.'#'.'REVIEWER3_ENDORSEMENT';
        }

        if ($revNumber =='rev4') {

            $uriRevIdProp = $RESULT_NS.'#'.'REVIEWER4_ID';
            $uriRevCommentProp = $RESULT_NS.'#'.'REVIEWER4_COMMENT';
            $uriRevEndorsementProp = $RESULT_NS.'#'.'REVIEWER4_ENDORSEMENT';
        }


        if ($revNumber =='revf') {

            $uriRevIdProp = $RESULT_NS.'#'.'FINAL_REVIEWER_ID';
            $uriRevCommentProp = $RESULT_NS.'#'.'FINAL_COMMENT';
            $uriRevEndorsementProp = $RESULT_NS.'#'.'FINAL_ENDORSEMENT';
        }

        $itemReviewed = new core_kernel_classes_Resource($uriItemReviewed);
        //create the properties
        $propRevId = new core_kernel_classes_Property($uriRevIdProp);


        $propRevComment = new core_kernel_classes_Property($uriRevCommentProp);
        //print_r($propRevComment);
        $proprevEndorsement = new core_kernel_classes_Property($uriRevEndorsementProp);


        //set values of properties

        $itemReviewed->editPropertyValues($propRevComment, $revComment);
        //print_r($itemReviewed);
        $itemReviewed->editPropertyValues($proprevEndorsement, $revEndorsement);
        $itemReviewed->editPropertyValues($propRevId, $revId);

    }

    public function dispatch() {
        if (isset($_POST['revOp'])) {

            //get itemBehavior information
            if ($_POST['revOp'] == 'getItermBehaviorInformation') {
                //get the filter options. Otherwise one uses all itemBehavior instances
                $idTest =$this->revTestId;// 'http://localhost/middleware/tao4.rdf#i1261572267020194300';
                $idSubject=$this->revSubjectId;//  'http://localhost/middleware/tao4.rdf#i1274434222052333200';
                $idItem=$this->revItemId;//  'http://localhost/middleware/tao4.rdf#i1274434065093789300';
                $list= $this->getItermBehaviorInformation($idTest,$idSubject,$idItem);

                echo (json_encode($list));

            }

            //set review information
            if ($_POST['revOp']=='setReviewInformation') {

                $uriItemReviewed=$_POST['uriItemReviewed'];
                $revNumber = $_POST['revNum'];
                $revId=$_POST['revId'];
                $revComment=$_POST['revComment'];
                $revEndorsement=$_POST['revEndorsement'];

                $this->setReviewInformation($uriItemReviewed, $revId, $revComment, $revEndorsement,$revNumber);
            }

        }
    }

}
//http://localhost/middleware/tao4.rdf#i1274964277010141500
//
//session_destroy();

$r = new ReviewResult();

error_reporting(0);
$r->dispatch();
error_reporting(-1);
/*$uriIB = "http://localhost/middleware/tao3.rdf#i1274357746084423200";
$r->getIbEndorsmentValue($uriIB);*/

?>
