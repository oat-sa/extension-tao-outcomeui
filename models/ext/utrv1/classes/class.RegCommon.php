<?php
/**
 * RegCommon provides the common methods to acces generis API in more suitable
 * in Table Builder context.
 *
 * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
 * @package Result
 */

require_once($_SERVER['DOCUMENT_ROOT']."/generis/common/inc.extension.php");
require_once($_SERVER['DOCUMENT_ROOT']."/taoResults/includes/common.php");

/**
 * RegCommon provides the common methods to acces generis API in more suitable
 * in Table Builder context.
 *
 * @access public
 * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
 * @package Result
 */
class RegCommon {
    /**
     * This method permits to connect to a specific module of generis.
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     */
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
    /**
     * to obtain a PHP object linked to the generis class, based on its URI
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  String $uriClass
     * @return java_lang_Object
     */
    public function linkClass($uriClass) {
        $trClass = new core_kernel_classes_Class($uriClass);
        return $trClass;
    }
    /**
     * to obtain a PHP object linked to the generis property, based on its URI
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  void $uriProperty
     * @return void
     */
    public function linkProperty($uriProperty) {
        $trProperty = new core_kernel_classes_Property($uriProperty);
        return $trProperty;
    }

    /**
     * get the label,the uri of the range and the label of the range of a
     * property
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  String $uriProperty
     * @return List
     */
    function trGetPropertyInfo($uriProperty) {
        $trProperty = new core_kernel_classes_Property($uriProperty);

        //extract most important properties for navigation
        $label = $trProperty->getLabel();

        //$domaine = $trProperty->getDomain()->getIterator();
        //$dbWrapper = core_kernel_classes_DbWrapper::singleton();
        //$dbWrapper->dbConnector->debug = true;
        //echo " \n HHHHHHHHHHHHHHHHHHHHHHHHHHH  ". $trProperty->getRange()."  HHHHHHHHHHHHHHHHHHHHHHHHHHH \n ";
        //        if ($uriProperty == 'http://127.0.0.1/middleware/demo.rdf#SUBJECT_ID'){
        //            echo " HHHHHHHHHHHHHHHHHHHHHHHHHHH  ". $trProperty->getRange();
        //
        //        }
        $range = $trProperty->getRange();
        //S$dbWrapper->dbConnector->debug = false;
        //Create an object prop with the main important variable in our context.

        $prop->label = $label;
        $prop->uriRange = $range->uriResource;
        $prop->labelRange = $range->label;
        //return the object
        return $prop;
    }

    /**
     * get properties of a particular class, return an array of uri properties
     * key with property info (Label + uriRange + labelRange)
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  String $uriClass
     * @return Collection
     */
    public function trGetProperties ($uriClass) {
        //Link the class, and create a PHP object
        $trClass = $this->linkClass($uriClass);
        //get properties of the class based on the API
        $listProperties = $trClass->getProperties(true);//now.. it returnes an array of properities object
        //create only, an array with the URI of properties
        //filter the properties
        $listProperties = $this->trFilterProperties($listProperties);

        $listUri = array_keys($listProperties);

        //populate the array $listPropertiesUri with property infos
        $listPropertiesUri = array();

        foreach($listUri as $uriProp) {
            //echo $uriProp. "<br>";
            //get the info of the property



            $infoProp = $this->trGetPropertyInfo($uriProp);
            //            if($uriProp =='http://127.0.0.1/middleware/demo.rdf#SUBJECT_ID'){
            //                echo "hhhhhhhhhhhhhhhhhhhh <br>";
            //                print_r($infoProp);
            //
            //            }
            //put the property infos in the array element
            $listPropertiesUri[$uriProp]=$infoProp;
        }

        return $listPropertiesUri;
    }

    /**
     * For a specific class, we get a list uf range classes,
     * ex : for class Student we will have a list that contains , Teacher,
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  String $uriClass
     * @return Collection
     */
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
            // if ($trResource->isClass()==true) {// verify with the api if this resource is a class
            //add this class to listClasses
            //the information are propertySourceUri, label of the class, the uri of the class is the key it self
            //IMPORTANT: we should instantiate a new object,
            $rangeClass =  new stdClass();
            //get the values
            $rangeClass->propertySourceUri = $uriProp;// to keep the property responsable of this bridge
            $rangeClass->label = $labelRange;
            $rangeClass->uriClass = $uriRange;
            //put in the array
            //
            //Do a filter on class range in this version we delete all RDF classes and il uri is null ( This ocure some times !!!!)

            if ((substr($uriRange,0,17) != 'http://www.w3.org') and ($uriRange!='')) {

                $lc[$uriRange] = $rangeClass;//->Label;
            }

            // }//end of adding class's info
        }
        return $lc;
    }
    /**
     * get  classes of the given inctance;
     * The class of an instance is the value of RDF_TYPE of the instance.
     * Return structure  : $classValuesUri[$classUri]
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  String $uriInstance
     * @return Collection
     */

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
    /**
     * This method provides only a set of properties that are not in filter,
     * It is important in the case of deleting all rdf properties
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  List $listProperties
     * @return List
     */
    function trFilterProperties($listProperties) {
        //http://www.w3.org/2000/01/rdf-schema#isDefinedBy
        //If the name space of the property is http://www.w3.org/2000/01/rdf-schema#isDefinedBy
        //Then delete from list
        $finalProp = $listProperties;
        $blockedProperties = array();

        $blockedProperties[]= 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type';
        $blockedProperties[]= 'http://www.w3.org/1999/02/22-rdf-syntax-ns#subject';
        $blockedProperties[]= 'http://www.w3.org/1999/02/22-rdf-syntax-ns#predicate';
        $blockedProperties[]= 'http://www.w3.org/1999/02/22-rdf-syntax-ns#object';
        $blockedProperties[]= 'http://www.w3.org/1999/02/22-rdf-syntax-ns#value';
        $blockedProperties[]= 'http://www.w3.org/2000/01/rdf-schema#subPropertyOf';
        $blockedProperties[]= 'http://www.w3.org/2000/01/rdf-schema#comment';
        $blockedProperties[]= 'http://www.w3.org/2000/01/rdf-schema#seeAlso';
        $blockedProperties[]='http://www.w3.org/2000/01/rdf-schema#isDefinedBy';
        $blockedProperties[]='http://www.w3.org/2000/01/rdf-schema#member';

        foreach ($listProperties as $uri=>$obj ) {

            if (in_array($uri,$blockedProperties)) {
                //echo "jjjjj";
                unset($finalProp[$uri]);
            }

        }
        return $finalProp;


    }

    /**
     * This method provides tow arrays,
     * 1- The array od properties with, public function trGetProperties
     * 2- The array of classes range with public function
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  String $uriClass
     * @return Collection
     */

    public function trGetPropertiesAndClassesRange($uriClass) {

        $tp = $this->trGetProperties($uriClass);
        $tabRangeClasses = $this->trGetRangeClasses($uriClass);
        //filter
        $tabProperties = $this->trFilterProperties($tp);

        //put the two arrays in one.
        $tabAll["propertiesList"] = $tabProperties;
        $tabAll["rangeClassesList"] = $tabRangeClasses;



        return $tabAll;
    }

    /**
     * This method is the first invoked. according to a list of classes, it
     * the first list of properties and rangeClasses
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  Collection $arrayOfClasses
     * @return Collection
     */

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
    /**
     * Provides a value of a specific property according to the path
     * ( path: a sequence of properties)  and the initial instance
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  String $instanceSourceUri
     * @param  String $pathOfProperties
     * @return java_lang_String
     */
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

        //keep the lreal path
        $realPath = array();

        // Into the path, step by step
        $countPath = count($pathOfProperties);
        $pathPosition = 0;

        foreach ($pathOfPropertiesArray as $propertyUri) {
            $pathPosition++ ;
            //for each uri instances in
            //link the resource

            $listInstancesUri=$intermediateTabUri;
            $intermediateTabUri=array();

            foreach ($listInstancesUri as $instanceUri) {

                $trResource = new core_kernel_classes_Resource($instanceUri);
                //get the value of the property for this instance
                $values = $trResource->getPropertyValues(new core_kernel_classes_Property($propertyUri)) ;// get the array of values
                // ************* Added to keep the real path of the instance

                /*foreach ($values as $val) {
                    //test if we arze in the rage property and not  the last property to
                    //do not have a conflict of values ( ex the property is label so we can have the same value
                    if ($pathPosition <= count($pathOfPropertiesArray)) {
                        
                        if (isset($realPath[urlencode($instanceUri)]) ) {
                            //$path = $realPath[$listInstancesUri].'.'.$listInstancesUri;
                            echo "exist";

                            $realPath[urlencode($val)] = $realPath[$instanceUri].'__'.$instanceUri;
                        }else {
                             echo "NNNN pas";
                            $realPath[urlencode($val)] = urlencode($instanceUri);
                        }
                        //the real path
                    }

                }
                 /*///Finish ************* Added to keep the real path of the instance

                //the value is the new instance to treat, it is now the new bridge
                //it can also be the last value, so we test the count

                //this array containes the bridged uri of instances
                $intermediateTabUri = array_merge($intermediateTabUri,$values);

            }//instance

            //break if the intermediateTab is void
            if(count($intermediateTabUri)==0) {
                $intermediateTabUri = array();
                break;
            }
            //now the intermediateTab is complete, it will be the next input of the loop

        }//path
        /*        echo "le real path";*/


        $finalValue = implode ('|$*', $intermediateTabUri);// $instanceUri;
        return $finalValue;
    }


    function YYYYtrGetBridgePropertyValues($instanceSourceUri,$pathOfProperties) {
        //we begin by explode the path structure into an array
        //the path is created by the user with path builder in  the client side
        $pathOfPropertiesArray = explode('__',$pathOfProperties);

        //$instanceUri = $instanceSourceUri;

        //the array of uri of instances
        $listInstancesUri=array(); //[]=$instanceSourceUri;

        //this array is used only to keep the list of the next uri instance of the actual path position
        $intermediateTabUri = array();
        $intermediateTabUri[]= $instanceSourceUri;

        //keep the lreal path
        $realPath = array();

        // Into the path, step by step
        $countPath = count($pathOfProperties);
        $pathPosition = 0;

        foreach ($pathOfPropertiesArray as $propertyUri) {
            $pathPosition++ ;
            //for each uri instances in
            //link the resource

            $listInstancesUri=$intermediateTabUri;
            $intermediateTabUri=array();

            foreach ($listInstancesUri as $instanceUri) {

                $trResource = new core_kernel_classes_Resource($instanceUri);
                //get the value of the property for this instance
                $values = $trResource->getPropertyValues(new core_kernel_classes_Property($propertyUri)) ;// get the array of values
                // ************* Added to keep the real path of the instance

                foreach ($values as $val) {
                    //test if we arze in the rage property and not  the last property to
                    //do not have a conflict of values ( ex the property is label so we can have the same value
                    if ($pathPosition <= count($pathOfPropertiesArray)) {

                        if (isset($realPath[urlencode($instanceUri)]) ) {
                            //$path = $realPath[$listInstancesUri].'.'.$listInstancesUri;
                            echo "exist";

                            $realPath[urlencode($val)] = $realPath[$instanceUri].'__'.$instanceUri;
                        }else {
                             echo "NNNN pas";
                            $realPath[urlencode($val)] = urlencode($instanceUri);
                        }
                        //the real path
                    }

                }
                // Finish ************* Added to keep the real path of the instance

                //the value is the new instance to treat, it is now the new bridge
                //it can also be the last value, so we test the count

                //this array containes the bridged uri of instances
                $intermediateTabUri = array_merge($intermediateTabUri,$values);

            }//instance

            //break if the intermediateTab is void
            if(count($intermediateTabUri)==0) {
                $intermediateTabUri = array();
                break;
            }
            //now the intermediateTab is complete, it will be the next input of the loop

        }//path
        /*        echo "le real path";*/
print_r($realPath);

        $finalValue = implode ('|$*', $intermediateTabUri);// $instanceUri;
        return $finalValue;
    }
    /**
     * Provides the code of the current module in used,
     * This is very helpful to have a contextual behavior of UTR acording to the module
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     *
     * @return String , the code tof the module, one of these values
     * -taoItems
     * -taoTests
     * -taoSubjects
     * -taoGroups
     * -taoResults
     * -taoDelivery
     */
    public function getCurrentModule() {

        $service =  tao_models_classes_ServiceFactory::get('tao_models_classes_TaoService');
        $extension = $service->getCurrentExtension();

        return $extension;


    }


}//End class



?>
