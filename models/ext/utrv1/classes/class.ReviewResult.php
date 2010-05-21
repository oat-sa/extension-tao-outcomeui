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


        //create the property LISTENERVALUE
        $ibEndorsmentListnerValue = new core_kernel_classes_Property($uriListnerValueProp);
        //get the valu of the instance uriIB for the the property LISTENERVALUE
        $utrResource = new core_kernel_classes_Resource($uri);
        $endorsement = $utrResource->getPropertyValues($ibEndorsmentListnerValue);

        $listenerName = $utrResource->getPropertyValues(new core_kernel_classes_Property($uriListenerNameProp));
        $idTest = $utrResource->getPropertyValues(new core_kernel_classes_Property($uriIDTestProp));
        $subjectId = $utrResource->getPropertyValues(new core_kernel_classes_Property($uriSubjectProp));
        $itemId = $utrResource->getPropertyValues(new core_kernel_classes_Property($uriItemIdProp));

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


        $ibInformationValues['endorsement']= $endorsement[0];
        $ibInformationValues['listenerName']= $listenerName[0];
        $ibInformationValues['$iDTest']= $idTestValue;
        $ibInformationValues['subjectId']= $subjectIdValue;
        $ibInformationValues['itemId']= $itemIdValue;

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

        //filter the list according to have only the endorssment
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

    public function getIbEndorsementValues() {
        $listIbInstances = $this->getItemBehaviorInstances();
        $listEndorsementValues = array();

        foreach($listIbInstances as $uriIB=>$resource ) {
            $endorsementValues =$this->getIbEndorsemenInformationValues($uriIB);
            $listEndorsementValues[$uriIB] = $endorsementValues;

        }

        return $listEndorsementValues;

    }

}
echo '***************************younes <br><br>';


$r = new ReviewResult();
print_r($r->getIbEndorsementValues());

/*$uriIB = "http://localhost/middleware/tao3.rdf#i1274357746084423200";
$r->getIbEndorsmentValue($uriIB);*/

?>
