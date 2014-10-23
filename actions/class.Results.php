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
        // load the constants
        common_ext_ExtensionsManager::singleton()->getExtensionById('taoDelivery');

		$this->defaultData();
	}
	
	protected function getClassService()
	{
		return taoResults_models_classes_ResultsService::singleton();
	}

    public function getOntologyData(){
        if(!tao_helpers_Request::isAjax()){
            throw new common_exception_IsAjaxAction(__FUNCTION__);
        }

        $options = array(
            'subclasses' => true,
            'instances' => true,
            'highlightUri' => '',
            'labelFilter' => '',
            'chunk' => false,
            'offset' => 0,
            'limit' => 0
        );

        if($this->hasRequestParameter('filter')){
            $options['labelFilter'] = $this->getRequestParameter('filter');
        }

        if($this->hasRequestParameter("selected")){
            $options['browse'] = array($this->getRequestParameter("selected"));
        }
        if($this->hasRequestParameter('hideInstances')){
            if((bool) $this->getRequestParameter('hideInstances')){
                $options['instances'] = false;
            }
        }
        if($this->hasRequestParameter('classUri')){
            $clazz = $this->getCurrentClass();
            $options['chunk'] = true;
        }
        else{
            $clazz = $this->getRootClass();
        }
        if($this->hasRequestParameter('offset')){
            $options['offset'] = $this->getRequestParameter('offset');
        }
        if($this->hasRequestParameter('limit')){
            $options['limit'] = $this->getRequestParameter('limit');
        }
        if($this->hasRequestParameter('subclasses')){
            $options['subclasses'] = $this->getRequestParameter('subclasses');
        }
        $children = array();
        $returnValue = array();

        if($options['labelFilter'] != '*'){
            // get results
            foreach($this->getClassService()->getImplementation()->getAllTestTakerIds() as $key => $association){
                $result = new core_kernel_classes_Resource($association["deliveryResultIdentifier"]);
                $child = array();
                $delivery = new core_kernel_classes_Resource($this->getClassService()->getImplementation()->getDelivery($result->getUri()));
                $testTaker = new core_kernel_classes_Resource($association["testTakerIdentifier"]);
                if(strpos(strtolower($testTaker->getLabel()),$options['labelFilter']) !== FALSE || strpos(strtolower($delivery->getLabel()),$options['labelFilter']) !== FALSE){
                    $child["attributes"] = array("id" => tao_helpers_Uri::encode($result->getUri()), "class" => "node-instance");
                    $title = $testTaker->getLabel()."-(".$result->getUri().")- ".$delivery->getLabel();

                    $child["data"] = $title;
                    $child["type"] = "instance";
                    $child["_data"] = array(
                        "uri" => $result->getUri(),
                        "class_uri" => TAO_DELIVERY_RESULT
                    );
                    $children[] = $child;
                }
            }
            $childrenLimited = array_slice($children,$options['offset'],$options['limit']);
            if(count($children) != 0){
                $returnValue = array(
                    "attributes" => array(
                        "class" => "node-class",
                        "id" => tao_helpers_Uri::encode(TAO_DELIVERY_RESULT),
                    ),
                    "_data" => array(
                        "uri" => TAO_DELIVERY_RESULT,
                        "class_uri" => null
                    ),
                    "children" => $childrenLimited,
                    "count" => count($children),
                    "data" => "Result",
                    "type" => "class",
                );
            }
        }
        else{
            //root class
            if(!$options['chunk']){
                // get subclasses
                foreach ($clazz->getSubClasses(false) as $subclass) {
                    $child["attributes"] = array("id" => tao_helpers_Uri::encode($subclass->getUri()), "class" => "node-class");
                    $child["data"] = $subclass->getLabel();
                    $child["type"] = "class";

                    if($subclass->countInstances() > 0){
                        $child["state"] = "closed";
                    }

                    $children[] = $child;
                }
                if($options['instances']){
                    // get results
                    $instances = array();
                    foreach($this->getClassService()->getImplementation()->getAllTestTakerIds() as $key => $association){
                            $result = new core_kernel_classes_Resource($association["deliveryResultIdentifier"]);
                            if(in_array(CLASS_DELVIERYEXECUTION,array_keys($result->getTypes())) || in_array(TAO_DELIVERY_RESULT,array_keys($result->getTypes()))){
                                $child = array();
                                $delivery = new core_kernel_classes_Resource($this->getClassService()->getImplementation()->getDelivery($result->getUri()));
                                $child["attributes"] = array("id" => tao_helpers_Uri::encode($result->getUri()), "class" => "node-instance");
                                $testTaker = new core_kernel_classes_Resource($association["testTakerIdentifier"]);
                                $title = $testTaker->getLabel()."-(".$result->getUri().")- ".$delivery->getLabel();
                                $child["_data"] = array(
                                    "uri" => $result->getUri(),
                                    "class_uri" => TAO_DELIVERY_RESULT
                                );
                                $child["data"] = $title;
                                $child["type"] = "instance";
                                $instances[] = $child;
                            }
                    }
                }
                $childrenLimited = array_merge($children,array_slice($instances,$options['offset'], $options['limit']));
                $returnValue = array(
                    "attributes" => array(
                        "class" => "node-class",
                        "id" => tao_helpers_Uri::encode(TAO_DELIVERY_RESULT),
                    ),
                    "_data" => array(
                        "uri" => TAO_DELIVERY_RESULT,
                        "class_uri" => null
                    ),
                    "children" => $childrenLimited,
                    "count" => count($instances),
                    "data" => "Result",
                    "state" => "open",
                    "type" => "class",

                );
            }
            // subclass details
            else{
                // get subclasses
                foreach ($clazz->getSubClasses(false) as $subclass) {
                    $child["attributes"] = array("id" => tao_helpers_Uri::encode($subclass->getUri()), "class" => "node-class");
                    $child["data"] = $subclass->getLabel();
                    $child["type"] = "class";
                    $child["_data"] = array(
                        "uri" => $subclass->getUri(),
                        "class_uri" => $clazz->getUri()
                    );
                    if($subclass->countInstances()){
                        $child["state"] = "closed";
                    }

                    $children[] = $child;
                }
                if($options['instances']){
                    // get results
                    $instances = $clazz->searchInstances(array(RDF_TYPE => $clazz->getUri()), array('recursive' => false));
                    foreach($instances as $instance){
                            $child = array();
                            $delivery = new core_kernel_classes_Resource($this->getClassService()->getImplementation()->getDelivery($instance->getUri()));
                            $child["attributes"] = array("id" => tao_helpers_Uri::encode($instance->getUri()), "class" => "node-instance");
                            $testTaker = new core_kernel_classes_Resource($this->getClassService()->getImplementation()->getTestTaker($instance->getUri()));
                            $title = $testTaker->getLabel()."-(".$instance->getUri().")- ".$delivery->getLabel();

                            $child["data"] = $title;
                            $child["type"] = "instance";
                            $child["_data"] = array(
                                "uri" => $instance->getUri(),
                                "class_uri" => $clazz->getUri()
                            );
                            $children[] = $child;
                    }
                }
                $returnValue = $children;
            }

        }
        echo json_encode($returnValue);
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
		
		$myForm = $this->editClass($clazz, $this->getClassService()->getRootClass());
		if($myForm->isSubmited()){
			if($myForm->isValid()){
				if($clazz instanceof core_kernel_classes_Resource){
					$this->setData("selectNode", tao_helpers_Uri::encode($clazz->getUri()));
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
			$deleted = $this->getClassService()->deleteResult($this->getCurrentInstance());
		}
		else{
			$deleted = $this->getClassService()->deleteResultClass($this->getCurrentClass());
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
        if($this->hasRequestParameter('implementation')){
            $this->getClassService()->setImplementation($this->getRequestParameter('implementation'));
        }

        $testTaker = $this->getClassService()->getTestTakerData($result);

        if (
                (is_object($testTaker) and (get_class($testTaker)=='core_kernel_classes_Literal'))
                or 
                (is_null($testTaker))
            ) {
            //the test taker is unknown
        $this->setData('userLogin', $testTaker);
        $this->setData('userLabel', $testTaker);
        $this->setData('userFirstName', $testTaker);
        $this->setData('userLastName', $testTaker);
        $this->setData('userEmail', $testTaker);
        } else {
           $login = (count($testTaker[PROPERTY_USER_LOGIN])>0) ? current($testTaker[PROPERTY_USER_LOGIN])->literal :"";
            $label = (count($testTaker[RDFS_LABEL])>0) ? current($testTaker[RDFS_LABEL])->literal:"";
            $firstName = (count($testTaker[PROPERTY_USER_FIRSTNAME])>0) ? current($testTaker[PROPERTY_USER_FIRSTNAME])->literal:"";
            $userLastName = (count($testTaker[PROPERTY_USER_LASTNAME])>0) ? current($testTaker[PROPERTY_USER_LASTNAME])->literal:"";
            $userEmail = (count($testTaker[PROPERTY_USER_MAIL])>0) ? current($testTaker[PROPERTY_USER_MAIL])->literal:"";

            $this->setData('userLogin', $login);
            $this->setData('userLabel', $label);
            $this->setData('userFirstName', $firstName);
            $this->setData('userLastName', $userLastName);
            $this->setData('userEmail', $userEmail);
        }
        $filter = ($this->hasRequestParameter("filter")) ? $this->getRequestParameter("filter") : "lastSubmitted";
        $stats = $this->getClassService()->getItemVariableDataStatsFromDeliveryResult($result, $filter);
        $this->setData('nbResponses',  $stats["nbResponses"]);
        $this->setData('nbCorrectResponses',  $stats["nbCorrectResponses"]);
        $this->setData('nbIncorrectResponses',  $stats["nbIncorrectResponses"]);
        $this->setData('nbUnscoredResponses',  $stats["nbUnscoredResponses"]);   
        $this->setData('deliveryResultLabel', $result->getLabel());
        $this->setData('variables',  $stats["data"]);
        //retireve variables not related to item executions
        $deliveryVariables = $this->getClassService()->getVariableDataFromDeliveryResult($result);
        $this->setData('deliveryVariables', $deliveryVariables);
        $this->setData('uri',$this->getRequestParameter("uri"));
        $this->setData('classUri',$this->getRequestParameter("classUri"));
        $this->setData('filter',$filter);
        $this->setView('viewResult.tpl');
    }
   
     public function getFile(){
        $variableUri = $this->getRequestParameter("variableUri");
        $file = $this->getClassService()->getVariableFile($variableUri);
        $trace = $file["data"];
        header('Set-Cookie: fileDownload=true'); //used by jquery file download to find out the download has been triggered ...
        setcookie("fileDownload","true", 0, "/");
        header("Content-type: ".$file["mimetype"]);
        if (!isset($file["filename"]) || $file["filename"]==""){
            header('Content-Disposition: attachment; filename=download');
        } else {
            header('Content-Disposition: attachment; filename='.$file["filename"]);
        }
        
        echo $file["data"];
    }
}
?>
