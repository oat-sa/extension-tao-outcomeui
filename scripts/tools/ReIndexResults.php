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
use oat\tao\model\search\SearchService;
use oat\taoDeliveryRdf\model\DeliveryAssemblyService;
use oat\taoOutcomeUi\model\search\ResultIndexIterator;

/**
 * ReIndex all results
 * Class ReIndexResults
 * @package oat\taoOutcomeUi\model
 */
class ReIndexResults extends AbstractAction
{
    /**
     * @param $params
     */
    public function __invoke($params)
    {
        $deliveryService = DeliveryAssemblyService::singleton();
        $deliveryClass = $deliveryService->getRootClass();
        $resultIndexIterator = new ResultIndexIterator([$deliveryClass->getUri()]);
        SearchService::getSearchImplementation()->index($resultIndexIterator);
    }
}