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
 * Copyright (c) 2018 Open Assessment Technologies SA
 *
 */
namespace oat\taoOutcomeUi\scripts\install;

use oat\oatbox\event\EventManager;
use oat\oatbox\extension\InstallAction;
use oat\taoDelivery\models\classes\execution\event\DeliveryExecutionCreated;
use oat\taoOutcomeUi\model\search\ResultCustomFieldsService;
use oat\taoOutcomeUi\model\search\ResultsWatcher;
use oat\taoTests\models\event\TestChangedEvent;

/**
 * Class SetupSearchService
 * @package oat\taoResultServer\scripts\install
 */
class SetupSearchService extends InstallAction
{
    /**
     * @param $params
     * @throws \common_Exception
     * @throws \oat\oatbox\service\exception\InvalidServiceManagerException
     */
    public function __invoke($params)
    {
        $this->getServiceManager()->register(ResultCustomFieldsService::SERVICE_ID, new ResultCustomFieldsService());
        $this->getServiceManager()->register(ResultsWatcher::SERVICE_ID, new ResultsWatcher([ResultsWatcher::OPTION_RESULT_SEARCH_FIELD_VISIBILITY => false]));
        /** @var EventManager $eventManager */
        $eventManager = $this->getServiceLocator()->get(EventManager::SERVICE_ID);
        $eventManager->attach(DeliveryExecutionCreated::class, [ResultsWatcher::SERVICE_ID, 'catchCreatedDeliveryExecutionEvent']);
        $eventManager->attach(TestChangedEvent::EVENT_NAME, [ResultsWatcher::SERVICE_ID, 'catchTestChangedEvent']);
        $this->getServiceManager()->register(EventManager::SERVICE_ID, $eventManager);
    }
}