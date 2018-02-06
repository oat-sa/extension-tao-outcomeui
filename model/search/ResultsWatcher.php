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
 *
 */
namespace oat\taoOutcomeUi\model\search;

use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\search\Search;
use oat\tao\model\search\tasks\AddSearchIndexFromArray;
use oat\taoDelivery\model\execution\DeliveryExecutionInterface;
use oat\taoDelivery\models\classes\execution\event\DeliveryExecutionCreated;
use oat\taoResultServer\models\classes\ResultService;
use oat\taoTaskQueue\model\QueueDispatcher;

/**
 * Class ResultsWatcher
 * @package oat\taoOutcomeUi\model\search
 */
class ResultsWatcher extends ConfigurableService
{
    use OntologyAwareTrait;
    const SERVICE_ID = 'taoOutcomeUi/ResultsWatcher';
    const INDEX_DELIVERY = 'delivery';
    const INDEX_TEST_TAKER = 'test_taker';
    /**
     * @param DeliveryExecutionCreated $event
     * @return \common_report_Report
     * @throws \common_exception_NotFound
     */
    public function catchCreatedDeliveryExecutionEvent(DeliveryExecutionCreated $event)
    {
        /** @var DeliveryExecutionInterface $resource */
        $deliveryExecution = $event->getDeliveryExecution();
        /** @var Search $searchService */
        $report = \common_report_Report::createSuccess();
        $searchService = $this->getServiceLocator()->get(Search::SERVICE_ID);
        if ($searchService->supportCustomIndex()) {
            $body = [
                'label' => $deliveryExecution->getLabel(),
                self::INDEX_DELIVERY => $deliveryExecution->getDelivery()->getUri(),
                'type' => ResultService::DELIVERY_RESULT_CLASS_URI,
                self::INDEX_TEST_TAKER => $deliveryExecution->getUserIdentifier()
            ];
            $id = $deliveryExecution->getIdentifier();
            $queueDispatcher = $this->getServiceLocator()->get(QueueDispatcher::SERVICE_ID);
            $queueDispatcher->setOwner('Index');
            $queueDispatcher->createTask(new AddSearchIndexFromArray(), [$id, $body], __('Adding/Updating search index for %s', $deliveryExecution->getLabel()));
        }

        return $report;
    }

}