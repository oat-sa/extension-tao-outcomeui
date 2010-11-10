<?php

error_reporting(E_ALL);

/**
 * Service methods to manage the Results business models using the RDF API.
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
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
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
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
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package taoResults
 * @subpackage models_classes
 */
class taoResults_models_classes_ResultsService extends tao_models_classes_GenerisService {
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
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @return mixed
     */
    public function __construct() {
        // section 127-0-1-1--233123b3:125208ce1cc:-8000:0000000000001C75 begin

        parent::__construct();
        $this->resultClass = new core_kernel_classes_Class(TAO_RESULT_CLASS);

        // section 127-0-1-1--233123b3:125208ce1cc:-8000:0000000000001C75 end
    }

    /**
     * get a result instance
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string identifier
     * @param  string mode
     * @param  Class clazz
     * @return core_kernel_classes_Resource
     */
    public function getResult($identifier, $mode = 'uri', core_kernel_classes_Class $clazz = null) {
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
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string uri
     * @return core_kernel_classes_Class
     */
    public function getResultClass($uri = '') {
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
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  Class clazz
     * @param  string label
     * @param  array properties
     * @return core_kernel_classes_Class
     */
    public function createResultClass(core_kernel_classes_Class $clazz = null, $label = '', $properties = array()) {
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
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  Resource result
     * @return boolean
     */
    public function deleteResult(core_kernel_classes_Resource $result) {
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
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  Class clazz
     * @return boolean
     */
    public function deleteResultClass(core_kernel_classes_Class $clazz) {
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
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  Class clazz
     * @return boolean
     */
    public function isResultClass(core_kernel_classes_Class $clazz) {
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
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  Resource group
     * @return core_kernel_classes_ContainerCollection
     */
    protected function getResultsByGroup(core_kernel_classes_Resource $group) {
        $returnValue = null;

        // section 10-13-1-45-792423e0:12398d13f24:-8000:00000000000017F3 begin
        // section 10-13-1-45-792423e0:12398d13f24:-8000:00000000000017F3 end

        return $returnValue;
    }

    /**
     * Short description of method addResultVariable
     *
     * @access public
     * @author Younes Dajghloul, Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  array dtisUris
     * @param  string key
     * @param  string value
     * @return core_kernel_classes_Resource
     */
    public function addResultVariable($dtisUris, $key, $value) {
        $returnValue = null;

        // section 127-0-1-1-3fc126b2:12c350e4297:-8000:0000000000002886 begin

        if (is_array($dtisUris) && !empty($key)) {

            //connect to the class of dtis Result Class

            $dtisResultClass = new core_kernel_classes_Class(TAO_ITEM_RESULTS_CLASS);
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

            // Create the label of the new instance
            $dtisLabel = $processLabel . "_" . $deliveryLabel . "_" . $testLabel . "_" . $itemLabel . "_" . $subjectLabel . "_" . date("Y/m/d_H:i:s"); // todo label of dtis + date
            $dtisComment = "Result Recieved the : " . date("Y/m/d_H:i:s");
            $dtisInstance = $dtisResultClass->createInstance($dtisLabel, $dtisComment);
            //Add the uri of the variable and its value to the array
            //Put the name only without the name space part
            $dtisUris['TAO_ITEM_VARIABLE_ID'] = $key;
            $dtisUris['TAO_ITEM_VARIABLE_VALUE'] = $value;

            // Create the property Object
            foreach ($arrayOfDtisVariables as $dtisUri => $dtisValue) {
                // Create the property Object
                $dtisProp = new core_kernel_classes_Property($resultNS . "#" . $dtisUri);
                // Set values of the instance
                $dtisInstance->setPropertyValue($dtisProp, $dtisValue);
            }
        }
        //put the instance as returned value
        $returnValue = $dtisInstance;

        // section 127-0-1-1-3fc126b2:12c350e4297:-8000:0000000000002886 end

        return $returnValue;
    }

    /**
     * Short description of method setScore
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  array dtisUris
     * @param  string scoreValue
     * @param  string minScoreValue
     * @param  string maxScoreValue
     * @return core_kernel_classes_Resource
     */
    public function setScore($dtisUris, $scoreValue, $minScoreValue = '', $maxScoreValue = '') {
        $returnValue = null;

        // section 127-0-1-1-3fc126b2:12c350e4297:-8000:000000000000288B begin

        $returnValue = $this->addResultVariable($dtisUris, SCORE_ID, $scoreValue);

        if (!empty($minScoreValue)) {
            $this->addResultVariable($dtisUris, SCORE_MIN_ID, $minScoreValue);
        }
        if (!empty($maxScoreValue)) {
            $this->addResultVariable($dtisUris, SCORE_MAX, $maxScoreValue);
        }

        // section 127-0-1-1-3fc126b2:12c350e4297:-8000:000000000000288B end

        return $returnValue;
    }

    /**
     * Short description of method setEndorsment
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  array dtisUris
     * @param  boolean|float endorsement
     * @return core_kernel_classes_Resource
     */
    public function setEndorsment($dtisUris, $endorsement) {
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
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  array dtisUris
     * @param  string value
     * @return core_kernel_classes_Resource
     */
    public function setAnsweredValues($dtisUris, $value) {
        $returnValue = null;

        // section 127-0-1-1-bdec0d0:12c357cc917:-8000:0000000000002897 begin

        $returnValue = $this->addResultVariable($dtisUris, ANSWERED_VALUES_ID, (string) $value);

        // section 127-0-1-1-bdec0d0:12c357cc917:-8000:0000000000002897 end

        return $returnValue;
    }

}

/* end of class taoResults_models_classes_ResultsService */
?>