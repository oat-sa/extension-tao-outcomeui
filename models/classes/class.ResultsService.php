<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API -
 *
 * $Id$
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 23.11.2009, 14:25:39 with ArgoUML PHP module 
 * (last revised $Date: 2009-04-11 21:57:46 +0200 (Sat, 11 Apr 2009) $)
 *
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
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
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 */
require_once('tao/models/classes/class.Service.php');

/* user defined includes */
// section 10-13-1-45-792423e0:12398d13f24:-8000:00000000000017F0-includes begin
// section 10-13-1-45-792423e0:12398d13f24:-8000:00000000000017F0-includes end

/* user defined constants */
// section 10-13-1-45-792423e0:12398d13f24:-8000:00000000000017F0-constants begin
// section 10-13-1-45-792423e0:12398d13f24:-8000:00000000000017F0-constants end

/**
 * Short description of class taoResults_models_classes_ResultsService
 *
 * @access public
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 * @package taoResults
 * @subpackage models_classes
 */
class taoResults_models_classes_ResultsService
    extends tao_models_classes_Service
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute resultClass
     *
     * @access protected
     * @var Class
     */
    protected $resultClass = null;

    /**
     * Short description of attribute resultsOntologies
     *
     * @access protected
     * @var array
     */
    protected $resultsOntologies = array('http://www.tao.lu/Ontologies/TAOResult.rdf');

    // --- OPERATIONS ---

    /**
     * Short description of method __construct
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @return mixed
     */
    public function __construct()
    {
        // section 127-0-1-1--233123b3:125208ce1cc:-8000:0000000000001C75 begin
		
		parent::__construct();
		$this->resultClass 	= new core_kernel_classes_Class( TAO_RESULT_CLASS );
		$this->loadOntologies($this->resultsOntologies);
		
        // section 127-0-1-1--233123b3:125208ce1cc:-8000:0000000000001C75 end
    }

    /**
     * Short description of method getResult
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  string identifier
     * @param  string mode
     * @param  Class clazz
     * @return core_kernel_classes_Resource
     */
    public function getResult($identifier, $mode = 'uri',  core_kernel_classes_Class $clazz = null)
    {
        $returnValue = null;

        // section 127-0-1-1--233123b3:125208ce1cc:-8000:0000000000001C77 begin
		
		if(is_null($clazz)){
			$clazz = $this->resultClass;
		}
		if($this->isResultClass($clazz)){
			$returnValue = $this->getOneInstanceBy( $clazz, $identifier, $mode);
		}
		
        // section 127-0-1-1--233123b3:125208ce1cc:-8000:0000000000001C77 end

        return $returnValue;
    }

    /**
     * Short description of method getResults
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  array options
     * @return core_kernel_classes_ContainerCollection
     */
    public function getResults($options)
    {
        $returnValue = null;

        // section 10-13-1-45-792423e0:12398d13f24:-8000:00000000000017CB begin
		
		if(is_null($clazz)){
			$clazz = $this->resultClass;
		}
		
		//verify the class type
		if( $clazz->uriResource != $this->resultClass->uriResource ){
			if( ! $clazz->isSubClassOf($this->resultClass) ){
				throw new Exception("your clazz argument must referr to a Result or Result's subclass in your ontology ");
			}
		}
		
		$instances = $clazz->getInstances();
		if($instances->count() > 0){
			
			//paginate options
			//@todo implements
			if(count($options) > 0){
			
				$sequence = $instances->sequence;
				
				if(isset($options['order'])){
					//order sequence by $options['order']
				}
				if(isset($options['start'])){
					//return sequence from $options['start'] index
				}
				if(isset($options['offset'])){
					//return  $options['offset'] elements of the sequence
				}
			
				$returnValue = new core_kernel_classes_ContainerCollection(new core_kernel_classes_Container(__METHOD__),__METHOD__);
				$returnValue->sequence = $sequence;
			}
			else{
				$returnValue = $instances;
			}
		}
		
        // section 10-13-1-45-792423e0:12398d13f24:-8000:00000000000017CB end

        return $returnValue;
    }

    /**
     * Short description of method getResultClass
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  string uri
     * @return core_kernel_classes_Class
     */
    public function getResultClass($uri = '')
    {
        $returnValue = null;

        // section 127-0-1-1--233123b3:125208ce1cc:-8000:0000000000001C86 begin
		
		if(empty($uri) && !is_null($this->resultClass)){
			$returnValue = $this->resultClass;
		}
		else{
			$clazz = new core_kernel_classes_Class($uri);
			if($this->isResultClass($clazz)){
				$returnValue = $clazz;
			}
		}
		
        // section 127-0-1-1--233123b3:125208ce1cc:-8000:0000000000001C86 end

        return $returnValue;
    }

    /**
     * Short description of method createResultClass
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  Class clazz
     * @param  string label
     * @param  array properties
     * @return core_kernel_classes_Class
     */
    public function createResultClass( core_kernel_classes_Class $clazz = null, $label = '', $properties = array())
    {
        $returnValue = null;

        // section 127-0-1-1--233123b3:125208ce1cc:-8000:0000000000001C8C begin
		
		if(is_null($clazz)){
			$clazz = $this->resultClass;
		}
		
		if($this->isResultClass($clazz)){
		
			$resultClass = $this->createSubClass($clazz, $label);
			
			foreach($properties as $propertyName => $propertyValue){
				$myProperty = $resultClass->createProperty(
					$propertyName,
					$propertyName . ' ' . $label .' result property created from ' . get_class($this) . ' the '. date('Y-m-d h:i:s') 
				);
				
				//@todo implement check if there is a widget key and/or a range key
			}
			$returnValue = $resultClass;
		}
		
        // section 127-0-1-1--233123b3:125208ce1cc:-8000:0000000000001C8C end

        return $returnValue;
    }

    /**
     * Short description of method deleteResult
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  Resource result
     * @return boolean
     */
    public function deleteResult( core_kernel_classes_Resource $result)
    {
        $returnValue = (bool) false;

        // section 10-13-1-45-792423e0:12398d13f24:-8000:00000000000017F6 begin
		
		if(!is_null($result)){
			$returnValue = $result->delete();
		}
		
        // section 10-13-1-45-792423e0:12398d13f24:-8000:00000000000017F6 end

        return (bool) $returnValue;
    }

    /**
     * Short description of method deleteResultClass
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  Class clazz
     * @return boolean
     */
    public function deleteResultClass( core_kernel_classes_Class $clazz)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1--233123b3:125208ce1cc:-8000:0000000000001C9E begin
		
		if(!is_null($clazz)){
			if($this->isResultClass($clazz) && $clazz->uriResource != $this->resultClass->uriResource){
				$returnValue = $clazz->delete();
			}
		}
		
        // section 127-0-1-1--233123b3:125208ce1cc:-8000:0000000000001C9E end

        return (bool) $returnValue;
    }

    /**
     * Short description of method isResultClass
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  Class clazz
     * @return boolean
     */
    public function isResultClass( core_kernel_classes_Class $clazz)
    {
        $returnValue = (bool) false;

        // section 127-0-1-1--233123b3:125208ce1cc:-8000:0000000000001CA2 begin
		
		if($clazz->uriResource == $this->resultClass->uriResource){
			$returnValue = true;	
		}
		else{
			foreach($this->getSubjectClasses() as $subclass){
				if($clazz->uriResource == $subclass->uriResource){
					$returnValue = true;
					break;	
				}
			}
		}
		
        // section 127-0-1-1--233123b3:125208ce1cc:-8000:0000000000001CA2 end

        return (bool) $returnValue;
    }

    /**
     * Short description of method analyseResult
     *
     * @access public
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
     * @param  Resource result
     * @param  Resource analyseFilter
     * @return core_kernel_classes_Resource
     */
    public function analyseResult( core_kernel_classes_Resource $result,  core_kernel_classes_Resource $analyseFilter)
    {
        $returnValue = null;

        // section 10-13-1-45-792423e0:12398d13f24:-8000:00000000000017F9 begin
        // section 10-13-1-45-792423e0:12398d13f24:-8000:00000000000017F9 end

        return $returnValue;
    }

    /**
     * Short description of method getResultsByGroup
     *
     * @access protected
     * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
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

} /* end of class taoResults_models_classes_ResultsService */

?>