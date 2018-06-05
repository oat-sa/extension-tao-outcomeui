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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 */

namespace oat\taoOutcomeUi\scripts\tools;

use common_report_Report as Report;
use oat\oatbox\extension\script\ScriptAction;
use oat\taoDelivery\model\execution\ServiceProxy;
use oat\taoOutcomeUi\model\ResultsService;
use oat\taoOutcomeUi\model\Wrapper\ResultServiceWrapper;

/**
 * Run example:
 *
 * ```
 * sudo -u www-data php index.php 'oat\taoOutcomeUi\scripts\tools\DeleteResultCache' -u {deliveryExecutionUri}
 * ```
 */
class DeleteResultCache extends ScriptAction
{
    protected function provideOptions()
    {
        return [
            'deliveryExecutionUri' => [
                'prefix' => 'u',
                'longPrefix' => 'uri',
                'required' => true,
                'description' => 'Delivery Execution Uri aka. Result Identifier Uri'
            ],
        ];
    }

    protected function provideDescription()
    {
        return 'The script deletes all result cache for the specified delivery execution/result';
    }

    protected function provideUsage()
    {
        return [
            'prefix' => 'h',
            'longPrefix' => 'help',
        ];
    }

    protected function run()
    {
        try {
            /** @var ServiceProxy $serviceProxy */
            $serviceProxy = $this->getServiceLocator()->get(ServiceProxy::SERVICE_ID);

            /** @var \core_kernel_classes_Resource $resource */
            $deliveryExecution = $serviceProxy->getDeliveryExecution($this->getOption('deliveryExecutionUri'));

            /** @var ResultsService $resultsService */
            $resultsService = $this->getServiceManager()->get(ResultServiceWrapper::SERVICE_ID)->getService();

            if (is_null($resultsService->getCache())) {
                throw new \RuntimeException('No result cache has been configured in persistence, so there is nothing to delete.');
            }

            $deliveryExecutionIdentifier = $deliveryExecution->getIdentifier();
            if ($resultsService->deleteCacheFor($deliveryExecutionIdentifier)) {
                $report = Report::createSuccess("Cache has been successfully deleted for entry '${deliveryExecutionIdentifier}'.");
            } else {
                throw new \RuntimeException("No cache has been deleted for entry '${deliveryExecutionIdentifier}'.");
            }
        } catch (\Exception $e) {
            $report = Report::createFailure($e->getMessage());
        }

        return $report;
    }
}