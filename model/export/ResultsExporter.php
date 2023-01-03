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

namespace oat\taoOutcomeUi\model\export;

use common_exception_NotFound;
use oat\generis\model\OntologyAwareTrait;
use oat\tao\model\taskQueue\QueueDispatcherInterface;
use oat\tao\model\taskQueue\Task\CallbackTaskInterface;
use oat\taoOutcomeUi\model\ResultsService;
use oat\taoOutcomeUi\scripts\task\ExportDeliveryResults;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * ResultsExporter
 *
 * @author Gyula Szucs <gyula@taotesting.com>
 */
class ResultsExporter implements ServiceLocatorAwareInterface
{
    use OntologyAwareTrait;
    use ServiceLocatorAwareTrait;

    private $exportStrategy;
    /**
     * @var ResultsService
     */
    private $resultsService;

    /**
     * ResultsExporter constructor.
     * @param $resource
     * @param ResultsService $resultsService
     * @param DeliveryResultsExporterFactoryInterface|null $deliveryResultsExporterFactory
     * @throws common_exception_NotFound
     */
    public function __construct($resource, ResultsService $resultsService, DeliveryResultsExporterFactoryInterface $deliveryResultsExporterFactory = null)
    {
        $resource = $this->getResource($resource);

        if ($deliveryResultsExporterFactory === null) {
            $deliveryResultsExporterFactory = new DeliveryCsvResultsExporterFactory();
        }

        if ($resource->isClass()) {
            $this->exportStrategy = new MultipleDeliveriesResultsExporter($this->getClass($resource->getUri()), $resultsService, $deliveryResultsExporterFactory);
        } else {
            $this->exportStrategy = $deliveryResultsExporterFactory->getDeliveryResultsExporter(
                $resource,
                $resultsService
            );
        }
        $this->resultsService = $resultsService;
    }

    /**
     * @return ResultsExporterInterface
     */
    public function getExporter()
    {
        $this->exportStrategy->setServiceLocator($this->getServiceLocator());

        return $this->exportStrategy;
    }

    /**
     * @param string|array $columnsToExport
     * @return ResultsExporter
     */
    public function setColumnsToExport($columnsToExport)
    {
        $this->getExporter()->setColumnsToExport($columnsToExport);

        return $this;
    }

    /**
     * @param string $variableToExport
     * @return ResultsExporter
     */
    public function setVariableToExport($variableToExport)
    {
        $this->getExporter()->setVariableToExport($variableToExport);
        return $this;
    }

    public function setFiltersToExport($filters)
    {
        $this->getExporter()->setFiltersToExport($filters);
        return $this;
    }

    /**
     * @param null|string $destination
     * @return string
     */
    public function export($destination = null)
    {
        return $this->getExporter()->export($destination);
    }

    /**
     * @return CallbackTaskInterface
     */
    public function createExportTask()
    {
        /** @var QueueDispatcherInterface $queueDispatcher */
        $queueDispatcher = $this->getServiceLocator()->get(QueueDispatcherInterface::SERVICE_ID);

        $columns = [];

        // we need to convert every column object into array first
        foreach ($this->getExporter()->getColumnsToExport() as $column) {
            $columns[] = $column->toArray();
        }

        $label = $this->exportStrategy->getResourceToExport()->isClass()
            ? __('%s results export for delivery class "%s"', $this->exportStrategy->getResultFormat(), $this->exportStrategy->getResourceToExport()->getLabel())
            : __('%s results export for delivery "%s"', $this->exportStrategy->getResultFormat(), $this->exportStrategy->getResourceToExport()->getLabel());

        return $queueDispatcher->createTask(
            new ExportDeliveryResults(),
            [
                $this->getExporter()->getResourceToExport()->getUri(),
                $columns,
                $this->getExporter()->getVariableToExport(),
                $this->getExporter()->getFiltersToExport(),
                $this->exportStrategy->getResultFormat()
            ],
            $label
        );
    }
}
