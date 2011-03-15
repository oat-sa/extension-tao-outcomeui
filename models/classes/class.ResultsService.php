<?php

error_reporting(E_ALL);

/**
 * Service methods to manage the Results business models using the RDF API.
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
// section 10-13-1-45-792423e0:12398d13f24:-8000:00000000000017F0-includes begin
// section 10-13-1-45-792423e0:12398d13f24:-8000:00000000000017F0-includes end

/* user defined constants */
// section 10-13-1-45-792423e0:12398d13f24:-8000:00000000000017F0-constants begin
// section 10-13-1-45-792423e0:12398d13f24:-8000:00000000000017F0-constants end

/**
 * Service methods to manage the Results business models using the RDF API.
 *
 * @access public
 * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
 * @package taoResults
 * @subpackage models_classes
 */
class taoResults_models_classes_ResultsService
    extends tao_models_classes_GenerisService
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * The RDFS top level result class
     *
     * @access protected
     * @var Class
     */
    protected $resultClass = null;

    // --- OPERATIONS ---

    /**
     * Short description of method __construct
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @return mixed
     */
    public function __construct()
    {
        // section 127-0-1-1--233123b3:125208ce1cc:-8000:0000000000001C75 begin

        parent::__construct();
        $this->resultClass = new core_kernel_classes_Class(TAO_RESULT_CLASS);

        // section 127-0-1-1--233123b3:125208ce1cc:-8000:0000000000001C75 end
    }

    /**
     * get a result instance
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  string identifier
     * @param  string mode
     * @param  Class clazz
     * @return core_kernel_classes_Resource
     */
    public function getResult($identifier, $mode = 'uri',  core_kernel_classes_Class $clazz = null)
    {
        $returnValue = null;

        // section 127-0-1-1--233123b3:125208ce1cc:-8000:0000000000001C77 begin

        if (is_null($clazz) && $mode == 'uri') {
            try {
                $resource = new core_kernel_classes_Resource($identifier);
                $type = $resource->getUniquePropertyValue(new core_kernel_classes_Property(RDF_TYPE));
                $clazz = new core_kernel_classes_Class($type->uriResource);
            } catch (Exception $e) {
                
            }
        }
        if (is_null($clazz)) {
            $clazz = $this->resultClass;
        }
        if ($this->isResultClass($clazz)) {
            $returnValue = $this->getOneInstanceBy($clazz, $identifier, $mode);
        }

        // section 127-0-1-1--233123b3:125208ce1cc:-8000:0000000000001C77 end

        return $returnValue;
    }

    /**
     * get a result subclass by uri. 
     * If the uri is not set, it returns the  result class (the top level class.
     * If the uri don't reference a  result subclass, it returns null
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  string uri
     * @return core_kernel_classes_Class
     */
    public function getResultClass($uri = '')
    {
        $returnValue = null;

        // section 127-0-1-1--233123b3:125208ce1cc:-8000:0000000000001C86 begin

        if (empty($uri) && !is_null($this->resultClass)) {
            $returnValue = $this->resultClass;
        } else {
            $clazz = new core_kernel_classes_Class($uri);
            if ($this->isResultClass($clazz)) {
                $returnValue = $clazz;
            }
        }

        // section 127-0-1-1--233123b3:125208ce1cc:-8000:0000000000001C86 end

        return $returnValue;
    }

    /**
     * subclass the result class
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  Class clazz
     * @param  string label
     * @param  array properties
     * @return core_kernel_classes_Class
     */
    public function createResultClass( core_kernel_classes_Class $clazz = null, $label = '', $properties = array())
    {
        $returnValue = null;

        // section 127-0-1-1--233123b3:125208ce1cc:-8000:0000000000001C8C begin

        if (is_null($clazz)) {
            $clazz = $this->resultClass;
        }

        if ($this->isResultClass($clazz)) {

            $resultClass = $this->createSubClass($clazz, $label);

            foreach ($properties as $propertyName => $propertyValue) {
                $myProperty = $resultClass->createProperty(
                                $propertyName,
                                $propertyName . ' ' . $label . ' result property created from ' . get_class($this) . ' the ' . date('Y-m-d h:i:s')
                );

                //@todo implement check if there is a widget key and/or a range key
            }
            $returnValue = $resultClass;
        }

        // section 127-0-1-1--233123b3:125208ce1cc:-8000:0000000000001C8C end

        return $returnValue;
    }

    /**
     * delete a result
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  Resource result
     * @return boolean
     */
    public function deleteResult( core_kernel_classes_Resource $result)
    {
        $returnValue = (bool) false;

        // section 10-13-1-45-792423e0:12398d13f24:-8000:00000000000017F6 begin

        if (!is_null($result)) {
            $returnValue = $result->delete();
        }

        // section 10-13-1-45-792423e0:12398d13f24:-8000:00000000000017F6 end

        return (bool) $returnValue;
    }

    /**
     * delete a result class or subclass
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  Class clazz
     * @return boolean
     */
    public function deleteResultClass( core_kernel_classes_Class $clazz)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1--233123b3:125208ce1cc:-8000:0000000000001C9E begin

        if (!is_null($clazz)) {
            if ($this->isResultClass($clazz) && $clazz->uriResource != $this->resultClass->uriResource) {
                $returnValue = $clazz->delete();
            }
        }

        // section 127-0-1-1--233123b3:125208ce1cc:-8000:0000000000001C9E end

        return (bool) $returnValue;
    }

    /**
     * check if the given class is a class or a subclass of Result
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  Class clazz
     * @return boolean
     */
    public function isResultClass( core_kernel_classes_Class $clazz)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1--233123b3:125208ce1cc:-8000:0000000000001CA2 begin

        if ($clazz->uriResource == $this->resultClass->uriResource) {
            $returnValue = true;
        } else {
            foreach ($this->resultClass->getSubClasses(true) as $subclass) {
                if ($clazz->uriResource == $subclass->uriResource) {
                    $returnValue = true;
                    break;
                }
            }
        }

        // section 127-0-1-1--233123b3:125208ce1cc:-8000:0000000000001CA2 end

        return (bool) $returnValue;
    }

    /**
     * Short description of method getResultsByGroup
     *
     * @access protected
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  Resource group
     * @return core_kernel_classes_ContainerCollection
     */
    protected function getResultsByGroup( core_kernel_classes_Resource $group)
    {
        $returnValue = null;

        // section 10-13-1-45-792423e0:12398d13f24:-8000:00000000000017F3 begin
        // section 10-13-1-45-792423e0:12398d13f24:-8000:00000000000017F3 end

        return $returnValue;
    }

    /**
     * Short description of method addResultVariable
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  array dtisUris
     * @param  string key
     * @param  string value
     * @return core_kernel_classes_Resource
     */
    public function addResultVariable($dtisUris, $key, $value)
    {
        $returnValue = null;

        // section 127-0-1-1-3fc126b2:12c350e4297:-8000:0000000000002886 begin
        //get the name space of the class

        $resultNS = 'http://www.tao.lu/Ontologies/TAOResult.rdf';

        if (is_array($dtisUris) && !empty($key)) {

            //connect to the class of dtis Result Class
//            $dtisResultClass = new core_kernel_classes_Class(TAO_ITEM_RESULTS_CLASS);
//            //****Remove from the last version
//            $resultNS = core_kernel_classes_Session::getNameSpace();
//            //echo " le namse space ".$resultNS; // to remove in the final version
            $dtisResultClass = new core_kernel_classes_Class($resultNS . '#' . "TAO_ITEM_RESULTS");
//            //****
            // Create the instance of DTIS_Result
            //one begins by create the label; label of DTIS + date
            // label of Delivery
            if (!isset($dtisUris["TAO_PROCESS_EXEC_ID"])) {
                throw new Exception('TAO_PROCESS_EXEC_ID must reference a process execution uri');
            }
            $uri = $dtisUris["TAO_PROCESS_EXEC_ID"];
            $inst = new core_kernel_classes_Resource($uri);
            $processLabel = $inst->getLabel();

            // label of Delivery
            if (!isset($dtisUris["TAO_DELIVERY_ID"])) {
                throw new Exception('TAO_DELIVERY_ID must reference a delivery uri');
            }
            $uri = $dtisUris["TAO_DELIVERY_ID"];
            $inst = new core_kernel_classes_Resource($uri);
            $deliveryLabel = $inst->getLabel();

            // label of Test
            if (!isset($dtisUris["TAO_TEST_ID"])) {
                throw new Exception('TAO_TEST_ID must reference a test uri');
            }
            $uri = $dtisUris["TAO_TEST_ID"];
            $inst = new core_kernel_classes_Resource($uri);
            $testLabel = $inst->getLabel();

            // label of Item
            if (!isset($dtisUris["TAO_ITEM_ID"])) {
                throw new Exception('TAO_ITEM_ID must reference an item uri');
            }
            $uri = $dtisUris["TAO_ITEM_ID"];
            $inst = new core_kernel_classes_Resource($uri);
            $itemLabel = $inst->getLabel();

            //label of Subject
            if (!isset($dtisUris["TAO_SUBJECT_ID"])) {
                throw new Exception('TAO_SUBJECT_ID must reference a subject uri');
            }
            $uri = $dtisUris["TAO_SUBJECT_ID"];
            $inst = new core_kernel_classes_Resource($uri);
            $subjectLabel = $inst->getLabel();
            //-----To remove in the last version
//            list($ns, $processLabel) = explode("#", $dtisUris["TAO_PROCESS_EXEC_ID"]);
//            list($ns, $deliveryLabel) = explode("#", $dtisUris["TAO_DELIVERY_ID"]);
//
//            list($ns, $testLabel) = explode("#", $dtisUris["TAO_TEST_ID"]);
//
//            list($ns, $itemLabel) = explode("#", $dtisUris["TAO_ITEM_ID"]);
//            list($ns, $subjectLabel) = explode("#", $dtisUris["TAO_SUBJECT_ID"]);

            // Create the label of the new instance
            $dtisLabel = $deliveryLabel . "_" . $processLabel . "_" . $testLabel . "_" . $itemLabel . "_" . $subjectLabel . "_" . date("Y/m/d_H:i:s"); // todo label of dtis + date
            $dtisComment = "Result Recieved the : " . date("Y/m/d_H:i:s");
            $dtisInstance = $dtisResultClass->createInstance($dtisLabel, $dtisComment);
            //Add the uri of the variable and its value to the array
            //Put the name only without the name space part

            $dtisUrisAll['TAO_ITEM_VARIABLE_ID'] = $key;
            $dtisUrisAll['TAO_ITEM_VARIABLE_VALUE'] = $value;
            $dtisUrisAll = array_merge($dtisUris, $dtisUrisAll);
            //print_r($dtisUris);
            // Create the property Object
            foreach ($dtisUrisAll as $dtisUri => $dtisValue) {
                // Create the property Object
                $dtisProp = new core_kernel_classes_Property($resultNS . "#" . $dtisUri);
                // Set values of the instance
                $dtisInstance->setPropertyValue($dtisProp, $dtisValue);
            }

            //put the instance as returned value
            $returnValue = $dtisInstance;

            //**********************************************************
            //Now we will add the method that create the instance in the delivery classe
            //the delivery classes will be created and dynamically, as well as thier properties
            //Regarding to each new result, we creat or no the class and the property,
            //its important to check the existance of the instance also to add the propertyvamue for the appropriate instance
            //get the appropriate Delivery Result Class according to the delivery of the result
            //one begins by geting all classes of TAO_DELIVERY_RESULTS
            //connect to the class TAO_DELIVERY_RESULTS
            $TAO_DELIVERY_RESULTS_CLASS = $resultNS . "#" . "TAO_DELIVERY_RESULTS"; // to modify in the last version and put a contant
            $deliveryResultClass = new core_kernel_classes_Class($TAO_DELIVERY_RESULTS_CLASS);
            $listOfDeliveryClasses = $deliveryResultClass->getSubClasses();
            //compaiason
            $uri = $dtisUris["TAO_DELIVERY_ID"];
            list($ns, $lastpartUriDeliveryOfResult) = explode("#", $uri);
            //use the last part as uri for the search
            $localNS = core_kernel_classes_Session::getNameSpace();

            $uriDelivery = $localNS . "#DR_" . $lastpartUriDeliveryOfResult; // add DR_ for the instance of the Delivery Result
            //
            //check of not existance => create it with the same last part uri as the delivery

            if (array_key_exists($uriDelivery, $listOfDeliveryClasses) === FALSE) {
                $rdfClass = new core_kernel_classes_Class(RDF_CLASS);
                $labelClass = $deliveryLabel . "_Results";
                $commentClass = " This result class contains all results for the " . $deliveryLabel . " delivery";
                $uriClass = '#' . "DR_" . $lastpartUriDeliveryOfResult;
                $resourceClass = $rdfClass->createInstance($labelClass, $commentClass, $uriClass);
                //This class should be linked to the URI already created
                $drClass = new core_kernel_classes_Class($resourceClass->uriResource);

                //set this class as sub class of TAO_DELIVERY_RESULTS ($deliveryResultClass)
                $drClass->setSubClassOf($deliveryResultClass);
                $uriDelivery = $drClass->uriResource;


            }
        	$drClass = new core_kernel_classes_Class($uriDelivery);
            $match = FALSE;
            $matchUri = '';
            $apiSearch = new core_kernel_impl_ApiSearchI();
        	$options = array('checkSubclasses'	=> false, 'like' => false);
        	$filters = array($resultNS . "#" . "TAO_PROCESS_EXEC_ID" => $dtisUris["TAO_PROCESS_EXEC_ID"]);
        	foreach($apiSearch->searchInstances($filters, $drClass, $options) as $processExec){
        		$match = TRUE;
                $matchUri = $processExec->uriResource;
                break;
        	}
            
            //if it doesn't match so create a new instance
            if (!$match) {
                //create a new instance of the current delivery class
                $label = "Res_" . $deliveryLabel . "_" . date("Y/m/d_H:i:s");
                $comment = "Result of " . $subjectLabel;
                $newInstance = $drClass->createInstance($label, $comment);
                $matchUri = $newInstance->uriResource;
            }
            
            // now the $matchUri is the instance to use
            //******************** Get the uri of the property to use to save the value
            //verify if the variable exists as property in the current class
            //get all properties of the current class
            $listOfProperties = $drClass->getProperties();
            //create the last part of the uri test+item++variable name
            $testUri = $dtisUris["TAO_TEST_ID"];
            list($ns, $lpTestUri) = explode("#", $testUri);
            $itemUri = $dtisUris["TAO_ITEM_ID"];
            list($ns, $lpItemUri) = explode("#", $itemUri);
            $nameOfVariable = $key;
            $lastPartUriProperty = $lpTestUri . '_' . $lpItemUri . "_" . $nameOfVariable;
            //check the existance of the property
            $uriPropertyToCheck = $localNS . "#" . $lastPartUriProperty;
            if (array_key_exists($uriPropertyToCheck, $listOfProperties) === FALSE) {
                //create the property
                $rdfProperty = new core_kernel_classes_Class(RDF_PROPERTY);
                $labelProperty = $nameOfVariable . " of " . $itemLabel . '_' . $testLabel;
                $commentProperty = " This property match the variable " . $nameOfVariable . " of the Item :  " . $itemLabel . ' in the test :' . $testLabel;

                $resourceProperty = $rdfProperty->createInstance($labelProperty, $commentProperty, "#" . $lastPartUriProperty);
                //Create the property and link it with the uri
                $drProperty = new core_kernel_classes_Property($resourceProperty->uriResource);

                $widgetProp = new core_kernel_classes_Property("http://www.tao.lu/datatypes/WidgetDefinitions.rdf#widget");
                $drProperty->setPropertyValue($widgetProp, "http://www.tao.lu/datatypes/WidgetDefinitions.rdf#TextBox");

                //Link the property with the class
                $drClass->setProperty($drProperty);
            }
            //the Uri of the property is
            $uriOfVariable = $localNS . "#" . $lastPartUriProperty;

            //now we have the uri of the property + the uri of the instance
            //just "Inchallah" set the property values for TAO_PROCESS_EXEC_ID TAO_DELIVERY_ID TAO_SUBJECT_ID
            $deliveryResultInstance = new core_kernel_classes_Resource($matchUri);

            $varPro = new core_kernel_classes_Property($resultNS . "#" . "TAO_PROCESS_EXEC_ID");
            $deliveryResultInstance->editPropertyValues($varPro, $dtisUris["TAO_PROCESS_EXEC_ID"]);


            $varPro = new core_kernel_classes_Property($resultNS . "#" . "TAO_DELIVERY_ID");
            $deliveryResultInstance->editPropertyValues($varPro, $dtisUris['TAO_DELIVERY_ID']);

            $varPro = new core_kernel_classes_Property($resultNS . "#" . "TAO_SUBJECT_ID");
            $deliveryResultInstance->editPropertyValues($varPro, $dtisUris["TAO_SUBJECT_ID"]);

            //set the value of the variable property
            $varPro = new core_kernel_classes_Property($uriOfVariable);
            $deliveryResultInstance->editPropertyValues($varPro, $value);
// the end
        }
        // section 127-0-1-1-3fc126b2:12c350e4297:-8000:0000000000002886 end

        return $returnValue;
    }

    /**
     * Short description of method setScore
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  array dtisUris
     * @param  string scoreValue
     * @param  string minScoreValue
     * @param  string maxScoreValue
     * @return core_kernel_classes_Resource
     */
    public function setScore($dtisUris, $scoreValue, $minScoreValue = '', $maxScoreValue = '')
    {
        $returnValue = null;

        // section 127-0-1-1-3fc126b2:12c350e4297:-8000:000000000000288B begin
	
        $returnValue = $this->addResultVariable($dtisUris, SCORE_ID, $scoreValue);

        if (!empty($minScoreValue)) {
            $this->addResultVariable($dtisUris, SCORE_MIN_ID, $minScoreValue);
        }
        if (!empty($maxScoreValue)) {
            $this->addResultVariable($dtisUris, SCORE_MAX_ID, $maxScoreValue);
        }

        // section 127-0-1-1-3fc126b2:12c350e4297:-8000:000000000000288B end

        return $returnValue;
    }

    /**
     * Short description of method setEndorsment
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  array dtisUris
     * @param  boolean endorsement
     * @return core_kernel_classes_Resource
     */
    public function setEndorsment($dtisUris, $endorsement)
    {
        $returnValue = null;

        // section 127-0-1-1-bdec0d0:12c357cc917:-8000:0000000000002893 begin

        if (is_float($endorsement) && $endorsement > 0.0 && $endorsement < 1.0) {
            $returnValue = $this->addResultVariable($dtisUris, ENDORSMENT_ID, $endorsement);
        } else {
            $returnValue = $this->addResultVariable($dtisUris, ENDORSMENT_ID, ($endorsement) ? '1' : '0');
        }
        // section 127-0-1-1-bdec0d0:12c357cc917:-8000:0000000000002893 end

        return $returnValue;
    }

    /**
     * Short description of method setAnsweredValues
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  array dtisUris
     * @param  string value
     * @return core_kernel_classes_Resource
     */
    public function setAnsweredValues($dtisUris, $value)
    {
        $returnValue = null;

        // section 127-0-1-1-bdec0d0:12c357cc917:-8000:0000000000002897 begin

        $returnValue = $this->addResultVariable($dtisUris, ANSWERED_VALUES_ID, (string) $value);

        // section 127-0-1-1-bdec0d0:12c357cc917:-8000:0000000000002897 end

        return $returnValue;
    }

    /**
     * Short description of method addResultVariables
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  array dtisUris
     * @param  array variables
     * @param  boolean onlyKnown
     * @return core_kernel_classes_Session_int
     */
    public function addResultVariables($dtisUris, $variables, $onlyKnown = false)
    {
        $returnValue = (int) 0;

        // section 127-0-1-1--360cae3b:12c3a1d0b40:-8000:00000000000028A8 begin
        
        $scores = array();
        
    	foreach($variables as $key => $value){
			switch($key){
				case SCORE_ID: 		$scores[SCORE_ID] = $value;			break;
				case SCORE_MIN_ID:	$scores[SCORE_MIN_ID] = $value;		break;
				case SCORE_MAX_ID:	$scores[SCORE_MAX_ID] = $value;		break;
				case ENDORSMENT_ID:
					$this->setEndorsment($dtisUris, $value);
					$returnValue++;
					break;
				case ANSWERED_VALUES_ID:
					$this->setAnsweredValues($dtisUris, $value);
					$returnValue++;
					break;
				default:
					if(!$onlyKnown){
						$this->addResultVariable($dtisUris, $key, $value);
						$returnValue++;
					}
					break;
			}
		}
		if(isset($scores[SCORE_ID])) {
			(isset($scores[SCORE_MIN_ID])) ? $min = $scores[SCORE_MIN_ID] : $min = '';
			(isset($scores[SCORE_MAX_ID])) ? $max = $scores[SCORE_MAX_ID] : $max = '';
			$this->setScore($dtisUris, $scores[SCORE_ID], $min, $max);
			$returnValue++;
		}
        
        // section 127-0-1-1--360cae3b:12c3a1d0b40:-8000:00000000000028A8 end

        return (int) $returnValue;
    }

} /* end of class taoResults_models_classes_ResultsService */

?>