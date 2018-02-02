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
 * Copyright (c) 2018 (original work) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 *
 *
 */
namespace oat\taoOutcomeUi\model\table;

use oat\oatbox\service\ServiceManager;
use oat\tao\helpers\UserHelper;
use oat\tao\model\datatable\DatatablePayload;
use oat\tao\model\datatable\implementation\DatatableRequest;
use oat\tao\model\search\index\IndexDocument;
use oat\tao\model\search\SearchService;
use oat\taoDelivery\model\execution\ServiceProxy;
use oat\taoDeliveryRdf\model\DeliveryAssemblyService;
use oat\taoProctoring\model\execution\DeliveryExecution;
use oat\taoResultServer\models\classes\ResultServerService;
use oat\taoResultServer\models\classes\ResultService;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Class ResultsMonitoringDatatable
 * @package oat\taoOutcomeUi\model\table
 */
class ResultsMonitoringDatatable implements DatatablePayload, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;
    protected $request;
    protected $resultsImplementation;
    protected $results;

    /**
     * ResultsMonitoringDatatable constructor.
     * @param null $serviceLocator
     * @throws \common_exception_Error
     */
    public function __construct($serviceLocator = null)
    {
        if ($serviceLocator) {
            $this->setServiceLocator($serviceLocator);
        }

        $request = DatatableRequest::fromGlobals();
        $this->request = $request;
        /** @var ResultServerService $resultService */
        $resultService = $this->getServiceLocator()->get(ResultServerService::SERVICE_ID);
        $this->resultsImplementation = $resultService->getResultStorage('');
        $this->results = [];
    }

    /**
     * @return array
     * @throws \common_Exception
     * @throws \common_exception_Error
     */
    public function getPayload()
    {
        $this->results = [
            'data' => [],
            'records' => 0
        ];
        $page = $this->request->getPage();
        $limit = $this->request->getRows();
        $order = $this->request->getSortBy();
        $sort = $this->request->getSortOrder();
        $start = $limit * $page - $limit;

        $params = \Context::getInstance()->getRequest()->getParameters();
        $criteria = isset($params['filterquery']) ? $params['filterquery'] : '';
        $classUri = isset($params['classUri']) ? $params['classUri'] : '';

        $deliveryService = DeliveryAssemblyService::singleton();
        $deliveryClass = $deliveryService->getRootClass();
        $deliveriesArray = [];

        if ($criteria) {
            $searchService = SearchService::getSearchImplementation();
            $class = new \core_kernel_classes_Class(ResultService::DELIVERY_RESULT_CLASS_URI);
            $resultsArray = $searchService->query($criteria, $class);
            /** @var IndexDocument $index */
            foreach ($resultsArray as $index) {

                /** @var DeliveryExecution $execution */
                $execution = ServiceProxy::singleton()->getDeliveryExecution($index);
                try {
                    $delivery = $execution->getDelivery();
                    if ($classUri && $delivery->getUri() != $classUri) {
                        break;
                    }
                    $user = UserHelper::getUser($execution->getUserIdentifier());
                    $userName = UserHelper::getUserName($user, true);
                    if (empty($userName)) {
                        $userName = $execution->getUserIdentifier();
                    }
                    try {
                        $startTime = \tao_helpers_Date::displayeDate($execution->getStartTime());
                    } catch (\common_exception_NotFound $e) {
                        \common_Logger::w($e->getMessage());
                        $startTime = '';
                    }
                    $this->results['data'][] = [
                        'id' => $execution->getIdentifier().'|'.$delivery->getUri(),
                        'delivery' => $delivery->getLabel(),
                        'testTakerIdentifier' => $userName,
                        'deliveryResultIdentifier' => $execution->getIdentifier(),
                        'start_time' => $startTime
                    ];
                } catch (\common_exception_NotFound $e) {
                    $gau = array(
                        'order' => $order,
                        'orderdir' => strtoupper($sort),
                        'offset' => $start,
                        'limit' => $limit,
                        'recursive' => true
                    );
                    if ($classUri && $execution->getIdentifier() != $classUri) {
                        break;
                    }
                    $this->getResultsByDeliveries([$execution->getIdentifier()], $gau);
                }
            }
            $this->results['records'] = $resultsArray->getTotalCount();
        } else {
            $deliveries = $deliveryClass->getInstances(true);
            /** @var \core_kernel_classes_Resource $delivery */
            foreach ($deliveries as $delivery) {
                $deliveriesArray[] = $delivery->getUri();
            }
            $gau = array(
                'order' => $order,
                'orderdir' => strtoupper($sort),
                'offset' => $start,
                'limit' => $limit,
                'recursive' => true
            );
            if ($deliveriesArray) {
                $this->getResultsByDeliveries($deliveriesArray, $gau);
            }
        }
        return $this->doPostprocessing();
    }
    /**
     * @param $deliveriesArray
     * @param $options
     * @return mixed
     * @throws \common_Exception
     */
    protected function getResultsByDeliveries($deliveriesArray, $options = [])
    {
        foreach($this->resultsImplementation->getResultByDelivery($deliveriesArray, $options) as $result){
            $id = isset($result['deliveryResultIdentifier']) ? $result['deliveryResultIdentifier'] : null;
            if ($id) {
                $deliveryResource = new \core_kernel_classes_Resource($result['deliveryIdentifier']);
                $label = '';
                if ($deliveryResource) {
                    $label = $deliveryResource->getLabel();
                }
                $deliveryExecution = ServiceProxy::singleton()->getDeliveryExecution($id);
                try {
                    $startTime = \tao_helpers_Date::displayeDate($deliveryExecution->getStartTime());
                } catch (\common_exception_NotFound $e) {
                    \common_Logger::w($e->getMessage());
                    $startTime = '';
                }
                $user = UserHelper::getUser($result['testTakerIdentifier']);
                $userName = UserHelper::getUserName($user, true);
                if (empty($userName)) {
                    $userName = $result['testTakerIdentifier'];
                }
                $this->results['data'][] = [
                    'id' => $id.'|'.$result['deliveryIdentifier'],
                    'delivery' => $label,
                    'testTakerIdentifier' => $userName,
                    'deliveryResultIdentifier' => $id,
                    'start_time' => $startTime
                ];
            }
        }
        $this->results['records'] = $this->resultsImplementation->countResultByDelivery($deliveriesArray);
    }
    /**
     * @param array $results
     * @return array
     */
    protected function doPostProcessing()
    {
        $payload = [
            'data' => $this->results['data'],
            'page' => (integer) $this->request->getPage(),
            'records' => (integer) count($this->results['data']),
            'total' => ceil($this->results['records'] / $this->request->getRows()),
        ];
        return $payload;
    }
    /**
     * @return array
     * @throws \common_Exception
     * @throws \common_exception_Error
     */
    public function jsonSerialize()
    {
        return $this->getPayload();
    }
}