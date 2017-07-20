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
use oat\oatbox\event\EventManager;
use oat\tao\model\accessControl\AclProxy;
use oat\tao\model\plugins\PluginModule;
use oat\taoDelivery\model\execution\ServiceProxy;
use oat\taoOutcomeUi\helper\ResponseVariableFormatter;
use oat\taoOutcomeUi\model\event\ResultsListPluginEvent;
use oat\taoOutcomeUi\model\plugins\ResultsPluginService;
use oat\taoOutcomeUi\model\Wrapper\ResultServiceWrapper;
use oat\taoResultServer\models\classes\QtiResultsService;
use \tao_actions_SaSModule;
use \tao_helpers_Request;
use \tao_helpers_Uri;
use oat\taoOutcomeUi\model\ResultsService;
use oat\taoDeliveryRdf\model\DeliveryAssemblyService;
use oat\taoResultServer\models\classes\ResultServerService;

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

    private $deliveryService;

    /**
     * constructor: initialize the service and the default data
     * @return Results
     */
    public function __construct()
    {
        parent::__construct();

        $this->service = $this->getServiceManager()->get(ResultServiceWrapper::SERVICE_ID)->getService();
        $this->deliveryService = DeliveryAssemblyService::singleton();
        $this->defaultData();
    }

    /**
     * @return ResultsService
     */
    protected function getClassService()
    {
        return $this->service;
    }

    /**
     * Ontology data for deliveries (not results, so use deliveryService->getRootClass)
     * @throws common_exception_IsAjaxAction
     */
    public function getOntologyData()
    {
        if (!tao_helpers_Request::isAjax()) {
            throw new common_exception_IsAjaxAction(__FUNCTION__);
        }

        $options = array(
            'subclasses' => true,
            'instances' => true,
            'highlightUri' => '',
            'chunk' => false,
            'offset' => 0,
            'limit' => 0
        );

        if ($this->hasRequestParameter('loadNode')) {
            $options['uniqueNode'] = $this->getRequestParameter('loadNode');
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
            $options['chunk'] = !$clazz->equals($this->deliveryService->getRootClass());
        } else {
            $clazz = $this->deliveryService->getRootClass();
        }

        if ($this->hasRequestParameter('offset')) {
            $options['offset'] = $this->getRequestParameter('offset');
        }

        if ($this->hasRequestParameter('limit')) {
            $options['limit'] = $this->getRequestParameter('limit');
        }

        //generate the tree from the given parameters
        $tree = $this->getClassService()->toTree($clazz, $options);

        $tree = $this->addPermissions($tree);

        function sortTree(&$tree)
        {
            usort($tree, function ($a, $b) {
                if (isset($a['data']) && isset($b['data'])) {
                    if ($a['type'] != $b['type']) {
                        return ($a['type'] == 'class') ? -1 : 1;
                    } else {
                        return strcasecmp($a['data'], $b['data']);
                    }
                }
                return 0;
            });
        }

        if (isset($tree['children'])) {
            sortTree($tree['children']);
        } elseif (array_values($tree) === $tree) {//is indexed array
            sortTree($tree);
        }

        //expose the tree
        $this->returnJson($tree);
    }

    /**
     * Action called on click on a delivery (class) construct and call the view to see the table of
     * all delivery execution for a specific delivery
     */
    public function index()
    {
        $model = array(
            array(
                'id' => 'ttaker',
                'label' => __('Test Taker'),
                'sortable' => false
            ),
            array(
                'id' => 'time',
                'label' => __('Start Time'),
                'sortable' => false
            )
        );

        $deliveryService = DeliveryAssemblyService::singleton();
        $delivery = new core_kernel_classes_Resource($this->getRequestParameter('id'));
        if ($delivery->getUri() !== $deliveryService->getRootClass()->getUri()) {

            try {
                // display delivery
                $this->getResultStorage($delivery);

                $this->setData('uri', $delivery->getUri());
                $this->setData('title', $delivery->getLabel());
                $this->setData('config', [
                    'dataModel' => $model,
                    'plugins' => $this->getResultsListPlugin()
                ]);

                $this->setView('resultList.tpl');
            } catch (\common_exception_Error $e) {
                $this->setData('type', 'error');
                $this->setData('error', $e->getMessage());
                $this->setView('index.tpl');
            }

        } else {
            $this->setData('type', 'info');
            $this->setData('error', __('No tests have been taken yet. As soon as a test-taker will take a test his results will be displayed here.'));
            $this->setView('index.tpl');
        }
    }


    /**
     * get all result delivery execution to display
     */
    public function getResults()
    {
        $page = $this->getRequestParameter('page');
        $limit = $this->getRequestParameter('rows');
        $order = $this->getRequestParameter('sortby');
        $sort = $this->getRequestParameter('sortorder');
        $start = $limit * $page - $limit;

        $gau = array(
            'order' => $order,
            'orderdir' => strtoupper($sort),
            'offset' => $start,
            'limit' => $limit,
            'recursive' => true
        );

        $delivery = new \core_kernel_classes_Resource(tao_helpers_Uri::decode($this->getRequestParameter('classUri')));

        try {
            $this->getResultStorage($delivery);

            $data = array();
            $readOnly = array();
            $user = \common_session_SessionManager::getSession()->getUser();
            $rights = array(
                'view' => !AclProxy::hasAccess($user, 'oat\taoOutcomeUi\controller\Results', 'viewResult', array()),
                'delete' => !AclProxy::hasAccess($user, 'oat\taoOutcomeUi\controller\Results', 'delete', array()));
            $results = $this->getClassService()->getImplementation()->getResultByDelivery(array($delivery->getUri()), $gau);
            $count = $this->getClassService()->getImplementation()->countResultByDelivery(array($delivery->getUri()));
            foreach ($results as $res) {

                $deliveryExecution = ServiceProxy::singleton()->getDeliveryExecution($res['deliveryResultIdentifier']);
                $testTaker = new core_kernel_classes_Resource($res['testTakerIdentifier']);

                try {
                    $startTime = \tao_helpers_Date::displayeDate($deliveryExecution->getStartTime());
                } catch (\common_exception_NotFound $e) {
                    \common_Logger::w($e->getMessage());
                    $startTime = '';
                }

                $data[] = array(
                    'id' => $deliveryExecution->getIdentifier(),
                    'ttaker' => empty($testTaker->getLabel())? $res['testTakerIdentifier'] : _dh($testTaker->getLabel()),
                    'time' => $startTime,
                );

                $readOnly[$deliveryExecution->getIdentifier()] = $rights;
            }

            $this->returnJson(array(
                'data' => $data,
                'page' => floor($start / $limit) + 1,
                'total' => ceil($count / $limit),
                'records' => count($data),
                'readonly' => $readOnly
            ));
        } catch (\common_exception_Error $e) {
            $this->returnJson(array(
                'error' => $e->getMessage()
            ));
        }
    }

    /**
     * Delete a result or a result class
     * @throws Exception
     * @return string json {'deleted' : true}
     */
    public function delete()
    {
        if (!tao_helpers_Request::isAjax()) {
            throw new Exception("wrong request mode");
        }
        $deliveryExecutionUri = tao_helpers_Uri::decode($this->getRequestParameter('uri'));
        $de = ServiceProxy::singleton()->getDeliveryExecution($deliveryExecutionUri);

        try {
            $this->getResultStorage($de->getDelivery());

            $deleted = $this->getClassService()->deleteResult($deliveryExecutionUri);

            $this->returnJson(array('deleted' => $deleted));
        } catch (\common_exception_Error $e) {
            $this->returnJson(array('error' => $e->getMessage()));
        }
    }

    /**
     * Get info on the current Result and display it
     */
    public function viewResult()
    {
        $resultId = $this->getRawParameter('id');
        $delivery = new \core_kernel_classes_Resource($this->getRequestParameter('classUri'));

        try {
            $this->getResultStorage($delivery);

            $testTaker = $this->getClassService()->getTestTakerData($resultId);

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
            $filterSubmission = ($this->hasRequestParameter("filterSubmission")) ? $this->getRequestParameter("filterSubmission") : "lastSubmitted";
            $filterTypes = ($this->hasRequestParameter("filterTypes")) ? $this->getRequestParameter("filterTypes") : array(\taoResultServer_models_classes_ResponseVariable::class, \taoResultServer_models_classes_OutcomeVariable::class, \taoResultServer_models_classes_TraceVariable::class);
            $variables = $this->getResultVariables($resultId, $filterSubmission, $filterTypes);
            $this->setData('variables', $variables);

            $stats = $this->getClassService()->calculateResponseStatistics($variables);
            $this->setData('nbResponses', $stats["nbResponses"]);
            $this->setData('nbCorrectResponses', $stats["nbCorrectResponses"]);
            $this->setData('nbIncorrectResponses', $stats["nbIncorrectResponses"]);
            $this->setData('nbUnscoredResponses', $stats["nbUnscoredResponses"]);

            //retireve variables not related to item executions
            $deliveryVariables = $this->getClassService()->getVariableDataFromDeliveryResult($resultId, $filterTypes);
            $this->setData('deliveryVariables', $deliveryVariables);
            $this->setData('id', $this->getRawParameter("id"));
            $this->setData('classUri', $this->getRequestParameter("classUri"));
            $this->setData('filterSubmission', $filterSubmission);
            $this->setData('filterTypes', $filterTypes);
            $this->setView('viewResult.tpl');
        } catch (\common_exception_Error $e) {
            $this->setData('type', 'error');
            $this->setData('error', $e->getMessage());
            $this->setView('index.tpl');
        }
    }

    /**
     * Download delivery execution XML
     *
     * @author Gyula Szucs, <gyula@taotesting.com>
     * @throws \common_exception_MissingParameter
     * @throws \common_exception_NotFound
     * @throws \common_exception_ValidationFailed
     */
    public function downloadXML()
    {
        try {
            if (!$this->hasRequestParameter('id') || empty($this->getRequestParameter('id'))) {
                throw new \common_exception_MissingParameter('Result id is missing from the request.', $this->getRequestURI());
            }
            if (!$this->hasRequestParameter('delivery') || empty($this->getRequestParameter('delivery'))) {
                throw new \common_exception_MissingParameter('Delivery id is missing from the request.', $this->getRequestURI());
            }

            $qtiResultService = $this->getServiceManager()->get(QtiResultsService::SERVICE_ID);
            $xml = $qtiResultService->getQtiResultXml($this->getRequestParameter('delivery'), $this->getRawParameter('id'));

            header('Set-Cookie: fileDownload=true'); //used by jquery file download to find out the download has been triggered ...
            setcookie("fileDownload", "true", 0, "/");
            header('Content-Disposition: attachment; filename="delivery_execution_' . date('YmdHis') . '.xml"');
            header('Content-Type: application/xml');

            echo $xml;
        } catch (\common_exception_UserReadableException $e) {
            $this->returnJson(array('error' => $e->getUserMessage()));
        }
    }

    /**
     * Get the data for the file in the response and allow user to download it
     */
    public function getFile()
    {

        $variableUri = $_POST["variableUri"];

        $delivery = new \core_kernel_classes_Resource(tao_helpers_Uri::decode($this->getRequestParameter('deliveryUri')));
        try {
            $this->getResultStorage($delivery);

            $file = $this->getClassService()->getVariableFile($variableUri);
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
        } catch (\common_exception_Error $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Returns the currently configured result storage
     *
     * @param \core_kernel_classes_Resource $delivery
     * @return \taoResultServer_models_classes_ReadableResultStorage
     */
    protected function getResultStorage($delivery)
    {
        $resultServerService = $this->getServiceManager()->get(ResultServerService::SERVICE_ID);
        $resultStorage = $resultServerService->getResultStorage($delivery->getUri());
        $this->getClassService()->setImplementation($resultStorage);
        return $resultStorage;
    }

    /**
     * Extracts the result variables, with respect to the user's filter, and inject item states to allow preview with results
     *
     * @param string $resultId
     * @param string $filterSubmission
     * @param array $filterTypes
     * @return array
     */
    protected function getResultVariables($resultId, $filterSubmission, $filterTypes = array())
    {
        $resultService = $this->getClassService();
        $displayedVariables = $resultService->getStructuredVariables($resultId, $filterSubmission, $filterTypes);
        $resultVariables = $resultService->getStructuredVariables($resultId, $filterSubmission, [\taoResultServer_models_classes_ResponseVariable::class]);
        $responses = ResponseVariableFormatter::formatStructuredVariablesToItemState($resultVariables);
        $excludedVariables = array_flip(['numAttempts', 'duration']);

        foreach ($displayedVariables as &$item) {
            if (!isset($item['uri'])) {
                continue;
            }
            $itemUri = $item['uri'];
            if (isset($responses[$itemUri])) {
                $item['state'] = json_encode(array_diff_key($responses[$itemUri], $excludedVariables));
            } else {
                $item['state'] = null;
            }
        }

        return $displayedVariables;
    }

    /**
     * Get the list of active plugins for the list of results
     * @return PluginModule[] the list of plugins
     */
    public function getResultsListPlugin()
    {
        $serviceManager = $this->getServiceManager();

        /* @var ResultsPluginService $pluginService */
        $pluginService = $serviceManager->get(ResultsPluginService::SERVICE_ID);

        $event = new ResultsListPluginEvent($pluginService->getAllPlugins());
        $serviceManager->get(EventManager::SERVICE_ID)->trigger($event);

        // return the list of active plugins
        return array_filter($event->getPlugins(), function ($plugin) {
            return !is_null($plugin) && $plugin->isActive();
        });
    }
}
