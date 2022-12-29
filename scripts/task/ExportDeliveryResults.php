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

declare(strict_types=1);

namespace oat\taoOutcomeUi\scripts\task;

use common_exception_NotFound;
use Exception;
use InvalidArgumentException;
use oat\oatbox\reporting\Report as Report;
use core_kernel_classes_Resource;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\action\Action;
use oat\tao\model\taskQueue\Task\WorkerContextAwareInterface;
use oat\tao\model\taskQueue\Task\WorkerContextAwareTrait;
use oat\taoOutcomeUi\model\export\DeliveryResultsExporterFactoryInterface;
use oat\taoOutcomeUi\model\export\ResultsExporter;
use oat\taoOutcomeUi\model\export\DeliveryCsvResultsExporterFactory;
use oat\taoOutcomeUi\model\export\SingleDeliverySqlResultsExporter;
use oat\taoOutcomeUi\model\export\DeliverySqlResultsExporterFactory;
use oat\taoOutcomeUi\model\ResultsService;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

// phpcs:disable Generic.Files.LineLength
/**
 * ExportDeliveryResults action, can be called either during a http request or from cli.
 *
 * Usage examples for CLI:
 *
 * If "--dir" is omitted, the file will be saved in the task queue storage.
 *
 * ```
 * sudo -u www-data php index.php 'oat\taoOutcomeUi\scripts\task\ExportDeliveryResults' <deliveryOrClassId>
 * sudo -u www-data php index.php 'oat\taoOutcomeUi\scripts\task\ExportDeliveryResults' <deliveryOrClassId> --dir=/home/user/exports
 * sudo -u www-data php index.php 'oat\taoOutcomeUi\scripts\task\ExportDeliveryResults' <deliveryOrClassId> --columns=tt,delivery,grades,responses
 * sudo -u www-data php index.php 'oat\taoOutcomeUi\scripts\task\ExportDeliveryResults' <deliveryOrClassId> --columns=all
 * sudo -u www-data php index.php 'oat\taoOutcomeUi\scripts\task\ExportDeliveryResults' <deliveryOrClassId> --columns=tt,delivery,grades,responses --submittedVersion=lastSubmitted
 * ```
 * @author Gyula Szucs <gyula@taotesting.com>
 */
// phpcs:enable
class ExportDeliveryResults implements Action, ServiceLocatorAwareInterface, WorkerContextAwareInterface
{
    use ServiceLocatorAwareTrait;
    use OntologyAwareTrait;
    use WorkerContextAwareTrait;

    /**
     * Possible CLI values for columns
     */
    private const COLUMNS_CLI_VALUE_ALL = 'all';
    private const COLUMNS_CLI_VALUE_TEST_TAKER = 'tt';
    private const COLUMNS_CLI_VALUE_DELIVERY = 'delivery';
    private const COLUMNS_CLI_VALUE_GRADES = 'grades';
    private const COLUMNS_CLI_VALUE_RESPONSES = 'responses';

    private core_kernel_classes_Resource $resourceToExport;
    private ?ResultsExporter $exporterService = null;
    private array $columns = [];
    private ?string $submittedVersion = null;
    private ?string $destination = null;
    private array $filters = [];
    private ?DeliveryResultsExporterFactoryInterface $deliveryResultsExporterFactory = null;

    /**
     * @param array $params
     */
    public function __invoke($params): Report
    {
        $this->loadExtensions();

        try {
            $this->parseParams($params);

            if ($this->submittedVersion) {
                $this->getExporterService()->setVariableToExport($this->submittedVersion);
            }

            $fileName = $this->getExporterService()
                ->setColumnsToExport($this->columns)
                ->setFiltersToExport($this->filters)
                ->export($this->destination);

            $msg = $fileName
                ? $this->isWorkerContext()
                    ? __('Results of "%s" successfully exported', $this->resourceToExport->getLabel())
                    : __(
                        'Results of "%s" successfully exported into "%s"',
                        $this->resourceToExport->getLabel(),
                        $fileName
                    )
                : __('Nothing to export for "%s"', $this->resourceToExport->getLabel());

            return Report::createSuccess($msg, $fileName);
        } catch (Exception $e) {
            return Report::createError($e->getMessage());
        }
    }

    /**
     * @throws common_exception_NotFound
     */
    private function getExporterService(): ResultsExporter
    {
        if (is_null($this->exporterService)) {
            $this->exporterService = new ResultsExporter(
                $this->resourceToExport->getUri(),
                ResultsService::singleton(),
                $this->deliveryResultsExporterFactory
            );
            $this->exporterService->setServiceLocator($this->getServiceLocator());
        }

        return $this->exporterService;
    }

    /**
     * Load the required TAO extensions (for constants)
     */
    private function loadExtensions(): void
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
     */
    private function parseParams(array $params): void
    {
        // Delivery or Class Uri
        if (!isset($params[0])) {
            throw new InvalidArgumentException(
                'Delivery or class uri missing. Please provide it as the first argument.'
            );
        }

        $this->resourceToExport = $this->getResource($params[0]);

        if ($this->isWorkerContext()) {
            // Columns to be exported, if defined
            if (isset($params[1]) && is_array($params[1])) {
                $this->columns = $params[1];
            }

            // Submitted version of variables, if defined
            if (isset($params[2])) {
                $this->submittedVersion = $params[2];
            }

            if (isset($params[3])) {
                $this->filters = $params[3];
            }

            if (isset($params[4]) && $params[4] === SingleDeliverySqlResultsExporter::RESULT_FORMAT) {
                $this->deliveryResultsExporterFactory = new DeliverySqlResultsExporterFactory();
            } else {
                $this->deliveryResultsExporterFactory = new DeliveryCsvResultsExporterFactory();
            }

            return;
        }
        // if the task is called from CLI

        // remove first param, it is always the resource uri, no need to re-check
        unset($params[0]);

        // check params. running the command from CLI we have different params structure
        foreach ($params as $param) {
            list($option, $value) = explode('=', $param);

            switch ($option) {
                case '--columns':
                    $columns = explode(',', $value);
                    $this->validateAndPopulateColumns($columns);

                    break;

                case '--submittedVersion':
                    if (
                        !in_array($value, [
                            ResultsService::VARIABLES_FILTER_ALL,
                            ResultsService::VARIABLES_FILTER_FIRST_SUBMITTED,
                            ResultsService::VARIABLES_FILTER_LAST_SUBMITTED
                        ])
                    ) {
                        throw new InvalidArgumentException(
                            sprintf(
                                'Invalid submitted version of variables %s. Valid options: %s',
                                $value,
                                implode(', ', [
                                    ResultsService::VARIABLES_FILTER_FIRST_SUBMITTED,
                                    ResultsService::VARIABLES_FILTER_LAST_SUBMITTED
                                ])
                            )
                        );
                    }

                    $this->submittedVersion = $value;
                    break;

                case '--dir':
                    if (!is_dir($value)) {
                        throw new InvalidArgumentException('Invalid directory "' . $value . '" provided.');
                    }

                    if (!is_writable($value)) {
                        throw new InvalidArgumentException('Directory "' . $value . '" not writable.');
                    }
                    $this->destination = $value;
                    break;
                case '--start-from':
                    $this->filters['startfrom'] = $value;
                    break;
                case '--start-to':
                    $this->filters['startto'] = $value;
                    break;
            }
        }
    }

    /**
     * @throws InvalidArgumentException
     * @throws common_exception_NotFound
     */
    private function validateAndPopulateColumns(array $columns): void
    {
        $invalidValues = array_diff($columns, $this->getPossibleColumnValues());

        if (count($invalidValues)) {
            throw new InvalidArgumentException(
                'Invalid columns value(s) "' . implode(
                    ', ',
                    $invalidValues
                ) . '". Valid options: ' . implode(', ', $this->getPossibleColumnValues())
            );
        }

        if (in_array(self::COLUMNS_CLI_VALUE_ALL, $columns)) {
            // skip cause SingleDeliveryResultsExporter use all columns by default
            return;
        }

        foreach ($columns as $column) {
            switch ($column) {
                case self::COLUMNS_CLI_VALUE_TEST_TAKER:
                    $this->columns = array_merge(
                        $this->columns,
                        $this->getExporterService()->getTestTakerColumns()
                    );
                    break;

                case self::COLUMNS_CLI_VALUE_DELIVERY:
                    $this->columns = array_merge(
                        $this->columns,
                        $this->getExporterService()->getDeliveryColumns()
                    );
                    break;

                case self::COLUMNS_CLI_VALUE_GRADES:
                    $this->columns = array_merge(
                        $this->columns,
                        $this->getExporterService()->getGradeColumns()
                    );
                    break;

                case self::COLUMNS_CLI_VALUE_RESPONSES:
                    $this->columns = array_merge(
                        $this->columns,
                        $this->getExporterService()->getResponseColumns()
                    );
                    break;
            }
        }
    }

    private function getPossibleColumnValues(): array
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
