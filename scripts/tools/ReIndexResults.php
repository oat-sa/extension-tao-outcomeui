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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA;
 */

namespace oat\taoOutcomeUi\scripts\tools;

use oat\oatbox\extension\AbstractAction;
use oat\tao\model\search\index\IndexDocument;
use oat\tao\model\search\SearchService;
use oat\taoDelivery\model\execution\ServiceProxy;
use oat\taoDeliveryRdf\model\DeliveryAssemblyService;
use oat\taoResultServer\models\classes\ResultServerService;
use oat\taoResultServer\models\classes\ResultService;

/**
 * ReIndex all results
 * Class ReIndexResults
 * @package oat\taoOutcomeUi\model
 */
class ReIndexResults extends AbstractAction
{
    /**
     * @param $params
     * @throws \common_exception_Error
     * @throws \common_exception_NotFound
     */
    public function __invoke($params)
    {
        $deliveryService = DeliveryAssemblyService::singleton();
        $deliveryClass = $deliveryService->getRootClass();
        /** @var ResultServerService $resultService */
        $resultService = $this->getServiceLocator()->get(ResultServerService::SERVICE_ID);
        $resultsImplementation = $resultService->getResultStorage('');
        $deliveries = $deliveryClass->getInstances(true);
        $deliveriesArray = [];
        /** @var \core_kernel_classes_Resource $delivery */
        foreach ($deliveries as $delivery) {
            $deliveriesArray[] = $delivery->getUri();
        }
        $options = array(
            'recursive' => true
        );
        if ($deliveriesArray) {
            foreach($resultsImplementation->getResultByDelivery($deliveriesArray, $options) as $result){
                $id = isset($result['deliveryResultIdentifier']) ? $result['deliveryResultIdentifier'] : null;
                if ($id) {
                    $deliveryExecution = ServiceProxy::singleton()->getDeliveryExecution($id);
                    $body = [
                        'label' => $deliveryExecution->getLabel()
                    ];
                    $document = new IndexDocument(
                        $deliveryExecution->getIdentifier(),
                        $deliveryExecution->getIdentifier(),
                        ResultService::DELIVERY_RESULT_CLASS_URI,
                        $body
                    );
                    SearchService::getSearchImplementation()->index($document);
                }
            }
        }
    }
}