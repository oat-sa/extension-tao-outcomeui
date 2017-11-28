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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoOutcomeUi\scripts\task;

use common_report_Report as Report;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\action\Action;
use oat\taoOutcomeUi\model\export\DeliveryResultsExporter;
use oat\taoOutcomeUi\model\ResultsService;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

/**
 * DeliveryResultsExporter action, can be called either during a http request or from cli.
 *
 * Using it in CLI:
 *
 * ```
 * sudo -u www-data php index.php 'oat\taoOutcomeUi\scripts\task\ExportDeliveryResults' <deliveryId>
 * ```
 *
 * @author Gyula Szucs <gyula@taotesting.com>
 */
class ExportDeliveryResults implements Action, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;
    use OntologyAwareTrait;

    /**
     * @var \core_kernel_classes_Resource
     */
    private $delivery;
    private $columns = [];
    private $submittedVersion;

    /**
     * @param array $params
     * @return Report
     */
    public function __invoke($params)
    {
        $this->loadExtensions();

        try {
            $this->parseParams($params);

            $exporter = (new DeliveryResultsExporter($this->delivery, ResultsService::singleton()))
                ->setColumnsToExport($this->columns);

            if ($this->submittedVersion == ResultsService::VARIABLES_FILTER_FIRST_SUBMITTED) {
                $exporter->useFirstSubmittedVariables();
            } else {
                $exporter->useLastSubmittedVariables();
            }

            $exporter->setServiceLocator($this->getServiceLocator());

            $file = $exporter->export();

            return Report::createSuccess(
                __('Results successfully exported for delivery "%s"', $this->delivery->getLabel()),
                $file->getPrefix()
            );
        } catch (\Exception $e) {
            return Report::createFailure($e->getMessage());
        }
    }

    /**
     * Load the required TAO extensions (for constants)
     */
    private function loadExtensions()
    {
        $this->getServiceLocator()->get(\common_ext_ExtensionsManager::SERVICE_ID)->getExtensionById('taoOutcomeUi');
        $this->getServiceLocator()->get(\common_ext_ExtensionsManager::SERVICE_ID)->getExtensionById('taoDeliveryRdf');
    }

    /**
     * Checks and sets parameters.
     *
     * @param array $params
     */
    private function parseParams($params)
    {
        // Delivery Uri
        if (!isset($params[0])) {
            throw new \InvalidArgumentException('Delivery uri missing. Please provide it as the first argument.');
        }

        $this->delivery = $this->getResource($params[0]);

        if (!$this->delivery->exists()) {
            throw new \RuntimeException('Delivery does not exist.');
        }

        // Columns to be exported, if defined
        if (isset($params[1])) {
            $this->columns = (array) $params[1];
        }

        // Submitted version of variables, if defined
        if (isset($params[2])) {
            $this->submittedVersion = $params[2];
        }
    }
}