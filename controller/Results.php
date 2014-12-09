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

namespace oat\taoOutcomeUi\controller;

use \Exception;
use \common_exception_IsAjaxAction;
use \core_kernel_classes_Resource;
use \tao_actions_SaSModule;
use \tao_helpers_Display;
use \tao_helpers_Request;
use \tao_helpers_Uri;
use oat\taoOutcomeUi\model\ResultsService;
use oat\taoOutcomeUi\helper\ResultLabel;
use Doctrine\DBAL\Schema\Index;

/**
 * Results Controller provide actions performed from url resolution
 *
 *
 * @author Patrick Plichart <patrick@taotesting.com>
 * @author Bertrand Chevrier, <taosupport@tudor.lu>
 * @package taoOutcomeUi
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 */
class Results extends tao_actions_SaSModule
{

    /**
     * constructor: initialize the service and the default data
     * @return Results
     */
    public function __construct()
    {
        parent::__construct();

        $this->defaultData();
        $this->service = self::getClassService();
    }

    protected function getClassService()
    {
        return ResultsService::singleton();
    }

    public function getOntologyData()
    {
        if (!tao_helpers_Request::isAjax()) {
            throw new common_exception_IsAjaxAction(__FUNCTION__);
        }
        
        $instances = array();
        
        if ($this->hasRequestParameter('classUri')) {
            
            $offset = $this->hasRequestParameter('offset') ? $this->getRequestParameter('offset') : 0;
            $limit = $this->hasRequestParameter('limit') ? $this->getRequestParameter('limit') : 0;

            // display delivery
            $delivery = new core_kernel_classes_Resource(tao_helpers_Uri::decode($this->getRequestParameter('classUri')));

            $deliveryResultServer = $delivery->getOnePropertyValue(new \core_kernel_classes_Property(TAO_DELIVERY_RESULTSERVER_PROP));

            $resultServerModel = $deliveryResultServer->getOnePropertyValue(new \core_kernel_classes_Property(TAO_RESULTSERVER_MODEL_PROP));

            $implementationClass = $resultServerModel->getOnePropertyValue(new \core_kernel_classes_Property(TAO_RESULTSERVER_MODEL_IMPL_PROP));

            if (class_exists($implementationClass->literal)) {
                $this->getClassService()->setImplementation($implementationClass->literal);
            }

            $storage = $this->getClassService()->getImplementation();
            
            $columns = array('http://www.tao.lu/Ontologies/TAOResult.rdf#resultOfDelivery');
            $filter = array('http://www.tao.lu/Ontologies/TAOResult.rdf#resultOfDelivery' => array($delivery->getUri()));
            foreach ($storage->getResultByColumn($columns, $filter) as $dataArray) {
                $result = new core_kernel_classes_Resource($dataArray['deliveryResultIdentifier']);
                
                $child = array();
                $child["attributes"] = array(
                    "id" => tao_helpers_Uri::encode($result->getUri()),
                    "class" => "node-instance",
                    'data-uri' => $result->getUri()
                );
                $testTaker = new core_kernel_classes_Resource($dataArray["testTakerIdentifier"]);
                $title = $testTaker->getLabel() . " (" . $result->getUri() . ")";
                $child["_data"] = array(
                    "uri" => $result->getUri(),
                );
                $child["data"] = $title;
                $child["type"] = "instance";
                $instances[] = $child;
            }
            
        } else {
            
            // root 
            $deliveryService = \taoDelivery_models_classes_DeliveryAssemblyService::singleton();
            foreach ($deliveryService->getAllAssemblies() as $assembly) {
                $child["attributes"] = array(
                    "id" => tao_helpers_Uri::encode($assembly->getUri()),
                    "class" => "node-class",
                    'data-uri' => $assembly->getUri()
                );
                $child["data"] = $assembly->getLabel();
                $child["type"] = "class";
                $child["state"] = "closed";
            
                $instances[] = $child;
            }
            
        }
        
        $this->returnJson($instances);
    }
    
    public function getOldOntologyData()
    {
        if (!tao_helpers_Request::isAjax()) {
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

        if ($this->hasRequestParameter('filter')) {
            $options['labelFilter'] = $this->getRequestParameter('filter');
        }

        if ($this->hasRequestParameter("selected")) {
            $options['browse'] = array($this->getRequestParameter("selected"));
        }
        if ($this->hasRequestParameter('hideInstances')) {
            if ((bool)$this->getRequestParameter('hideInstances')) {
                $options['instances'] = false;
            }
        }
        if ($this->hasRequestParameter('classUri')) {
            $clazz = $this->getCurrentClass();
            $options['chunk'] = true;
        } else {
            $clazz = $this->getRootClass();
        }
        if ($this->hasRequestParameter('offset')) {
            $options['offset'] = $this->getRequestParameter('offset');
        }
        if ($this->hasRequestParameter('limit')) {
            $options['limit'] = $this->getRequestParameter('limit');
        }
        if ($this->hasRequestParameter('subclasses')) {
            $options['subclasses'] = $this->getRequestParameter('subclasses');
        }
        $children = array();
        $returnValue = array();
        $rootClass = $this->getRootClass();

        if ($options['labelFilter'] != '*') {
            // get results
            foreach ($this->getClassService()->getImplementation()->getAllTestTakerIds() as $key => $association) {
                $result = new core_kernel_classes_Resource($association["deliveryResultIdentifier"]);
                $child = array();
                $delivery = new core_kernel_classes_Resource($this->getClassService()->getImplementation()->getDelivery(
                    $result->getUri()
                ));
                $testTaker = new core_kernel_classes_Resource($association["testTakerIdentifier"]);
                if (strpos(strtolower($testTaker->getLabel()), $options['labelFilter']) !== false || strpos(
                        strtolower($delivery->getLabel()),
                        $options['labelFilter']
                    ) !== false
                ) {
                    $child["attributes"] = array(
                        "id" => tao_helpers_Uri::encode($result->getUri()),
                        "class" => "node-instance",
                        'data-uri' => $result->getUri()
                    );
                    $resultLabel = new ResultLabel($result, $testTaker, $delivery);

                    $child["data"] = (string)$resultLabel;
                    $child["type"] = "instance";
                    $child["_data"] = array(
                        "uri" => $result->getUri(),
                        "class_uri" => $rootClass->getUri()
                    );
                    $children[] = $child;
                }
            }
            $childrenLimited = array_slice($children, $options['offset'], $options['limit']);
            if (count($children) != 0) {
                $returnValue = array(
                    "attributes" => array(
                        "class" => "node-class",
                        "id" => tao_helpers_Uri::encode($rootClass->getUri()),
                        'data-uri' => $rootClass->getUri()
                    ),
                    "_data" => array(
                        "uri" => $rootClass->getUri(),
                        "class_uri" => null
                    ),
                    "children" => $childrenLimited,
                    "count" => count($children),
                    "data" => "Result",
                    "type" => "class",
                );
            }
        } else {
            //root class
            if (!$options['chunk']) {
                // get subclasses
                foreach ($clazz->getSubClasses(false) as $subclass) {
                    $child["attributes"] = array(
                        "id" => tao_helpers_Uri::encode($subclass->getUri()),
                        "class" => "node-class",
                        'data-uri' => $subclass->getUri()
                    );
                    $child["data"] = $subclass->getLabel();
                    $child["type"] = "class";

                    if ($subclass->countInstances() > 0) {
                        $child["state"] = "closed";
                    }

                    $children[] = $child;
                }
                if ($options['instances']) {
                    // get results
                    $instances = array();
                    foreach ($this->getClassService()->getImplementation()->getAllTestTakerIds(
                             ) as $key => $association) {
                        $result = new core_kernel_classes_Resource($association["deliveryResultIdentifier"]);
                        if (in_array(CLASS_DELVIERYEXECUTION, array_keys($result->getTypes())) || in_array(
                                $rootClass->getUri(),
                                array_keys($result->getTypes())
                            )
                        ) {
                            $child = array();
                            $delivery = new core_kernel_classes_Resource($this->getClassService()->getImplementation(
                            )->getDelivery($result->getUri()));
                            $child["attributes"] = array(
                                "id" => tao_helpers_Uri::encode($result->getUri()),
                                "class" => "node-instance",
                                'data-uri' => $result->getUri()
                            );
                            $testTaker = new core_kernel_classes_Resource($association["testTakerIdentifier"]);
                            $title = $testTaker->getLabel() . "-(" . $result->getUri() . ")- " . $delivery->getLabel();
                            $child["_data"] = array(
                                "uri" => $result->getUri(),
                                "class_uri" => $rootClass->getUri()
                            );
                            $child["data"] = $title;
                            $child["type"] = "instance";
                            $instances[] = $child;
                        }
                    }
                }
                $childrenLimited = array_merge(
                    $children,
                    array_slice($instances, $options['offset'], $options['limit'])
                );
                $returnValue = array(
                    "attributes" => array(
                        "class" => "node-class",
                        "id" => tao_helpers_Uri::encode($rootClass->getUri()),
                        'data-uri' => $rootClass->getUri()
                    ),
                    "_data" => array(
                        "uri" => $rootClass->getUri(),
                        "class_uri" => null
                    ),
                    "data" => "Result",
                    "type" => "class",

                );
                if (count($instances) > 0) {
                    $returnValue["state"] = "open";
                    $returnValue["children"] = $childrenLimited;
                    $returnValue["count"] = count($instances);
                } else {
                    $returnValue["state"] = "close";
                }
            } // subclass details
            else {
                // get subclasses
                foreach ($clazz->getSubClasses(false) as $subclass) {
                    $child["attributes"] = array(
                        "id" => tao_helpers_Uri::encode($subclass->getUri()),
                        "class" => "node-class",
                        'data-uri' => $subclass->getUri()
                    );
                    $child["data"] = $subclass->getLabel();
                    $child["type"] = "class";
                    $child["_data"] = array(
                        "uri" => $subclass->getUri(),
                        "class_uri" => $clazz->getUri()
                    );
                    if ($subclass->countInstances()) {
                        $child["state"] = "closed";
                    }

                    $children[] = $child;
                }
                if ($options['instances']) {
                    // get results
                    $instances = $clazz->searchInstances(
                        array(RDF_TYPE => $clazz->getUri()),
                        array('recursive' => false)
                    );
                    foreach ($instances as $instance) {
                        $child = array();
                        $delivery = new core_kernel_classes_Resource(
                            $this->getClassService()->getImplementation()->getDelivery($instance->getUri())
                        );
                        $child["attributes"] = array(
                            "id" => tao_helpers_Uri::encode($instance->getUri()),
                            "class" => "node-instance",
                            'data-uri' => $instance->getUri()
                        );
                        $testTaker = new core_kernel_classes_Resource(
                            $this->getClassService()->getImplementation()->getTestTaker($instance->getUri())
                        );
                        $title = $testTaker->getLabel() . "-(" . $instance->getUri() . ")- " . $delivery->getLabel();

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

    public function index()
    {
        //Properties to filter on
        $properties = array(
            new \core_kernel_classes_Property(RDFS_LABEL),
            new \core_kernel_classes_Property(RDF_TYPE)
        );

        // display delivery
        $delivery = new core_kernel_classes_Resource(tao_helpers_Uri::decode($this->getRequestParameter('classUri')));

        $deliveryResultServer = $delivery->getOnePropertyValue(new \core_kernel_classes_Property(TAO_DELIVERY_RESULTSERVER_PROP));

        $resultServerModel = $deliveryResultServer->getOnePropertyValue(new \core_kernel_classes_Property(TAO_RESULTSERVER_MODEL_PROP));

        $implementationClass = $resultServerModel->getOnePropertyValue(new \core_kernel_classes_Property(TAO_RESULTSERVER_MODEL_IMPL_PROP));


        $model = array();
        foreach($properties as $property){
            $model[] = array(
                'id'       => $property->getUri(),
                'label'    => $property->getLabel(),
                'sortable' => true
            );
        }

        $this->setData('implementation',urlencode($implementationClass->literal));
        $this->setData('model',$model);

        $this->setView('resultList.tpl');
    }

    public function getResults()
    {
        $page = $this->getRequestParameter('page');
        $limit = $this->getRequestParameter('rows');
        $order = $this->getRequestParameter('sortby');
        $sord = $this->getRequestParameter('sortorder');
        $start = $limit * $page - $limit;

        $gau = array(
            'order' 	=> $order,
            'orderdir'	=> strtoupper($sord),
            'offset'    => $start,
            'limit'		=> $limit,
            'recursive' => true
        );

        // Get filter parameter
        $filter = array();
        if($this->hasRequestParameter('filter')){
            $filter = $this->getFilterState('filter');
        }
        if($this->hasRequestParameter('implementation')){
            if (class_exists(urldecode($this->getRequestParameter('implementation')))) {
                $this->getClassService()->setImplementation(urldecode($this->getRequestParameter('implementation')));
            }
        }

        $columns = array_keys($filter);

        $data = array();
        $results = $this->getClassService()->getImplementation()->getResultByColumn($columns, $filter, $gau);
        $counti = $this->getClassService()->getImplementation()->countResultByFilter($columns, $filter);
        foreach($results as $res){

            $deliveryResult = new core_kernel_classes_Resource($res['deliveryResultIdentifier']);
            $delivery = new core_kernel_classes_Resource($res['deliveryIdentifier']);
            $testTaker = new core_kernel_classes_Resource($res['testTakerIdentifier']);
            $label = new ResultLabel($deliveryResult, $testTaker, $delivery);
            $types = $deliveryResult->getTypes();

            $data[] = array(
                'id'                           => $deliveryResult->getUri(),
                RDFS_LABEL                     => (string)$label,
                RDF_TYPE                       => array_shift($types)->getLabel()
            );
        }
        $this->returnJSON(array(
                'data' => $data,
                'page' => floor($start / $limit) + 1,
                'total' => ceil($counti / $limit),
                'records' => count($data)
            ));
    }

    /**
     * Edit a result class
     * @return void
     */
    public function editResultClass()
    {
        $clazz = $this->getCurrentClass();

        if ($this->hasRequestParameter('property_mode')) {
            $this->setSessionAttribute('property_mode', $this->getRequestParameter('property_mode'));
        }

        $myForm = $this->editClass($clazz, $this->getRootClass());
        if ($myForm->isSubmited()) {
            if ($myForm->isValid()) {
                if ($clazz instanceof core_kernel_classes_Resource) {
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
        if (!tao_helpers_Request::isAjax()) {
            throw new Exception("wrong request mode");
        }
        $deleted = $this->getClassService()->deleteResult($this->getRequestParameter('id'));
        $this->returnJson(array('deleted' => $deleted));
    }

    /**
     *
     * @author Patrick Plichart <patrick@taotesting.com>
     */
    public function viewResult()
    {
        $result = $this->getCurrentInstance();
        $deliveryExecution = $result->getOnePropertyValue(new \core_kernel_classes_Property(PROPERTY_DELVIERYEXECUTION_DELIVERY));

        $deliveryResultServer = $deliveryExecution->getOnePropertyValue(new \core_kernel_classes_Property(TAO_DELIVERY_RESULTSERVER_PROP));

        $resultServerModel = $deliveryResultServer->getOnePropertyValue(new \core_kernel_classes_Property(TAO_RESULTSERVER_MODEL_PROP));

        /** @var $implementationClass \core_kernel_classes_Literal*/
        $implementationClass = $resultServerModel->getOnePropertyValue(new \core_kernel_classes_Property(TAO_RESULTSERVER_MODEL_IMPL_PROP));

        if (class_exists($implementationClass->literal)) {
            $this->getClassService()->setImplementation($implementationClass->literal);
        }

        $testTaker = $this->getClassService()->getTestTakerData($result);

        if (
            (is_object($testTaker) and (get_class($testTaker) == 'core_kernel_classes_Literal'))
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
            $login = (count($testTaker[PROPERTY_USER_LOGIN]) > 0) ? current(
                $testTaker[PROPERTY_USER_LOGIN]
            )->literal : "";
            $label = (count($testTaker[RDFS_LABEL]) > 0) ? current($testTaker[RDFS_LABEL])->literal : "";
            $firstName = (count($testTaker[PROPERTY_USER_FIRSTNAME]) > 0) ? current(
                $testTaker[PROPERTY_USER_FIRSTNAME]
            )->literal : "";
            $userLastName = (count($testTaker[PROPERTY_USER_LASTNAME]) > 0) ? current(
                $testTaker[PROPERTY_USER_LASTNAME]
            )->literal : "";
            $userEmail = (count($testTaker[PROPERTY_USER_MAIL]) > 0) ? current(
                $testTaker[PROPERTY_USER_MAIL]
            )->literal : "";

            $this->setData('userLogin', $login);
            $this->setData('userLabel', $label);
            $this->setData('userFirstName', $firstName);
            $this->setData('userLastName', $userLastName);
            $this->setData('userEmail', $userEmail);
        }
        $filter = ($this->hasRequestParameter("filter")) ? $this->getRequestParameter("filter") : "lastSubmitted";
        $stats = $this->getClassService()->getItemVariableDataStatsFromDeliveryResult($result, $filter);
        $this->setData('nbResponses', $stats["nbResponses"]);
        $this->setData('nbCorrectResponses', $stats["nbCorrectResponses"]);
        $this->setData('nbIncorrectResponses', $stats["nbIncorrectResponses"]);
        $this->setData('nbUnscoredResponses', $stats["nbUnscoredResponses"]);
        $this->setData('deliveryResultLabel', $result->getLabel());
        $this->setData('variables', $stats["data"]);
        //retireve variables not related to item executions
        $deliveryVariables = $this->getClassService()->getVariableDataFromDeliveryResult($result);
        $this->setData('deliveryVariables', $deliveryVariables);
        $this->setData('uri', $this->getRequestParameter("uri"));
        $this->setData('classUri', $this->getRequestParameter("classUri"));
        $this->setData('filter', $filter);
        $this->setView('viewResult.tpl');
    }

    public function getFile()
    {
        $variableUri = $this->getRequestParameter("variableUri");
        $file = $this->getClassService()->getVariableFile($variableUri);
        $trace = $file["data"];
        header(
            'Set-Cookie: fileDownload=true'
        ); //used by jquery file download to find out the download has been triggered ...
        setcookie("fileDownload", "true", 0, "/");
        header("Content-type: " . $file["mimetype"]);
        if (!isset($file["filename"]) || $file["filename"] == "") {
            header('Content-Disposition: attachment; filename=download');
        } else {
            header('Content-Disposition: attachment; filename=' . $file["filename"]);
        }

        echo $file["data"];
    }
}
