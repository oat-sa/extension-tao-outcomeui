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

use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\service\ConfigurableService;
use oat\taoDelivery\model\execution\DeliveryExecutionInterface;
use oat\taoDelivery\models\classes\execution\event\DeliveryExecutionCreated;
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

    /**
     * @param DeliveryExecutionCreated $event
     * @return \common_report_Report
     * @throws \common_exception_NotFound
     */
    public function catchCreatedDeliveryExecutionEvent(DeliveryExecutionCreated $event)
    {
        /** @var DeliveryExecutionInterface $resource */
        $deliveryExecution = $event->getDeliveryExecution();
        return $this->getResultIndexer()->addIndex($deliveryExecution);
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
            return $this->getResultIndexer()->addIndex($deliveryExecution);
        }
    }

    /**
     * Control filtering visibility
     * @return boolean
     */
    public function isResultSearchEnabled()
    {
        return (bool)$this->getOption(self::OPTION_RESULT_SEARCH_FIELD_VISIBILITY);
    }

    private function getResultIndexer(): ResultIndexer
    {
        return $this->getServiceLocator()->get(ResultIndexer::class);
    }
}
