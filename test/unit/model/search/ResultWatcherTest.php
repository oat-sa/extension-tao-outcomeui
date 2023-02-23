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
 * Copyright (c) 2021  (original work) Open Assessment Technologies SA;
 */

namespace oat\taoOutcomeUi\unit\model\search;

use core_kernel_classes_Resource;
use oat\generis\test\ServiceManagerMockTrait;
use oat\oatbox\log\LoggerService;
use oat\oatbox\user\User;
use oat\oatbox\user\UserService;
use oat\tao\model\AdvancedSearch\AdvancedSearchChecker;
use oat\tao\model\search\SearchProxy;
use oat\tao\model\taskQueue\QueueDispatcherInterface;
use oat\taoDelivery\model\execution\DeliveryExecutionInterface;
use oat\taoDelivery\models\classes\execution\event\DeliveryExecutionCreated;
use oat\taoOutcomeUi\model\search\ResultCustomFieldsService;
use oat\taoOutcomeUi\model\search\ResultsWatcher;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ResultWatcherTest extends TestCase
{
    use ServiceManagerMockTrait;

    /**
     * @var ResultsWatcher
     */
    private $subject;

    /**
     * @var AdvancedSearchChecker|MockObject
     */
    private $advancedSearchChecker;

    /**
     * @var QueueDispatcherInterface|MockObject
     */
    private $queueDispatcher;

    /**
     * @var SearchProxy|MockObject
     */
    private $searchService;

    protected function setUp(): void
    {
        $this->subject = new ResultsWatcher();
        $this->advancedSearchChecker = $this->createMock(AdvancedSearchChecker::class);
        $this->searchService = $this->createMock(SearchProxy::class);
        $this->queueDispatcher = $this->createMock(QueueDispatcherInterface::class);

        $userServiceMock = $this->createMock(UserService::class);
        $userServiceMock->method('getUser')->willReturn($this->createMock(User::class));

        $customFieldMock = $this->createMock(ResultCustomFieldsService::class);
        $customFieldMock->method('getCustomFields')->willReturn([]);

        $this->subject->setServiceLocator(
            $this->getServiceManagerMock(
                [
                    AdvancedSearchChecker::class => $this->advancedSearchChecker,
                    SearchProxy::SERVICE_ID => $this->searchService,
                    QueueDispatcherInterface::SERVICE_ID => $this->queueDispatcher,
                    UserService::SERVICE_ID => $userServiceMock,
                    ResultCustomFieldsService::SERVICE_ID => $customFieldMock,
                    LoggerService::SERVICE_ID => $this->createMock(LoggerService::class),
                ]
            )
        );
    }

    public function testCatchCreatedDeliveryExecutionEvent(): void
    {
        $event = $this->createMock(DeliveryExecutionCreated::class);
        $deliveryExecutionMock = $this->createMock(DeliveryExecutionInterface::class);
        $deliveryMock = $this->createMock(core_kernel_classes_Resource::class);

        $deliveryExecutionMock->method('getDelivery')->willReturn($deliveryMock);
        $deliveryExecutionMock->method('getStartTime')->willReturn(microtime());

        $event->method('getDeliveryExecution')->willReturn($deliveryExecutionMock);

        $this->advancedSearchChecker->method('isEnabled')->willReturn(true);
        $this->searchService->method('supportCustomIndex')->willReturn(true);
        $this->queueDispatcher->expects($this->once())->method('createTask');

        $this->subject->catchCreatedDeliveryExecutionEvent($event);
    }

    public function testCatchCreatedDeliveryExecutionEventNonQueued(): void
    {
        $event = $this->createMock(DeliveryExecutionCreated::class);
        $deliveryExecutionMock = $this->getMockForAbstractClass(DeliveryExecutionInterface::class);
        $event->method('getDeliveryExecution')->willReturn($deliveryExecutionMock);

        $this->advancedSearchChecker->method('isEnabled')->willReturn(false);
        $this->searchService->method('supportCustomIndex')->willReturn(true);
        $this->queueDispatcher->expects($this->never())->method('createTask');
        $this->subject->catchCreatedDeliveryExecutionEvent($event);
    }
}
