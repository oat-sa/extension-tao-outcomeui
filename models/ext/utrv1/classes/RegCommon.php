<?php
/* 
 *Younes Djaghloul,CRP Henri Tudor, Luxembourg.
 */

//This class provides the common services on generis API

require('../../../../../generis/common/inc.extension.php');
require('../../../../includes/common.php');

class RegCommon {
//This method permits to connect to a specific module of generis.
    public function regConnect() {

        $session = core_kernel_classes_Session::singleton();
        $session->model->loadModel(RESULT_ONTOLOGY);
        $session->model->loadModel(ITEM_ONTOLOGY);
        $session->model->loadModel(GROUP_ONTOLOGY);
        $session->model->loadModel(TEST_ONTOLOGY);
        $session->model->loadModel(SUBJECT_ONTOLOGY);

    //Authentication

      /*  $login = "hyperclass";
        $password = md5("hyperclass");
        $module = "hyperclass";
        //Connect to generis API
        $connexion = new  core_control_FrontController();
        $connexion->connect($login,$password,$module);
        //return the variable in order to be used by other services

        if ($connexion->isConnected()) {
        //echo "#################### Connected to ". $module. " ######################";
        }
        return $connexion;*/
    }
    //get the binding info of specific property

    //Creates a n new instance for of the selected class, based on the dom of the Log

    public function createResultFromLog($resultClass,$logDOM) {


    }


    //get the range of class
    //student = http://127.0.0.1/middleware/hyperclass.rdf#121310317039702
    // to obtain a PHP object linked to the generis class, based on its URI
    public function linkClass($uriClass) {
        $trClass = new core_kernel_classes_Class($uriClass);
        return $trClass;
    }
    // to obtain a PHP object linked to the generis property, based on its URI
    public function linkProperty($uriProperty) {
        $trProperty = new core_kernel_classes_Property($uriProperty);
        return $trProperty;
    }

    //get the label,the uri of the range and the label of the range of a particular property
    function trGetPropertyInfo($uriProperty) {
        $trProperty = new core_kernel_classes_Property($uriProperty);

        //extract most important properties for navigation
        $label = $trProperty->getLabel();

        //$domaine = $trProperty->getDomain()->getIterator();
        $range = $trProperty->getRange();
        //Create an object prop with the main important variable in our context.

        $prop->label = $label;
        $prop->uriRange = $range->uriResource;
        $prop->labelRange = $range->label;
        //return the object
        return $prop;
    }

    //get properties of a particular class, return an array of uri properties as key with property info
    //Label + uriRange + labelRange)
    //
    public function trGetProperties ($uriClass) {
    //Link the class, and create a PHP object
        $trClass = $this->linkClass($uriClass);
        //get properties of the class based on the API
        $listProperties = $trClass->getProperties(true);//now.. it returnes an array of properities object
        //create only, an array with the URI of properties

        $listUri = array_keys($listProperties);

        //populate the array $listPropertiesUri with property infos
        $listPropertiesUri = array();

        foreach($listUri as $uriProp) {
        //echo $uriProp. "<br>";
        //get the info of the property
            $infoProp = $this->trGetPropertyInfo($uriProp);
            //put the property infos in the array element
            $listPropertiesUri[$uriProp]=$infoProp;
        }
        return $listPropertiesUri;
    }



    //for a specific class, we get a list uf range classes,
    //ex : for class Student we will have a list that contains , Teacher, School
    public function trGetRangeClasses($uriClass) {
    //initialize the array of classes
        $lc = array();
        $rangeClass =  new stdClass();

        //get properties of the class
        $listProp = $this->trGetProperties($uriClass);

        //get the range of all properties
        foreach ($listProp as $uriProp=>$infoProp) {

            $labelProp = $infoProp->label;
            $uriRange = $infoProp->uriRange;
            $labelRange = $infoProp->labelRange;
            //echo $labelProp." ". $labelRange." <br>";

            //Test if the range is class or not, if it is class, add it to the array
            //
            //Link to ressource

            $trResource = new core_kernel_classes_Resource($uriRange);
            if ($trResource->isClass()==true) {// verify with the api if this resource is a class
            //add this class to listClasses
            //the information are propertySourceUri, label of the class, the uri of the class is the key it self
            //IMPORTANT: we should instantiate a new object,
                $rangeClass =  new stdClass();
                //get the values
                $rangeClass->propertySourceUri = $uriProp;// to keep the property responsable of this bridge
                $rangeClass->label = $labelRange;
                $rangeClass->uriClass = $uriRange;
                //put inn the array
                $lc[$uriRange] = $rangeClass;//->Label;
        }//end of adding class's info
        }
        return $lc;
    }

    //get  classes of the given inctance;
    //The class of an instance is the value of RDF_TYPE of the instance.
    //Return structure  : $classValuesUri[$classUri]
    public function trGetClassesOfInstance($uriInstance) {
    //We begin by create a resource PHP object
        $trResource = new core_kernel_classes_Resource($uriInstance);
        //get the values of this instance for the property RDF_TYPE
        //it is an array of values array[]=uris
        $cv=$trResource->getPropertyValues(new core_kernel_classes_Property( RDF_TYPE));

        //put the uris as keys
        foreach ($cv as $uri) {
            $classValuesUri[$uri] = 0;
        }
        //return an array of classes URI;
        //an instance can belong to several classes
        return $classValuesUri;
    }
    //this method provides tow arrays,
    //1- The array od properties with, public function trGetProperties ($uriClass)
    //2- The array of classes range with public function trGetRangeClasses($uriClass)

    public function trGetPropertiesAndClassesRange($uriClass) {
        $tabProperties = $this->trGetProperties($uriClass);
        $tabRangeClasses = $this->trGetRangeClasses($uriClass);
        //put the two arrays in one.
        $tabAll["propertiesList"] = $tabProperties;
        $tabAll["rangeClassesList"] = $tabRangeClasses;

        return $tabAll;
    }

    //this method is the first invoked. according to a list of classes, it provies the first list of properties and rangeClasses
    public function trExploreModel($arrayOfClasses) {
        $tabClass = $arrayOfClasses;
        $contextClass = array();
        //

        foreach ($tabClass as $uriClass=>$info) {
            $contextClass[] = $this->trGetPropertiesAndClassesRange($uriClass);
        }
        return $contextClass;
    }

    //La fonction qui me fesait peur, le saut ou la jonction
    //based on a path: a sequence of properties and the initial instance
    function trGetBridgePropertyValues0($instanceSourceUri,$pathOfProperties) {
    //we begin by explode the path structure into an array
    //the path is created by the user with path builder in  the client side
        $pathOfPropertiesArray = explode('__',$pathOfProperties);
        $instanceUri = $instanceSourceUri;

        foreach ($pathOfPropertiesArray as $propertyUri) {

        //link the resource
            $trResource = new core_kernel_classes_Resource($instanceUri);
            //get the value of the property for this instance
            $values = $trResource->getPropertyValues(new core_kernel_classes_Property($propertyUri)) ;// get the array of values

            //the value is the new instance to treat, it is now the new bridge
            //it can also be the last value, so we te

            if (count($values)==0) {
                $instanceUri ="";
                //echo "<br> the value is gg = ".$instanceUri;
                break;
            }
            $instanceUri = $values[0];//prendre la première seulement; in newt release we wil take all the values
        //echo "<br> the value is gg = ".$instanceUri;
        }

        $finalValue = $instanceUri;
        return $finalValue;
    }

    //La fonction qui me fesait peur, le saut ou la jonction
    //based on a path: a sequence of properties and the initial instance
    function trGetBridgePropertyValues($instanceSourceUri,$pathOfProperties) {
    //we begin by explode the path structure into an array
    //the path is created by the user with path builder in  the client side
        $pathOfPropertiesArray = explode('__',$pathOfProperties);

        //$instanceUri = $instanceSourceUri;

        //the array of uri of instances
        $listInstancesUri=array(); //[]=$instanceSourceUri;

        //this array is used only to keep the list of the next uri instance of the actual path position
        $intermediateTabUri = array();
        $intermediateTabUri[]= $instanceSourceUri;

        // Into the path, step by step
        foreach ($pathOfPropertiesArray as $propertyUri) {
        //for each uri instances in
        //link the resource

            $listInstancesUri=$intermediateTabUri;
            $intermediateTabUri=array();

            foreach ($listInstancesUri as $instanceUri) {

                $trResource = new core_kernel_classes_Resource($instanceUri);
                //get the value of the property for this instance
                $values = $trResource->getPropertyValues(new core_kernel_classes_Property($propertyUri)) ;// get the array of values

                //the value is the new instance to treat, it is now the new bridge
                //it can also be the last value, so we te

                //this array containes the bridged uri of instances
                $intermediateTabUri = array_merge($intermediateTabUri,$values);

            //                if (count($values)==0) {
            //                    $instanceUri ="";
            //                    //echo "<br> the value is gg = ".$instanceUri;
            //                    break;
            //                }
            //$instanceUri = $values[0];//prendre la première seulement; in newt release we wil take all the values
            //echo "<br> the value is gg = ".$instanceUri;


            }//instance

            //break if the intermediateTab is void
            if(count($intermediateTabUri)==0) {
                $intermediateTabUri = array();
                break;
            }
        //now the intermediateTab is complete, it will be the next input of the loop

        }//path


        $finalValue = implode ('-', $intermediateTabUri);// $instanceUri;
        return $finalValue;
    }
}
 /*
  *
 *
 *
 *  //
 *
 */
//echo "uuuu";
//$p = new RegCommon();
//$p->regConnect();
////$p = new TReg_VirtualTable();
//$p->getInstances();

//
////$tabprop=$p->trGetProperties(teacherUri);
////print_r($tabprop);
//
//$t[]=propHasTeacher;
//$t[]="http://127.0.0.1/middleware/hyperclass.rdf#12157851749292";//nationality bridgehttp://127.0.0.1/middleware/hyperclass.rdf#12157851749292
////$t[]="sdfsf";
//$t[]="http://www.w3.org/2000/01/rdf-schema#label";
//
//
////get techcher uri of patrick
//$v=$p->trGetBridgePropertyValues(younes, $t);

//$tabClasses = $p->trGetRangeClasses("http://127.0.0.1/middleware/hyperclass.rdf#121310312857514");
//foreach ($tabClasses as $c=>$info) {
//    echo "<br> Uri de la classe  = ".$c;
//    echo "<br> Le label de la classe est : ".$info->Label;
//    echo "<br> La property source est = ".$info->propertySourceUri;
//    echo "<br>#################################<br>";
//}

/*$trStudent = new core_kernel_classes_Class(studentUri);
//$coco=$trStudent->getInstances();
//
//echo '<br>';
//
//print_r($coco);
$ins = $p->trGetClassOfInstance(patrickUri);
print_r($ins);*/


?>
