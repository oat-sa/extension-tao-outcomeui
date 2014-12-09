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
use \tao_helpers_Request;
use \tao_helpers_Uri;
use oat\taoOutcomeUi\model\ResultsService;
use oat\taoOutcomeUi\helper\ResultLabel;

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
        $variableUriCut = substr($variableUri,0,strpos($variableUri,'http://',1));
        $result = new \core_kernel_classes_Resource($variableUriCut);
        $deliveryExecution = $result->getOnePropertyValue(new \core_kernel_classes_Property(PROPERTY_DELVIERYEXECUTION_DELIVERY));

        $deliveryResultServer = $deliveryExecution->getOnePropertyValue(new \core_kernel_classes_Property(TAO_DELIVERY_RESULTSERVER_PROP));

        $resultServerModel = $deliveryResultServer->getOnePropertyValue(new \core_kernel_classes_Property(TAO_RESULTSERVER_MODEL_PROP));

        /** @var $implementationClass \core_kernel_classes_Literal*/
        $implementationClass = $resultServerModel->getOnePropertyValue(new \core_kernel_classes_Property(TAO_RESULTSERVER_MODEL_IMPL_PROP));

        if (class_exists($implementationClass->literal)) {
            $this->getClassService()->setImplementation($implementationClass->literal);
        }


        \common_Logger::w('variable : '.$variableUri);
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
