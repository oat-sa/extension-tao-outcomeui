<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2002-2008 (original work) Public Research Centre Henri Tudor & University of Luxembourg (under the project TAO & TAO2);
 *               2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV); *
 */

/**
 * Results Controller provide actions performed from url resolution
 *
 *
 * @author Patrick Plichart <patrick@taotesting.com>
 * @author Bertrand Chevrier, <taosupport@tudor.lu>
 * @package taoResults
 * @subpackage actions
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 */
class taoResults_actions_Results extends tao_actions_SaSModule {

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
	
	protected function getClassService()
	{
		return taoResults_models_classes_ResultsService::singleton();
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
		
		$myForm = $this->editClass($clazz, $this->service->getRootClass());
		if($myForm->isSubmited()){
			if($myForm->isValid()){
				if($clazz instanceof core_kernel_classes_Resource){
					$this->setSessionAttribute("showNodeUri", tao_helpers_Uri::encode($clazz->getUri()));
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
		$this->setSessionAttribute("showNodeUri", tao_helpers_Uri::encode($result->getUri()));
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
	/**
     *
     * @author Patrick Plichart <patrick@taotesting.com>
     */
    public function viewResult()
    {
        $result = $this->getCurrentInstance();
        $testTaker = $this->service->getTestTaker($result);
        $this->setData('TestTakerLabel', $testTaker->getLabel());
        $values = $testTaker->getPropertyValues(new core_kernel_classes_Property(PROPERTY_USER_LOGIN));
        $this->setData('TestTakerLogin', array_pop($values));
        $variablesByItem = $this->service->getItemVariableDataFromDeliveryResult($result);
        $this->setData('deliveryResultLabel', $result->getLabel());
        $this->setData('variables',  $variablesByItem);
        $this->setView('viewResult.tpl');
    }




    
}
?>