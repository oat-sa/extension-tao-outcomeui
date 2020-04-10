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

use oat\tao\helpers\UserHelper;
use oat\tao\model\datatable\DatatablePayload;
use oat\tao\model\search\index\IndexDocument;
use oat\tao\model\search\Search;
use oat\taoDelivery\model\execution\ServiceProxy;
use oat\taoDeliveryRdf\model\DeliveryAssemblyService;
use oat\taoDelivery\model\execution\DeliveryExecutionInterface;
use oat\taoResultServer\models\classes\ResultManagement;
use oat\taoResultServer\models\classes\ResultServerService;
use oat\taoResultServer\models\classes\ResultService;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use oat\tao\model\datatable\DatatableRequest;

/**
 * Class ResultsMonitoringDatatable
 * @package oat\taoOutcomeUi\model\table
 */
class ResultsMonitoringDatatable implements DatatablePayload, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    protected $request;

    protected $results = [];

    /**
     * ResultsMonitoringDatatable constructor.
     * @param DatatableRequest $request
     */
    public function __construct(DatatableRequest $request)
    {
        $this->request = $request;
        $this->results = [];
    }

    /**
     * @throws \common_Exception
     * @throws \common_exception_NotFound
     *
     * @return array
     */
    public function getPayload()
    {
        $this->results = [
            'data' => [],
            'records' => 0,
        ];

        $page = $this->request->getPage();
        $limit = $this->request->getRows();
        $start = $limit * $page - $limit;

        $options = [
            'order' => $this->request->getSortBy(),
            'orderdir' => strtoupper($this->request->getSortOrder()),
            'offset' => $start,
            'limit' => $limit,
            'recursive' => true,
        ];

        $params = \Context::getInstance()->getRequest()->getParameters();
        $criteria = isset($params['filterquery']) ? $params['filterquery'] : '';
        $classUri = isset($params['classUri']) ? $params['classUri'] : '';

        $deliveriesArray = [];

        if ($criteria) {
            /** @var Search $searchService */
            $searchService = $this->getServiceLocator()->get(Search::SERVICE_ID);

            if ($classUri) {
                $criteria .= ' AND delivery:"' . $classUri . '"';
            }

            $resultsArray = $searchService->query($criteria, ResultService::DELIVERY_RESULT_CLASS_URI, $start, $limit);

            /** @var IndexDocument $index */
            foreach ($resultsArray as $index) {
                /** @var DeliveryExecutionInterface $execution */
                $execution = ServiceProxy::singleton()->getDeliveryExecution($index);

                try {
                    $delivery = $execution->getDelivery();

                    if ($classUri && $delivery->getUri() !== $classUri) {
                        break;
                    }

                    try {
                        $startTime = \tao_helpers_Date::displayeDate($execution->getStartTime());
                    } catch (\common_exception_NotFound $e) {
                        \common_Logger::w($e->getMessage());
                        $startTime = '';
                    }

                    $user = UserHelper::getUser($execution->getUserIdentifier());
                    $userName = UserHelper::getUserName($user, true);

                    $this->results['data'][] = [
                        'id' => $execution->getIdentifier() . '|' . $delivery->getUri(),
                        'delivery' => $execution->getLabel() ? $execution->getLabel() : $delivery->getLabel(),
                        'userName' => !empty($userName) ? $userName : $execution->getUserIdentifier(),
                        'testTakerIdentifier' => $execution->getUserIdentifier(),
                        'deliveryResultIdentifier' => $execution->getIdentifier(),
                        'start_time' => $startTime,
                    ];
                } catch (\common_exception_NotFound $e) {
                    if ($classUri && $execution->getIdentifier() !== $classUri) {
                        break;
                    }

                    $this->getResultsByDeliveries([$execution->getIdentifier()], $options);
                }
            }

            $this->results['records'] = $resultsArray->getTotalCount();
        } else {
            /** @var \core_kernel_classes_Resource $delivery */
            foreach (DeliveryAssemblyService::singleton()->getRootClass()->getInstances(true) as $delivery) {
                $deliveriesArray[] = $delivery->getUri();
            }

            if ($deliveriesArray) {
                $this->getResultsByDeliveries($deliveriesArray, $options);
            }
        }

        return $this->doPostprocessing();
    }

    /**
     * @param $deliveriesArray
     * @param array $options
     *
     * @throws \common_Exception
     * @throws \common_exception_Error
     */
    protected function getResultsByDeliveries($deliveriesArray, $options = [])
    {
        /** @var ResultServerService $resultService */
        $resultService = $this->getServiceLocator()->get(ResultServerService::SERVICE_ID);
        $resultsImplementation = $resultService->getResultStorage(null);

        if ($resultsImplementation instanceof ResultManagement) {
            foreach ($resultsImplementation->getResultByDelivery($deliveriesArray, $options) as $result) {
                $id = isset($result['deliveryResultIdentifier']) ? $result['deliveryResultIdentifier'] : null;
                if ($id) {
                    $deliveryExecution = ServiceProxy::singleton()->getDeliveryExecution($id);
                    try {
                        $startTime = \tao_helpers_Date::displayeDate($deliveryExecution->getStartTime());
                    } catch (\common_exception_NotFound $e) {
                        \common_Logger::w($e->getMessage());
                        $startTime = '';
                    }
                    $label = '';
                    try {
                        $label = $deliveryExecution->getLabel();
                    } catch (\common_exception_NotFound $e) {
                        \common_Logger::w($e->getMessage());
                        if (isset($result['deliveryIdentifier'])) {
                            $deliveryResource = new \core_kernel_classes_Resource($result['deliveryIdentifier']);
                            if ($deliveryResource) {
                                $label = $deliveryResource->getLabel();
                            }
                        }
                    }

                    $testTakerId = $result['testTakerIdentifier'] ? $result['testTakerIdentifier'] : 'TestTaker';
                    $user = UserHelper::getUser($testTakerId);
                    $userName = UserHelper::getUserName($user, true);

                    $this->results['data'][] = [
                        'id' => $id . '|' . $result['deliveryIdentifier'],
                        'delivery' => $label,
                        'userName' => !empty($userName) ? $userName : $testTakerId,
                        'testTakerIdentifier' => $testTakerId,
                        'deliveryResultIdentifier' => $id,
                        'start_time' => $startTime,
                    ];
                }
            }

            $this->results['records'] = $resultsImplementation->countResultByDelivery($deliveriesArray);
        } else {
            \common_Logger::i('Attempt to read from non-manageable result storage');
        }
    }

    /**
     * @return array
     */
    protected function doPostProcessing()
    {
        $numberOfRecords = isset($this->results['records']['value'])
            ? $this->results['records']['value']
            : $this->results['records'];

        return [
            'data' => $this->results['data'],
            'page' => (int) $this->request->getPage(),
            'records' => (int) count($this->results['data']),
            'total' => ceil($numberOfRecords / $this->request->getRows()),
        ];
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
