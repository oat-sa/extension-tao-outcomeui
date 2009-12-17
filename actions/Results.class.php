<?php
require_once('tao/actions/CommonModule.class.php');
require_once('tao/actions/TaoModule.class.php');

/**
 * Results Controller provide actions performed from url resolution
 * 
 * @author Bertrand Chevrier, <taosupport@tudor.lu>
 * @package taoResults
 * @subpackage actions
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * 
 */

class Results extends TaoModule {

	/**
	 * constructor: initialize the service and the default data
	 * @return Results
	 */
	public function __construct(){
		
		parent::__construct();
		
		//the service is initialized by default
		$this->service = tao_models_classes_ServiceFactory::get('Results');
		$this->defaultData();
	}
	
/*
 * conveniance methods
 */
	
	/**
	 * get the instancee of the current subject regarding the 'uri' and 'classUri' request parameters
	 * @return core_kernel_classes_Resource the subject instance
	 */
	private function getCurrentResult(){
		
		$uri = tao_helpers_Uri::decode($this->getRequestParameter('uri'));
		if(is_null($uri) || empty($uri)){
			throw new Exception("No valid uri found");
		}
		
		$clazz = $this->getCurrentClass();
		
		$result = $this->service->getResult($uri, 'uri', $clazz);
		if(is_null($result)){
			throw new Exception("No subject found for the uri {$uri}");
		}
		
		return $result;
	}
	
/*
 * controller actions
 */

	/**
	 * main action
	 * @return void
	 */
	public function index(){
		
		if($this->getData('reload') == true){
			unset($_SESSION[SESSION_NAMESPACE]['uri']);
			unset($_SESSION[SESSION_NAMESPACE]['classUri']);
		}
		
		$context = Context::getInstance();
		$this->setData('content', "this is the ". get_class($this) ." module, " . $context->getActionName());
		$this->setView('index.tpl');
	}
	
	/**
	 * Render json data to populate the subject tree 
	 * 'modelType' must be in request parameter
	 * @return void
	 */
	public function getResults(){
		
		if(!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		$highlightUri = '';
		if($this->hasSessionAttribute("showNodeUri")){
			$highlightUri = $this->getSessionAttribute("showNodeUri");
			unset($_SESSION[SESSION_NAMESPACE]["showNodeUri"]);
		} 
		$filter = '';
		if($this->hasRequestParameter('filter')){
			$filter = $this->getRequestParameter('filter');
		}
		echo json_encode($this->service->toTree( $this->service->getResultClass(), true, true, $highlightUri, $filter));
	}
	
	/**
	 * Add an subject instance
	 */
	public function addResult(){
		if(!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		$clazz = $this->getCurrentClass();
		$result = $this->service->createInstance($clazz);
		if(!is_null($result) && $result instanceof core_kernel_classes_Resource){
			echo json_encode(array(
				'label'	=> $result->getLabel(),
				'uri' 	=> tao_helpers_Uri::encode($result->uriResource)
			));
		}
	}
	
	/**
	 * edit an subject instance
	 */
	public function editResult(){
		$clazz = $this->getCurrentClass();
		$result = $this->getCurrentResult();
		$myForm = tao_helpers_form_GenerisFormFactory::instanceEditor($clazz, $result);
		if($myForm->isSubmited()){
			if($myForm->isValid()){
				
				$result = $this->service->bindProperties($result, $myForm->getValues());
				
				$this->setSessionAttribute("showNodeUri", tao_helpers_Uri::encode($result->uriResource));
				$this->setData('message', 'Result saved');
				$this->setData('reload', true);
				$this->forward('Results', 'index');
			}
		}
		
		$this->setData('formTitle', 'Edit result');
		$this->setData('myForm', $myForm->render());
		$this->setView('form.tpl');
	}
	
	/**
	 * add a subject model (subclass Result)
	 */
	public function addResultClass(){
		if(!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		$clazz = $this->service->createResultClass($this->getCurrentClass());
		if(!is_null($clazz) && $clazz instanceof core_kernel_classes_Class){
			echo json_encode(array(
				'label'	=> $clazz->getLabel(),
				'uri' 	=> tao_helpers_Uri::encode($clazz->uriResource)
			));
		}
	}
	
	/**
	 * Edit a subject model (edit a class)
	 */
	public function editResultClass(){
		$clazz = $this->getCurrentClass();
		$myForm = $this->editClass($clazz, $this->service->getResultClass());
		if($myForm->isSubmited()){
			if($myForm->isValid()){
				if($clazz instanceof core_kernel_classes_Resource){
					$this->setSessionAttribute("showNodeUri", tao_helpers_Uri::encode($clazz->uriResource));
				}
				$this->setData('message', 'class saved');
				$this->setData('reload', true);
				$this->forward('Results', 'index');
			}
		}
		$this->setData('formTitle', 'Edit result class');
		$this->setData('myForm', $myForm->render());
		$this->setView('form.tpl');
	}
	
	/**
	 * delete a subject or a subject model
	 * called via ajax
	 */
	public function delete(){
		if(!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		
		$deleted = false;
		if($this->getRequestParameter('uri')){
			$deleted = $this->service->deleteResult($this->getCurrentResult());
		}
		else{
			$deleted = $this->service->deleteResultClass($this->getCurrentClass());
		}
		
		echo json_encode(array('deleted'	=> $deleted));
	}
	
	/**
	 * duplicate a subject instance by property copy
	 */
	public function cloneResult(){
		if(!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		
		$result = $this->getCurrentResult();
		$clazz = $this->getCurrentClass();
		
		$clone = $this->service->createInstance($clazz);
		if(!is_null($clone)){
			
			foreach($clazz->getProperties() as $property){
				foreach($result->getPropertyValues($property) as $propertyValue){
					$clone->setPropertyValue($property, $propertyValue);
				}
			}
			$clone->setLabel($result->getLabel()."'");
			echo json_encode(array(
				'label'	=> $clone->getLabel(),
				'uri' 	=> tao_helpers_Uri::encode($clone->uriResource)
			));
		}
	}
	
	/**
	 * 
	 * @return 
	 */
	public function createTable(){
		
		$clazz = $this->service->getResultClass();
		$_SESSION['instances'] = array();
		
		foreach($clazz->getInstances(true) as $instance){
			$_SESSION['instances'][$instance->uriResource] =  $instance->getLabel();
		}
		$this->setView("create_table.tpl");
	}
	
	/*
	 * @TODO implement the following actions
	 */
	
	public function getMetaData(){
		throw new Exception("Not yet implemented");
	}
	
	public function saveComment(){
		throw new Exception("Not yet implemented");
	}
	
}
?>