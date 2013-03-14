<?php
/**
 * Results Controller provide actions performed from url resolution
 * 
 * @package taoResults
 * @subpackage actions
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 */
class taoResults_actions_Results extends tao_actions_TaoModule {

	/**
	 * constructor: initialize the service and the default data
	 * @return Results
	 */
	public function __construct()
	{
		parent::__construct();
		
		//the service is initialized by default
		$this->service = taoResults_models_classes_ResultsService::singleton();
		
		
		$this->defaultData();
	}
/*
 * conveniance methods
 */
	
	/**
	 * get the main class
	 * @return core_kernel_classes_Classes
	 */
	protected function getRootClass()
	{
		return $this->service->getResultClass();
	}
	
/*
 * controller actions
 */
	
	/**
	 * Edit a result class
	 * @return void
	 */
	public function editResultClass()
	{
		$clazz = $this->getCurrentClass();
		
		if($this->hasRequestParameter('property_mode')){
			$this->setSessionAttribute('property_mode', $this->getRequestParameter('property_mode'));
		}
		
		$myForm = $this->editClass($clazz, $this->service->getResultClass());
		if($myForm->isSubmited()){
			if($myForm->isValid()){
				if($clazz instanceof core_kernel_classes_Resource){
					$this->setSessionAttribute("showNodeUri", tao_helpers_Uri::encode($clazz->uriResource));
				}
				$this->setData('message', __('Class saved'));
				$this->setData('reload', true);
			}
		}
		$this->setData('formTitle', __('Edit result class'));
		$this->setData('myForm', $myForm->render());
		$this->setView('form.tpl', 'tao');
	}
	/**
	 * Edit a result instance
	 * @return void
	 */
	public function editResult()
	{
		$clazz = $this->getCurrentClass();
		$result = $this->getCurrentInstance();
		$formContainer = new tao_actions_form_Instance($clazz, $result);
		$myForm = $formContainer->getForm();
		if($myForm->isSubmited()){
			if($myForm->isValid()){
				
				$binder = new tao_models_classes_dataBinding_GenerisFormDataBinder($result);
				$result = $binder->bind($myForm->getValues());
				
				$this->setData('message', __('Result saved'));
				$this->setData('reload', true);
			}
		}
		$this->setSessionAttribute("showNodeUri", tao_helpers_Uri::encode($result->uriResource));
		$relatedSubjects = tao_helpers_Uri::encodeArray($this->service->getRelatedSubjects($result), tao_helpers_Uri::ENCODE_ARRAY_VALUES, true, true);
		$this->setData('relatedSubjects', json_encode(array_values($relatedSubjects)));
		$relatedDeliveries = tao_helpers_Uri::encodeArray($this->service->getRelatedDeliveries($result), tao_helpers_Uri::ENCODE_ARRAY_VALUES, true, true);
		$this->setData('relatedDeliveries', json_encode($relatedDeliveries));
		$this->setData('formTitle', 'Edit result');
		$this->setData('myForm', $myForm->render());
		$this->setView('form_result.tpl');
	}
	/**
	 * Add a result subclass
	 * @return void
	 */
	public function addSubClass()
	{
		if(!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		$clazz = $this->service->createSubClass($this->getCurrentClass());
		if(is_null($clazz) || !$clazz instanceof core_kernel_classes_Class){
		    throw new common_exception_Error('Unable to subclass '.$this->getCurrentClass()->getUri());
		}
		echo json_encode(array(
			'label'	=> $clazz->getLabel(),
			'uri' 	=> tao_helpers_Uri::encode($clazz->getUri())
		));
		
	}
	
	/**
	 * Delete a result or a result class
	 * @return void
	 */
	public function delete()
	{
		if(!tao_helpers_Request::isAjax()){
			throw new Exception("wrong request mode");
		}
		
		$deleted = false;
		if($this->getRequestParameter('uri')){
			$deleted = $this->service->deleteResult($this->getCurrentInstance());
		}
		else{
			$deleted = $this->service->deleteResultClass($this->getCurrentClass());
		}
		
		echo json_encode(array('deleted'	=> $deleted));
	}
	
	
	
	
}
?>