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
use oat\taoTaskQueue\model\Task\WorkerContextAwareInterface;
use oat\taoTaskQueue\model\Task\WorkerContextAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

/**
 * DeliveryResultsExporter action, can be called either during a http request or from cli.
 *
 * Usage examples for CLI:
 *
 * ```
 * sudo -u www-data php index.php 'oat\taoOutcomeUi\scripts\task\ExportDeliveryResults' <deliveryId>
 * sudo -u www-data php index.php 'oat\taoOutcomeUi\scripts\task\ExportDeliveryResults' <deliveryId> --columns=tt,delivery,grades,responses
 * sudo -u www-data php index.php 'oat\taoOutcomeUi\scripts\task\ExportDeliveryResults' <deliveryId> --columns=all
 * sudo -u www-data php index.php 'oat\taoOutcomeUi\scripts\task\ExportDeliveryResults' <deliveryId> --columns=tt,delivery,grades,responses --submittedVersion=lastSubmitted
 * ```
 * @author Gyula Szucs <gyula@taotesting.com>
 */
class ExportDeliveryResults implements Action, ServiceLocatorAwareInterface, WorkerContextAwareInterface
{
    use ServiceLocatorAwareTrait;
    use OntologyAwareTrait;
    use WorkerContextAwareTrait;

    /**
     * Possible CLI values for columns
     */
    const COLUMNS_CLI_VALUE_ALL = 'all';
    const COLUMNS_CLI_VALUE_TEST_TAKER = 'tt';
    const COLUMNS_CLI_VALUE_DELIVERY = 'delivery';
    const COLUMNS_CLI_VALUE_GRADES = 'grades';
    const COLUMNS_CLI_VALUE_RESPONSES = 'responses';

    /**
     * @var DeliveryResultsExporter
     */
    private $exporterService;

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

            $this->getExporterService()->setColumnsToExport($this->columns);

            if ($this->submittedVersion == ResultsService::VARIABLES_FILTER_FIRST_SUBMITTED) {
                $this->getExporterService()->useFirstSubmittedVariables();
            } else {
                $this->getExporterService()->useLastSubmittedVariables();
            }

            $file = $this->getExporterService()->export();

            $msg = $this->isWorkerContext()
                ? __('Results of "%s" successfully exported', $this->delivery->getLabel())
                : __('Results of "%s" successfully exported into "%s"', $this->delivery->getLabel(), $file->getPrefix());

            return Report::createSuccess($msg, $file->getPrefix());
        } catch (\Exception $e) {
            return Report::createFailure($e->getMessage());
        }
    }

    /**
     * @return DeliveryResultsExporter
     */
    private function getExporterService()
    {
        if (is_null($this->exporterService)) {
            $this->exporterService = new DeliveryResultsExporter($this->delivery, ResultsService::singleton());
            $this->exporterService->setServiceLocator($this->getServiceLocator());
        }

        return $this->exporterService;
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
     * Params order:
     * - $params[0]: delivery uri (required, string)
     * - $params[1]: columns (optional, array|string)
     * - $params[2]: submittedVersion (optional, string)
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

        if ($this->isWorkerContext()) {
            // Columns to be exported, if defined
            if (isset($params[1]) && is_array($params[1])) {
                $this->columns = $params[1];
            }

            // Submitted version of variables, if defined
            if (isset($params[2])) {
                $this->submittedVersion = $params[2];
            }
        } else {
            // if the task is called from CLI

            // remove first param, it is always the delivery uri, no need to re-check
            unset($params[0]);

            // check params. running the command from CLI we have different params structure
            foreach ($params as $param) {
                list($option, $value) = explode('=', $param);

                switch ($option) {
                    case '--columns':
                        $columns = explode(',', $value);

                        $invalidValues = array_diff($columns, $this->getPossibleColumnValues());

                        if (count($invalidValues)) {
                            throw new \InvalidArgumentException('Invalid columns value(s) "' . implode(', ', $invalidValues) . '". Valid options: ' . implode(', ', $this->getPossibleColumnValues()));
                        }

                        if (in_array(self::COLUMNS_CLI_VALUE_ALL, $columns)) {
                            // do nothing because DeliveryResultsExporter will use all columns by default if no columns specified
                            continue;
                        }

                        foreach ($columns as $column) {
                            switch ($column) {
                                case self::COLUMNS_CLI_VALUE_TEST_TAKER:
                                    $this->columns = array_merge($this->columns, $this->getExporterService()->getTestTakerColumns());
                                    break;

                                case self::COLUMNS_CLI_VALUE_DELIVERY:
                                    $this->columns = array_merge($this->columns, $this->getExporterService()->getDeliveryColumns());
                                    break;

                                case self::COLUMNS_CLI_VALUE_GRADES:
                                    $this->columns = array_merge($this->columns, $this->getExporterService()->getGradeColumns());
                                    break;

                                case self::COLUMNS_CLI_VALUE_RESPONSES:
                                    $this->columns = array_merge($this->columns, $this->getExporterService()->getResponseColumns());
                                    break;
                            }
                        }
                        break;

                    case '--submittedVersion':
                        if (!in_array($value, [ResultsService::VARIABLES_FILTER_FIRST_SUBMITTED, ResultsService::VARIABLES_FILTER_LAST_SUBMITTED])) {
                            throw new \InvalidArgumentException('Invalid submitted version of variables "' . $value . '". Valid options: ' . implode(', ', [ResultsService::VARIABLES_FILTER_FIRST_SUBMITTED, ResultsService::VARIABLES_FILTER_LAST_SUBMITTED]));
                        }

                        $this->submittedVersion = $value;
                        break;
                }
            }
        }
    }

    /**
     * @return array
     */
    private function getPossibleColumnValues()
    {
        return [
            self::COLUMNS_CLI_VALUE_ALL,
            self::COLUMNS_CLI_VALUE_TEST_TAKER,
            self::COLUMNS_CLI_VALUE_DELIVERY,
            self::COLUMNS_CLI_VALUE_GRADES,
            self::COLUMNS_CLI_VALUE_RESPONSES
        ];
    }
}