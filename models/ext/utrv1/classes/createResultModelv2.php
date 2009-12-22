<?php
/* 
 * 
 */
// dans ce scipt nous allon,s fournir les po


//tester un peu le import selon le xpath
define ('inputFile','resultFileTao2.xml');

//require_once('RegCommon.php');
//require_once($_SERVER['DOCUMENT_ROOT'].'/taoResults/models/ext/utrv1/classes/RegCommon.php');
require_once($_SERVER['DOCUMENT_ROOT']."/generis/common/inc.extension.php");
require_once($_SERVER['DOCUMENT_ROOT']."/taoResults/includes/common.php");

define ("CLASS_TEST",'TEST');
define ("ID_TEST",'ID_TEST');

define ("RESULT_NS",'http://127.0.0.1/middleware/demo.rdf#');
define ('RESULT_MODEL',$_SERVER['DOCUMENT_ROOT'].'/taoResults/models/ext/utrv1/classes/model.xml');

//
//This class build the result model, by creatin the differents classes abd properties,
//to have the name space of the uri, we can cretae a FASTOCHE resource, get its uri and get also, the name space delimited by #
//name space#uriuri

class ResultModelBuilder {
//create class with all properties that are included in the paramaters
    public $uriRootClassOfResult ="http://www.tao.lu/Ontologies/TAOResult.rdf#Result";

    public function createModel() {
        $domResult = new DOMDocument();
        $domResult->load(RESULT_MODEL);
        //get class info

        define('API_LOGIN','generis');
        define('API_PASSWORD',md5('g3n3r1s'));
        core_control_FrontController::connect(API_LOGIN, API_PASSWORD, DATABASE_NAME);

        $listClasses = $domResult->getElementsByTagName("class");

        foreach($listClasses as $class) {
        //labelClass = $class->getAttribute("label");
            $uriClass = $class->getAttribute("uri");
            //make the case upper, more
            $uriClass = strtoupper($uriClass);

            $labelClass = "".$uriClass;

            //For the actual class we buuil the array of properties
            $listProperties = $class->getElementsByTagName("property");
            $t= array();
            foreach($listProperties as $propertyDescription) {
                $propDescription=array();

                $uriProp = $propertyDescription->getAttribute("uri");
                //Make the case upper
                $uriProp = strtoupper($uriProp);

                $labelProp =$propertyDescription->getAttribute("label");

                $propDescription['labelProperty']=''.$uriProp;
                $propDescription['commentProperty']='comment '.$uriProp;
                $propDescription['uriProperty']='#'.$uriProp;
                $t[]=$propDescription;

            // echo "----------".$labelProp.'<br>';

            }
            //Create the class
            $this->createClassWithProperties($this->uriRootClassOfResult, '#'.$uriClass, $labelClass, '$comment', $t);

        }
        //add range properties

        $prop = new core_kernel_classes_Property(RESULT_NS."CITEM");
        $class = new core_kernel_classes_Class(RESULT_NS."CITEM_CLASS");
        $prop->setRange($class);

        $prop = new core_kernel_classes_Property(RESULT_NS."ITEMBEHAVIOR");
        $class = new core_kernel_classes_Class(RESULT_NS."ITEMBEHAVIOR_CLASS");
        $prop->setRange($class);

        //for subject antd other classes
        $prop = new core_kernel_classes_Property(RESULT_NS."SUBJECT_ID");
        $class = new core_kernel_classes_Class(TAO_SUBJECT_CLASS);

        $prop->setRange($class);


        $prop = new core_kernel_classes_Property(RESULT_NS."ID_TEST");
        $class = new core_kernel_classes_Class(TAO_TEST_CLASS);
        $prop->setRange($class);



    }

    public function createClassWithProperties($uriRootClass,$uriClass,$labelClass,$commentClass,$tabProperties) {
    //        $r = new RegCommon();
    //        $r->regConnect();

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
        //Put the class as subclass of root
        $rootResultClass = new core_kernel_classes_Class($uriRootClass);
        $trClass->setSubClassOf($rootResultClass);

    //end


    }
}


//$propDescription['labelProperty']='label test';
//$propDescription['commentProperty']='comment test ';
//$propDescription['uriProperty']='#testIDSubject';
//
//$t[]=$propDescription;
//
//$propDescription["labelProperty"]='label test2';
//$propDescription['commentProperty']='comment test2';
//$propDescription['uriProperty']='#testIDSubject2';
//
//$t[]=$propDescription;

$p= new ResultModelBuilder();
//echo $_SERVER['DOCUMENT_ROOT']."generis/common/inc.extension.php";
$p->createModel();


?>
