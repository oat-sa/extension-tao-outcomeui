<?php

namespace oat\taoOutcomeUi\model\search;

use DateTimeImmutable;
use oat\oatbox\service\ConfigurableService;
use oat\tao\helpers\UserHelper;
use oat\tao\model\search\Search;
use oat\tao\model\search\tasks\AddSearchIndexFromArray;
use oat\tao\model\taskQueue\QueueDispatcherInterface;
use oat\taoDelivery\model\execution\DeliveryExecutionInterface;
use oat\taoResultServer\models\classes\ResultService;

class ResultIndexer extends ConfigurableService
{
    public const INDEX_DELIVERY = 'delivery';
    public const INDEX_TEST_TAKER = 'test_taker';
    public const INDEX_TEST_TAKER_NAME = 'test_taker_name';
    public const INDEX_TEST_TAKER_LABEL = 'test_taker_label';
    public const INDEX_DELIVERY_EXECUTION = 'delivery_execution';
    public const INDEX_DELIVERY_EXECUTION_START_TIME = 'delivery_execution_start_time';
    public const INDEX_TEST_TAKER_LAST_NAME = 'test_taker_last_name';
    public const INDEX_TEST_TAKER_FIRST_NAME = 'test_taker_first_name';

    public function addIndex(DeliveryExecutionInterface $deliveryExecution)
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