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
use oat\tao\helpers\UserHelper;
use oat\tao\model\search\Search;
use oat\tao\model\search\tasks\AddSearchIndexFromArray;
use oat\tao\model\taskQueue\QueueDispatcherInterface;
use oat\taoDelivery\model\execution\DeliveryExecutionInterface;
use oat\taoDelivery\models\classes\execution\event\DeliveryExecutionCreated;
use oat\taoResultServer\models\classes\ResultService;
use oat\taoTests\models\event\TestChangedEvent;
use oat\taoDelivery\model\execution\ServiceProxy;

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
    const INDEX_TEST_TAKER_NAME = 'test_taker_name';
    const INDEX_DELIVERY_EXECUTION = 'delivery_execution';
    const OPTION_RESULT_SEARCH_FIELD_VISIBILITY = 'option_result_search_field_visibility';

    /**
     * @param DeliveryExecutionCreated $event
     * @return \common_report_Report
     * @throws \common_exception_NotFound
     */
    public function catchCreatedDeliveryExecutionEvent(DeliveryExecutionCreated $event)
    {
        /** @var DeliveryExecutionInterface $resource */
        $deliveryExecution = $event->getDeliveryExecution();
        return $this->addIndex($deliveryExecution);
    }

    /**
     * @param TestChangedEvent $event
     * @return \common_report_Report
     * @throws \common_exception_NotFound
     */
    public function catchTestChangedEvent(TestChangedEvent $event)
    {
        $sessionMemento = $event->getSessionMemento();
        $session = $event->getSession();
        if ($sessionMemento && $session->getState() !== $sessionMemento->getState()) {
            $deliveryExecution = ServiceProxy::singleton()->getDeliveryExecution($event->getServiceCallId());
            return $this->addIndex($deliveryExecution);
        }
    }

    /**
     * @param DeliveryExecutionInterface $deliveryExecution
     * @return \common_report_Report
     * @throws \common_exception_NotFound
     */
    protected function addIndex(DeliveryExecutionInterface $deliveryExecution)
    {
        /** @var Search $searchService */
        $report = \common_report_Report::createSuccess();
        $searchService = $this->getServiceLocator()->get(Search::SERVICE_ID);
        if ($searchService->supportCustomIndex()) {
            $id = $deliveryExecution->getIdentifier();
            $user = UserHelper::getUser($deliveryExecution->getUserIdentifier());
            $customFieldService = $this->getServiceLocator()->get(ResultCustomFieldsService::SERVICE_ID);
            $customBody = $customFieldService->getCustomFields($deliveryExecution);
            $userName = UserHelper::getUserName($user, true);
            $body = [
                'label' => $deliveryExecution->getLabel(),
                self::INDEX_DELIVERY => $deliveryExecution->getDelivery()->getUri(),
                'type' => ResultService::DELIVERY_RESULT_CLASS_URI,
                self::INDEX_TEST_TAKER => $user->getIdentifier(),
                self::INDEX_TEST_TAKER_NAME => $userName,
                self::INDEX_DELIVERY_EXECUTION => $id,
            ];
            $body = array_merge($body, $customBody);
            $queueDispatcher = $this->getServiceLocator()->get(QueueDispatcherInterface::SERVICE_ID);
            $queueDispatcher->setOwner('Index');
            $queueDispatcher->createTask(new AddSearchIndexFromArray(), [$id, $body], __('Adding/Updating search index for %s', $deliveryExecution->getLabel()));
        }
        return $report;
    }

    /**
     * Control filtering visibility
     * @return boolean
     */
    public function isResultSearchEnabled()
    {
        return (boolean)$this->getOption(self::OPTION_RESULT_SEARCH_FIELD_VISIBILITY);
    }

}