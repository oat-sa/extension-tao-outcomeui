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
 * Copyright (c) 2018-2020 (original work) Open Assessment Technologies SA;
 */

namespace oat\taoOutcomeUi\model\search;

use DateTime;
use DateTimeImmutable;
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

    public const SERVICE_ID = 'taoOutcomeUi/ResultsWatcher';
    public const OPTION_RESULT_SEARCH_FIELD_VISIBILITY = 'option_result_search_field_visibility';

    public const INDEX_DELIVERY = 'delivery';
    public const INDEX_TEST_TAKER = 'test_taker';
    public const INDEX_TEST_TAKER_NAME = 'test_taker_name';
    public const INDEX_TEST_TAKER_LABEL = 'test_taker_label';
    public const INDEX_DELIVERY_EXECUTION = 'delivery_execution';
    public const INDEX_DELIVERY_EXECUTION_START_TIME = 'delivery_execution_start_time';
    public const INDEX_TEST_TAKER_LAST_NAME = 'test_taker_last_name';
    public const INDEX_TEST_TAKER_FIRST_NAME = 'test_taker_first_name';

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
            $deliveryExecutionId = $deliveryExecution->getIdentifier();
            $user = UserHelper::getUser($deliveryExecution->getUserIdentifier());
            $customFieldService = $this->getServiceLocator()->get(ResultCustomFieldsService::SERVICE_ID);
            $customBody = $customFieldService->getCustomFields($deliveryExecution);

            $body = [
                'label' => $deliveryExecution->getLabel(),
                self::INDEX_DELIVERY => $deliveryExecution->getDelivery()->getUri(),
                'type' => ResultService::DELIVERY_RESULT_CLASS_URI,
                self::INDEX_TEST_TAKER => $user->getIdentifier(),
                self::INDEX_TEST_TAKER_FIRST_NAME => UserHelper::getUserFirstName($user, true),
                self::INDEX_TEST_TAKER_LAST_NAME => UserHelper::getUserLastName($user, true),
                self::INDEX_TEST_TAKER_NAME => UserHelper::getUserName($user, true),
                self::INDEX_TEST_TAKER_LABEL => UserHelper::getUserLabel($user),
                self::INDEX_DELIVERY_EXECUTION => $deliveryExecutionId,
                self::INDEX_DELIVERY_EXECUTION_START_TIME =>  $this->transformDateTime(
                    $deliveryExecution->getStartTime()
                )
            ];
            $body = array_merge($body, $customBody);
            $queueDispatcher = $this->getServiceLocator()->get(QueueDispatcherInterface::SERVICE_ID);

            $queueDispatcher->createTask(
                new AddSearchIndexFromArray(),
                [$deliveryExecutionId, $body],
                __('Adding/Updating search index for %s', $deliveryExecution->getLabel())
            );
        }
        return $report;
    }

    /**
     * Control filtering visibility
     * @return boolean
     */
    public function isResultSearchEnabled()
    {
        return (bool)$this->getOption(self::OPTION_RESULT_SEARCH_FIELD_VISIBILITY);
    }

    private function transformDateTime(string $getStartTime): string
    {
        $timeArray = explode(" ", $getStartTime);
        $date = DateTimeImmutable::createFromFormat('U', $timeArray[1]);

        if ($date === false) {
            $date = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $getStartTime);
        }

        if (!$date instanceof DateTimeImmutable) {
            $this->logCritical(
                sprintf('We were not able to transform string: "%s" delivery-execution start time!', $getStartTime)
            );
            return '';
        }

        return $date->format('m/d/Y H:i:s');
    }
}
