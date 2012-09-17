<?php

error_reporting(E_ALL);

/**
 * TAO - taoResults\models\classes\class.RegCommon.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 01.06.2011, 14:22:01 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
 * @package taoResults
 * @subpackage models_classes
 */
if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * The Service class is an abstraction of each service instance. 
 * Used to centralize the behavior related to every servcie instances.
 *
 * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
 */
require_once('tao/models/classes/class.GenerisService.php');

/* user defined includes */
// section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002A1D-includes begin
// section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002A1D-includes end

/* user defined constants */
// section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002A1D-constants begin
// section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002A1D-constants end

/**
 * Short description of class taoResults_models_classes_RegCommon
 *
 * @access public
 * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
 * @package taoResults
 * @subpackage models_classes
 */
class taoResults_models_classes_RegCommon extends tao_models_classes_GenerisService {
    // --- ASSOCIATIONS ---
    // --- ATTRIBUTES ---
    // --- OPERATIONS ---

    /**
     * RegCommon provides the common methods to acces generis API in more
     * in Table Builder context.
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @return mixed
     */
    public function regConnect() {
        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002A1E begin
        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002A1E end
    }

    /**
     * Short description of method linkProperty
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  string uriProperty
     * @return core_kernel_classes_Property
     */
    public function linkProperty($uriProperty) {
        $returnValue = null;

        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002A20 begin
        $trProperty = new core_kernel_classes_Property($uriProperty);
        $returnValue = $trProperty;

        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002A20 end

        return $returnValue;
    }

    /**
     * Short description of method trGetPropertyInfo
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  string uriProperty
     * @return array
     */
    public function trGetPropertyInfo($uriProperty) {
        $returnValue = array();

        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002A39 begin
        $trProperty = new core_kernel_classes_Property($uriProperty);

        //extract most important properties for navigation
        $label = $trProperty->getLabel();
        $range = $trProperty->getRange();
        //Create an object prop with the main important variable in our context.
        $prop->label = $label;
        $prop->uriRange = $range->uriResource;
        $prop->labelRange = $range->getLabel();
        //return the object
        $returnValue = $prop;
        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002A39 end

        return (array) $returnValue;
    }

    /**
     * Short description of method trGetProperties
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  string uriClass
     * @return array
     */
    public function trGetProperties($uriClass) {
        $returnValue = array();

        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002A50 begin
        //Link the class, and create a PHP object
        $trClass = $this->linkClass($uriClass);
        //get properties of the class based on the API
        $listProperties = $trClass->getProperties(true); //an array of properities
        //create an array with the URI of properties
        //filter the properties
        $listProperties = $this->trFilterProperties($listProperties);

        $listUri = array_keys($listProperties);

        //populate the array $listPropertiesUri with property infos
        $listPropertiesUri = array();

        foreach ($listUri as $uriProp) {
            //echo $uriProp. "<br>";
            //get the info of the property



            $infoProp = $this->trGetPropertyInfo($uriProp);
            //put the property infos in the array element
            $listPropertiesUri[$uriProp] = $infoProp;
        }

        $returnValue = $listPropertiesUri;

        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002A50 end

        return (array) $returnValue;
    }

    /**
     * Short description of method trGetRangeClasses
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  string uriClass
     * @return array
     */
    public function trGetRangeClasses($uriClass) {
        $returnValue = array();

        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002A53 begin
        //initialize the array of classes
        $lc = array();
        $rangeClass = new stdClass();

        //get properties of the class
        $listProp = $this->trGetProperties($uriClass);

        //get the range of all properties
        foreach ($listProp as $uriProp => $infoProp) {

            $labelProp = $infoProp->label;
            $uriRange = $infoProp->uriRange;
            $labelRange = $infoProp->labelRange;

            //Test if the range is class or not, if it is class, add it to the array
            //Link to ressource

            $trResource = new core_kernel_classes_Resource($uriRange);
            // if ($trResource->isClass()==true) {// verify with the api if this resource is a class
            //add this class to listClasses
            //the information are propertySourceUri, label of the class, the uri of the class is the key it self
            //IMPORTANT: we should instantiate a new object,
            $rangeClass = new stdClass();
            //get the values
            $rangeClass->propertySourceUri = $uriProp; // to keep the property responsable of this bridge
            $rangeClass->label = $labelRange;
            $rangeClass->uriClass = $uriRange;
            //put in the array
            //Do a filter on class range in this version we delete all RDF classes and il uri is null ( This ocure some times !!!!)

            if ((substr($uriRange, 0, 17) != 'http://www.w3.org') and ($uriRange != '')) {

                $lc[$uriRange] = $rangeClass; //->Label;
            }

            // }//end of adding class's info
        }
        $returnValue = $lc;

        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002A53 end

        return (array) $returnValue;
    }

    /**
     * Short description of method trGetClassesOfInstance
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  string uriInstance
     * @return array
     */
    public function trGetClassesOfInstance($uriInstance) {
        $returnValue = array();

        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002A56 begin
        //We begin by create a resource PHP object
        $trResource = new core_kernel_classes_Resource($uriInstance);

        $cv = $trResource->getType();

        //put the uris as keys
        foreach ($cv as $type) {
            $classValuesUri[$type->uriResource] = 0;
        }
        //return an array of classes URI;
        //an instance can belong to several classes
        $returnValue = $classValuesUri;
        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002A56 end

        return (array) $returnValue;
    }

    /**
     * Short description of method trFilterProperties
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  array listProperties
     * @return array
     */
    public function trFilterProperties($listProperties) {
        $returnValue = array();

        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002A59 begin
        
        //If the name space of the property is http://www.w3.org/2000/01/rdf-schema#isDefinedBy
        //Then delete from list
        $finalProp = $listProperties;
        $blockedProperties = array();

        $blockedProperties[] = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type';
        $blockedProperties[] = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#subject';
        $blockedProperties[] = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#predicate';
        $blockedProperties[] = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#object';
        $blockedProperties[] = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#value';
        $blockedProperties[] = 'http://www.w3.org/2000/01/rdf-schema#subPropertyOf';
        $blockedProperties[] = 'http://www.w3.org/2000/01/rdf-schema#comment';
        $blockedProperties[] = 'http://www.w3.org/2000/01/rdf-schema#seeAlso';
        $blockedProperties[] = 'http://www.w3.org/2000/01/rdf-schema#isDefinedBy';
        $blockedProperties[] = 'http://www.w3.org/2000/01/rdf-schema#member';

        foreach ($listProperties as $uri => $obj) {

            if (in_array($uri, $blockedProperties)) {

                unset($finalProp[$uri]);
            }
        }
        $returnValue = $finalProp;
        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002A59 end

        return (array) $returnValue;
    }

    /**
     * Short description of method trGetPropertiesAndClassesRange
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  string uriClass
     * @return array
     */
    public function trGetPropertiesAndClassesRange($uriClass) {
        $returnValue = array();

        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002A5C begin
        $tp = $this->trGetProperties($uriClass);
        $tabRangeClasses = $this->trGetRangeClasses($uriClass);
        //filter
        $tabProperties = $this->trFilterProperties($tp);

        //put the two arrays in one.
        $tabAll["propertiesList"] = $tabProperties;
        $tabAll["rangeClassesList"] = $tabRangeClasses;



        $returnValue = $tabAll;

        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002A5C end

        return (array) $returnValue;
    }

    /**
     * Short description of method trExploreModel
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  array arrayOfClasses
     * @return array
     */
    public function trExploreModel($arrayOfClasses) {
        $returnValue = array();

        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002A5F begin
        $tabClass = $arrayOfClasses;
        $contextClass = array();
        //

        foreach ($tabClass as $uriClass => $info) {
            $contextClass[] = $this->trGetPropertiesAndClassesRange($uriClass);
        }
        $returnValue = $contextClass;
        
        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002A5F end

        return (array) $returnValue;
    }

    /**
     * Short description of method trGetBridgePropertyValues
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  string instanceSourceUri
     * @param  string pathOfProperties
     * @return string
     */
    public function trGetBridgePropertyValues($instanceSourceUri, $pathOfProperties) {
        $returnValue = (string) '';

        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002A62 begin
        //we begin by explode the path structure into an array
        //the path is created by the user with path builder in  the client side

        $pathOfPropertiesArray = explode('__', $pathOfProperties);
        //the array of uri of instances
        $listInstancesUri = array();
        //this array is used only to keep the list of the next uri instance of the actual path position
        $intermediateTabUri = array();
        //keep the real path
        $realPath = array();


        $valuePath['instance'] = $instanceSourceUri;
        $trResource = new core_kernel_classes_Resource($valuePath['instance']);

        //$valuePath['realPath'][] ='';
        $intermediateTabUri[] = $valuePath;
        foreach ($pathOfPropertiesArray as $propertyUri) {
            //link the resource
            $listInstancesUri = $intermediateTabUri;

            // after each progression in the path , we remove the array
            $intermediateTabUri = array();
            $valuesPath = array();
            //print_r($listInstancesUri);
            foreach ($listInstancesUri as $instanceUri) {
                $valuesPath = array();
                $trResource = new core_kernel_classes_Resource($instanceUri['instance']);
                // ça marche echo " -----the label is : ". $trResource->getLabel();
                //get the value of the property for this instance
                $values = $trResource->getPropertyValues(new core_kernel_classes_Property($propertyUri)); // get the array of values
                //print_r($values);
                //add the path of the actual instance on all step+1 instances

                foreach ($values as $val) {
                    //echo "<br>". $val;
                    $actualPV = array();
                    $actualPV['instance'] = $val;
                    //prepare the label
                    $labelVal = ' ';//TODO: change  space by null
                    if (!empty($val) &&(is_string($val))) {
                        $trResource = new core_kernel_classes_Resource($val);

                        //todo: i have to get the label of the current resource and add it the the old path already created
                        $labelVal = $trResource->getLabel(); // this is the error, if t$val is not an uri, it return tyt !!!!!
                        if ($labelVal == NULL) {
                            //send the  val as label
                            $labelVal = (string)$val;
                        }
                    }


                    if (isset($instanceUri['realPath'])) {
                        $actualPV['realPath'] = $instanceUri['realPath'];
                    }
                    $actualPV['realPath'][] = $labelVal;
                    $valuesPath[] = $actualPV;
                }

                //this array containes the bridged uri of instances
                $intermediateTabUri = array_merge($intermediateTabUri, $valuesPath);
            }

            //break if the intermediateTab is void
            if (count($intermediateTabUri) == 0) {

                $intermediateTabUri = array();
                break;
            }
            //now the intermediateTab is complete, it will be the next input of the loop
        }//path
        //create the last value
        $finalValueTab = array();
        foreach ($intermediateTabUri as $vp) {

            //prepare values with label
            $finalValueTab[] = implode("->", $vp['realPath']);
            //in order to get only the value without all the path
        }

        $finalValue = implode('|$*', $finalValueTab); // $instanceUri;
        $returnValue = $finalValue;
         
         
        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002A62 end

        return (string) $returnValue;
    }

    /**
     * Short description of method getCurrentModule
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @return string
     */
    public function getCurrentModule() {
        $returnValue = (string) '';

        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002A66 begin
		return context::getInstance()->getExtensionName();
        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002A66 end

        return (string) $returnValue;
    }

    /**
     * Short description of method linkClass
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  string uriClass
     * @return core_kernel_classes_Class
     */
    public function linkClass($uriClass) {
        $returnValue = null;

        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002A68 begin
        $trClass = new core_kernel_classes_Class($uriClass);
        $returnValue = $trClass;

        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002A68 end

        return $returnValue;
    }

}

/* end of class taoResults_models_classes_RegCommon */
?>