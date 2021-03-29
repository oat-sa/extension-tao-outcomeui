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

use common_Exception;
use common_exception_NotFound;
use \Exception;
use \common_exception_BadRequest;
use oat\generis\model\GenerisRdf;
use oat\generis\model\OntologyAwareTrait;
use oat\generis\model\OntologyRdfs;
use oat\oatbox\event\EventManager;
use oat\tao\model\plugins\PluginModule;
use oat\tao\model\taskQueue\TaskLogActionTrait;
use oat\taoDelivery\model\execution\DeliveryExecutionInterface;
use oat\taoDelivery\model\execution\ServiceProxy;
use oat\taoOutcomeUi\helper\ResponseVariableFormatter;
use oat\taoOutcomeUi\model\event\ResultsListPluginEvent;
use oat\taoOutcomeUi\model\export\DeliveryCsvResultsExporterFactory;
use oat\taoOutcomeUi\model\export\DeliveryResultsExporterFactoryInterface;
use oat\taoOutcomeUi\model\export\DeliverySqlResultsExporterFactory;
use oat\taoOutcomeUi\model\export\ResultsExporter;
use oat\taoOutcomeUi\model\plugins\ResultsPluginService;
use oat\taoOutcomeUi\model\search\ResultsWatcher;
use oat\taoOutcomeUi\model\table\ResultsMonitoringDatatable;
use oat\taoOutcomeUi\model\Wrapper\ResultServiceWrapper;
use oat\taoResultServer\models\classes\NoResultStorage;
use oat\taoResultServer\models\classes\NoResultStorageException;
use oat\taoResultServer\models\classes\QtiResultsService;
use oat\taoResultServer\models\Formatter\ItemResponseCollectionNormalizer;
use \tao_helpers_Uri;
use oat\taoOutcomeUi\model\ResultsService;
use oat\taoDeliveryRdf\model\DeliveryAssemblyService;
use oat\taoResultServer\models\classes\ResultServerService;
use oat\tao\helpers\UserHelper;
use oat\tao\model\datatable\implementation\DatatableRequest;

/**
 * Results Controller provide actions performed from url resolution
 *
 *
 * @author Patrick Plichart <patrick@taotesting.com>
 * @author Bertrand Chevrier, <taosupport@tudor.lu>
 * @package taoOutcomeUi
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 */
class Results extends \tao_actions_CommonModule
{
    use TaskLogActionTrait;
    use OntologyAwareTrait;

    const PARAMETER_DELIVERY_URI = 'uri';
    const PARAMETER_DELIVERY_CLASS_URI = 'classUri';

    /**
     * @return ResultsService
     */
    protected function getResultsService()
    {
        return $this->getServiceLocator()->get(ResultServiceWrapper::SERVICE_ID)->getService();
    }

    /**
     * @return object|ServiceProxy
     */
    protected function getServiceProxy()
    {
        return $this->getServiceLocator()->get(ServiceProxy::SERVICE_ID);
    }

    /**
     * @return DeliveryAssemblyService
     */
    protected function getDeliveryAssemblyService()
    {
        return $this->getServiceLocator()->get(DeliveryAssemblyService::class);
    }

    /**
     * Action called on click on a delivery (class) construct and call the view to see the table of
     * all delivery execution for a specific delivery
     */
    public function index()
    {
        $this->defaultData();

        // if delivery class has been selected, return nothing
        if (!$this->hasRequestParameter(self::PARAMETER_DELIVERY_URI)) {
            return;
        }

        $model = [
            [
                'id' => 'ttakerid',
                'label' => __('Test Taker ID'),
                'sortable' => false
            ],
            [
                'id' => 'ttaker',
                'label' => __('Test Taker'),
                'sortable' => false
            ],
            [
                'id' => 'time',
                'label' => __('Start Time'),
                'sortable' => false
            ]
        ];

        $deliveryService = DeliveryAssemblyService::singleton();
        $delivery = $this->getResource($this->getRequestParameter('id'));
        if ($delivery->getUri() !== $deliveryService->getRootClass()->getUri()) {
            try {
                // display delivery
                $this->getResultStorage($delivery);

                $this->setData('uri', $delivery->getUri());
                $this->setData('title', $delivery->getLabel());
                $this->setData('config', [
                    'dataModel' => $model,
                    'plugins' => $this->getResultsListPlugin(),
                    'searchable' => $this->getServiceLocator()->get(ResultsWatcher::SERVICE_ID)->isResultSearchEnabled()
                ]);

                if ($this->hasRequestParameter('export-callback-url')) {
                    $this->setData('export-callback-url', $this->getRequestParameter('export-callback-url'));
                }
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
     * Get all result delivery execution to display
     */
    public function getResults()
    {
        $limit = $this->getRequestParameter('rows');
        $start = $limit * $this->getRequestParameter('page') - $limit;

        try {
            $data = [];
            $readOnly = [];
            $rights = [
                'view' => !$this->hasAccess('oat\taoOutcomeUi\controller\Results', 'viewResult', []),
                'delete' => !$this->hasAccess('oat\taoOutcomeUi\controller\Results', 'delete', []),
            ];

            if ($this->hasRequestParameter('filterquery')) {
                $resultsData = new ResultsMonitoringDatatable(DatatableRequest::fromGlobals());
                $resultsData->setServiceLocator($this->getServiceLocator());

                $payload = $resultsData->getPayload();
                $results = $payload['data'];
                $count = $payload['records'];
            } else {
                $delivery = new \core_kernel_classes_Resource(tao_helpers_Uri::decode($this->getRequestParameter('classUri')));
                $this->getResultStorage($delivery);

                $results = $this->getResultsService()->getImplementation()->getResultByDelivery([$delivery->getUri()], [
                    'order' => $this->getRequestParameter('sortby'),
                    'orderdir' => strtoupper($this->getRequestParameter('sortorder')),
                    'offset' => $start,
                    'limit' => $limit,
                    'recursive' => true,
                ]);
                $count = $this->getResultsService()->getImplementation()->countResultByDelivery([$delivery->getUri()]);
            }

            foreach ($results as $res) {
                $deliveryExecution = $this->getServiceProxy()->getDeliveryExecution($res['deliveryResultIdentifier']);

                try {
                    $startTime = \tao_helpers_Date::displayeDate($deliveryExecution->getStartTime());
                } catch (common_exception_NotFound $e) {
                    $this->logWarning($e->getMessage());
                    $startTime = '';
                }

                $user = UserHelper::getUser($res['testTakerIdentifier']);

                $data[] = [
                    'id' => $deliveryExecution->getIdentifier(),
                    'ttakerid' => $res['testTakerIdentifier'],
                    'ttaker' => _dh(UserHelper::getUserName($user, true)),
                    'time' => $startTime,
                ];

                $readOnly[$deliveryExecution->getIdentifier()] = $rights;
            }

            $this->returnJson([
                'data' => $data,
                'page' => floor($start / $limit) + 1,
                'total' => ceil($count / $limit),
                'records' => count($data),
                'readonly' => $readOnly,
            ]);
        } catch (\common_exception_Error $e) {
            $this->returnJson([
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Delete a result or a result class
     * @throws Exception
     * @throws common_exception_BadRequest
     * @return string json {'deleted' : true}
     */
    public function delete()
    {
        if (!$this->isXmlHttpRequest()) {
            throw new common_exception_BadRequest('wrong request mode');
        }
        $deliveryExecutionUri = tao_helpers_Uri::decode($this->getRequestParameter('uri'));
        $de = $this->getServiceProxy()->getDeliveryExecution($deliveryExecutionUri);

        try {
            $this->getResultStorage($de->getDelivery());

            $deleted = $this->getResultsService()->deleteResult($deliveryExecutionUri);

            $this->returnJson(['deleted' => $deleted]);
        } catch (\common_exception_Error $e) {
            $this->returnJson(['error' => $e->getMessage()]);
        }
    }

    /**
     * Is the given delivery execution aka. result cacheable?
     *
     * @param string $resultIdentifier
     * @return bool
     * @throws common_exception_NotFound
     */
    private function isCacheable($resultIdentifier)
    {
        return $this->getServiceProxy()->getDeliveryExecution($resultIdentifier)->getState()->getUri() == DeliveryExecutionInterface::STATE_FINISHIED;
    }

    /**
     * Get info on the current Result and display it
     */
    public function viewResult()
    {
        $this->defaultData();

        $resultId = $this->getRawParameter('id');
        $delivery = $this->getResource($this->getRequestParameter('classUri'));

        try {
            $this->getResultStorage($delivery);

            $testTaker = $this->getResultsService()->getTestTakerData($resultId);

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
                $login = (count($testTaker[GenerisRdf::PROPERTY_USER_LOGIN]) > 0) ? current(
                    $testTaker[GenerisRdf::PROPERTY_USER_LOGIN]
                )->literal : "";
                $label = (count($testTaker[OntologyRdfs::RDFS_LABEL]) > 0) ? current($testTaker[OntologyRdfs::RDFS_LABEL])->literal : "";
                $firstName = (count($testTaker[GenerisRdf::PROPERTY_USER_FIRSTNAME]) > 0) ? current(
                    $testTaker[GenerisRdf::PROPERTY_USER_FIRSTNAME]
                )->literal : "";
                $userLastName = (count($testTaker[GenerisRdf::PROPERTY_USER_LASTNAME]) > 0) ? current(
                    $testTaker[GenerisRdf::PROPERTY_USER_LASTNAME]
                )->literal : "";
                $userEmail = (count($testTaker[GenerisRdf::PROPERTY_USER_MAIL]) > 0) ? current(
                    $testTaker[GenerisRdf::PROPERTY_USER_MAIL]
                )->literal : "";

                $this->setData('userLogin', $login);
                $this->setData('userLabel', $label);
                $this->setData('userFirstName', $firstName);
                $this->setData('userLastName', $userLastName);
                $this->setData('userEmail', $userEmail);
            }
            $filterSubmission = ($this->hasRequestParameter("filterSubmission")) ? $this->getRequestParameter("filterSubmission") : ResultsService::VARIABLES_FILTER_LAST_SUBMITTED;
            $filterTypes = ($this->hasRequestParameter("filterTypes")) ? $this->getRequestParameter("filterTypes") : [\taoResultServer_models_classes_ResponseVariable::class, \taoResultServer_models_classes_OutcomeVariable::class, \taoResultServer_models_classes_TraceVariable::class];

            // check the result page cache; if we have hit than return the gzencoded string and let the client to encode the data
            $cacheKey = $this->getResultsService()->getCacheKey($resultId, md5($filterSubmission . implode(',', $filterTypes)));
            if (
                $this->isCacheable($resultId)
                && $this->getResultsService()->getCache()
                && $this->getResultsService()->getCache()->exists($cacheKey)
            ) {
                $this->logDebug('Result page cache hit for "' . $cacheKey . '"');

                $gzipOutput = $this->getResultsService()->getCache()->get($cacheKey);

                header('Content-Encoding: gzip');
                header('Content-Length: ' . strlen($gzipOutput));

                echo $gzipOutput;
                exit;
            }

            $variables = $this->getResultsService()->getImplementation()->getDeliveryVariables($resultId);
            $variables = $this->getNormalizer()->normalize($variables);

            $structuredItemVariables = $this->getResultsService()->structureItemVariables($variables, $filterSubmission);
            $itemVariables = $this->formatItemVariables($structuredItemVariables, $filterTypes);
            $testVariables = $this->getResultsService()->extractTestVariables($variables, $filterTypes, $filterSubmission);

            // render item variables
            $this->setData('variables', $itemVariables);
            $stats = $this->getResultsService()->calculateResponseStatistics($itemVariables);
            $this->setData('nbResponses', $stats["nbResponses"]);
            $this->setData('nbCorrectResponses', $stats["nbCorrectResponses"]);
            $this->setData('nbIncorrectResponses', $stats["nbIncorrectResponses"]);
            $this->setData('nbUnscoredResponses', $stats["nbUnscoredResponses"]);
            // render test variables
            $this->setData('deliveryVariables', $testVariables);

            $this->setData('itemType', $this->getResultsService()->getDeliveryItemType($resultId));
            $this->setData('id', $resultId);
            $this->setData('classUri', $delivery->getUri());
            $this->setData('filterSubmission', $filterSubmission);
            $this->setData('filterTypes', $filterTypes);
            $this->setView('viewResult.tpl');

            // quick hack to gain performance: caching the entire result page if it is cacheable
            // "gzencode" is used to reduce the size of the string to be cached
            ob_start(function ($buffer) use ($resultId, $cacheKey) {
                if (
                    $this->isCacheable($resultId)
                    && $this->getResultsService()->setCacheValue($resultId, $cacheKey, gzencode($buffer, 9))
                ) {
                    \common_Logger::d('Result page cache set for "' . $cacheKey . '"');
                }

                return $buffer;
            });
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
     * @throws common_exception_NotFound
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
            $this->returnJson(['error' => $e->getUserMessage()]);
        }
    }

    /**
     * Get the data for the file in the response and allow user to download it
     */
    public function getFile()
    {
        $variableUri = $_POST["variableUri"];

        $delivery = $this->getResource(tao_helpers_Uri::decode($this->getRequestParameter('deliveryUri')));
        try {
            $this->getResultStorage($delivery);

            $file = $this->getResultsService()->getVariableFile($variableUri);
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
     * Get the data for the file in the response as a variable data
     */
    public function getVariableFile()
    {
        $delivery = $this->getResource(tao_helpers_Uri::decode($this->getRequestParameter('deliveryUri')));
        $variableUri = $this->getResource(tao_helpers_Uri::decode($this->getRequestParameter('variableUri')));
        try {
            $this->getResultStorage($delivery);

            $file = $this->getResultsService()->getVariableFile($variableUri);

            // weirdly, the mime type declaration can be expressed as a HTTP header notation
            $mime = trim(str_replace('content-type:', '', strtolower($file["mimetype"])));

            $this->returnJson(
                [
                    'success' => true,
                    'data' => base64_encode($file["data"]),
                    'name' => $file["filename"],
                    'mime' => $mime,
                ]
            );
        } catch (\common_exception_Error $e) {
            $this->returnJson(
                $this->getErrorResponse($e),
                $this->getStatusCode($e)
            );
        }
    }

    /**
     * Gets an error response object
     * @param Exception $e Exception from which extract the error context
     * @return array
     */
    protected function getErrorResponse(Exception $e): array
    {
        $this->logError($e->getMessage());

        $response = [
            'success' => false,
            'type' => 'error',
        ];
        if ($e instanceof Exception) {
            $response['type'] = 'exception';
            $response['code'] = $e->getCode();
        }
        if ($e instanceof \common_exception_UserReadableException) {
            $response['message'] = $e->getUserMessage();
        } else {
            $response['message'] = __('Internal server error!');
        }
        if ($e instanceof \common_exception_Unauthorized) {
            $response['code'] = 403;
        }
        return $response;
    }

    /**
     * Gets an HTTP response code
     * @param ?Exception [$e] Optional exception from which extract the error context
     * @return int
     */
    protected function getStatusCode(?Exception $e = null): int
    {
        $code = 200;
        if ($e) {
            $code = 500;

            switch (true) {
                case $e instanceof \common_exception_NotImplemented:
                case $e instanceof \common_exception_NoImplementation:
                    $code = 501;
                    break;

                case $e instanceof \common_exception_Unauthorized:
                    $code = 403;
                    break;

                case $e instanceof \tao_models_classes_FileNotFoundException:
                    $code = 404;
                    break;
            }
        }
        return $code;
    }

    /**
     * Returns the currently configured result storage
     *
     * @param \core_kernel_classes_Resource $delivery
     * @return \taoResultServer_models_classes_ReadableResultStorage
     */
    protected function getResultStorage($delivery)
    {
        /** @var ResultServerService $resultServerService */
        $resultServerService = $this->getServiceManager()->get(ResultServerService::SERVICE_ID);
        $resultStorage = $resultServerService->getResultStorage($delivery->getUri());
        if ($resultStorage instanceof NoResultStorage) {
            throw NoResultStorageException::create();
        }

        if (!$resultStorage instanceof \taoResultServer_models_classes_ReadableResultStorage) {
            throw new \common_exception_Error('The results storage it is not readable');
        }
        $this->getResultsService()->setImplementation($resultStorage);
        return $resultStorage;
    }

    /**
     * Regroup item variables by attempt
     * @param array $variables
     * @param array $filterTypes
     * @return array
     */
    protected function formatItemVariables($variables, $filterTypes)
    {
        $displayedVariables = $this->getResultsService()->filterStructuredVariables($variables, $filterTypes);
        $responses = ResponseVariableFormatter::formatStructuredVariablesToItemState($variables);
        $excludedVariables = array_flip(['numAttempts', 'duration']);

        foreach ($displayedVariables as &$item) {
            if (!isset($item['uri'])) {
                continue;
            }
            $itemUri = $item['uri'];
            $state = isset($responses[$itemUri][$item['attempt']])
                ? array_diff_key($responses[$itemUri][$item['attempt']], $excludedVariables)
                : [];
            $item['state'] = !empty($state) ? json_encode($state) : '{}';
        }

        return $displayedVariables;
    }

    /**
     * Get the list of active plugins for the list of results
     * @return PluginModule[] the list of plugins
     */
    public function getResultsListPlugin()
    {
        /* @var ResultsPluginService $pluginService */
        $pluginService = $this->getServiceLocator()->get(ResultsPluginService::SERVICE_ID);

        $event = new ResultsListPluginEvent($pluginService->getAllPlugins());
        $this->getServiceLocator()->get(EventManager::SERVICE_ID)->trigger($event);

        // return the list of active plugins
        return array_filter($event->getPlugins(), function ($plugin) {
            return !is_null($plugin) && $plugin->isActive();
        });
    }

    /**
     * @param array $options
     * @return array
     * @throws
     */
    protected function getTreeOptionsFromRequest($options = [])
    {
        $config = $this->getServiceManager()->get('taoDeliveryRdf/DeliveryMgmt')->getConfig();
        $options =  parent::getTreeOptionsFromRequest($options);
        $options['order'] = key($config['OntologyTreeOrder']);
        $options['orderdir'] = $config['OntologyTreeOrder'][$options['order']];
        if ($this->hasRequestParameter('classUri')) {
            $options['class'] = $this->getCurrentClass();
        } else {
            $options['class'] = $this->getDeliveryAssemblyService()->getRootClass();
        }
        return $options;
    }

    /**
     * Exports results by either a class or a single delivery in csv format.
     *
     * Only creating the export task.
     *
     * @throws Exception
     * @throws common_Exception
     */
    public function export()
    {
        $exporter = $this->getExporter(new DeliveryCsvResultsExporterFactory());
        return $this->returnTaskJson($exporter->createExportTask());
    }

    /**
     * Exports results by either a class or a single delivery in sql format.
     *
     * Only creating the export task.
     *
     * @throws Exception
     * @throws common_Exception
     */
    public function exportSql()
    {
        $exporter = $this->getExporter(new DeliverySqlResultsExporterFactory());
        return $this->returnTaskJson($exporter->createExportTask());
    }

    /**
     * @param DeliveryResultsExporterFactoryInterface $deliveryResultsExporterFactory
     * @return ResultsExporter
     * @throws common_Exception
     * @throws common_exception_NotFound
     */
    private function getExporter(DeliveryResultsExporterFactoryInterface $deliveryResultsExporterFactory)
    {
        if (!$this->isXmlHttpRequest()) {
            throw new \Exception('Only ajax call allowed.');
        }

        if (!$this->hasRequestParameter(self::PARAMETER_DELIVERY_CLASS_URI) && !$this->hasRequestParameter(self::PARAMETER_DELIVERY_URI)) {
            throw new common_Exception('Parameter "' . self::PARAMETER_DELIVERY_CLASS_URI . '" or "' . self::PARAMETER_DELIVERY_URI . '" missing');
        }

        $resourceUri = $this->hasRequestParameter(self::PARAMETER_DELIVERY_URI)
            ? \tao_helpers_Uri::decode($this->getRequestParameter(self::PARAMETER_DELIVERY_URI))
            : \tao_helpers_Uri::decode($this->getRequestParameter(self::PARAMETER_DELIVERY_CLASS_URI));

        /** @var ResultsExporter $exporter */
        $exporter = $this->propagate(new ResultsExporter($resourceUri, ResultsService::singleton(), $deliveryResultsExporterFactory));

        return $exporter;
    }

    private function getNormalizer(): ItemResponseCollectionNormalizer
    {
        return $this->getServiceLocator()->get(ItemResponseCollectionNormalizer::class);
    }

    /**
     * @return ResultsService
     */
    private function getResultService()
    {
        return $this->getServiceLocator()->get(ResultsService::SERVICE_ID);
    }
}
