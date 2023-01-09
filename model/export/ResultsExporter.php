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
 * Copyright (c) 2017-2022 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

declare(strict_types=1);

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

    private ResultsExporterInterface $exportStrategy;
    private ResultsService $resultsService;
    private array $columns = [];

    /**
     * @throws common_exception_NotFound
     */
    public function __construct(
        string $resourceUri,
        ResultsService $resultsService,
        DeliveryResultsExporterFactoryInterface $deliveryResultsExporterFactory = null
    ) {
        $resource = $this->getResource($resourceUri);

        if ($deliveryResultsExporterFactory === null) {
            $deliveryResultsExporterFactory = new DeliveryCsvResultsExporterFactory();
        }

        if ($resource->isClass()) {
            $this->exportStrategy = new MultipleDeliveriesResultsExporter(
                $this->getClass($resource->getUri()),
                $resultsService,
                $deliveryResultsExporterFactory
            );
        } else {
            $this->exportStrategy = $deliveryResultsExporterFactory->getDeliveryResultsExporter(
                $resource,
                $resultsService
            );
        }
        $this->resultsService = $resultsService;
    }

    public function getExporter(): ResultsExporterInterface
    {
        $this->exportStrategy->setServiceLocator($this->getServiceLocator());
        $this->exportStrategy->setColumnsToExport($this->columns);

        return $this->exportStrategy;
    }

    public function setColumnsToExport(array $columnsToExport): self
    {
        $this->columns = $columnsToExport;

        return $this;
    }

    public function setVariableToExport(string $variableToExport): self
    {
        $this->getExporter()->setVariableToExport($variableToExport);
        return $this;
    }

    public function setFiltersToExport(array $filters): self
    {
        $this->getExporter()->setFiltersToExport($filters);
        return $this;
    }

    public function export(string $destination = null): string
    {
        return $this->getExporter()
            ->setColumnsToExport($this->columns)
            ->export($destination);
    }

    /**
     * @return CallbackTaskInterface
     */
    public function createExportTask()
    {
        /** @var QueueDispatcherInterface $queueDispatcher */
        $queueDispatcher = $this->getServiceLocator()->get(QueueDispatcherInterface::SERVICE_ID);

        $label = $this->exportStrategy->getResourceToExport()->isClass()
            ? __(
                '%s results export for delivery class "%s"',
                $this->exportStrategy->getResultFormat(),
                $this->exportStrategy->getResourceToExport()->getLabel()
            )
            : __(
                '%s results export for delivery "%s"',
                $this->exportStrategy->getResultFormat(),
                $this->exportStrategy->getResourceToExport()->getLabel()
            );

        return $queueDispatcher->createTask(
            new ExportDeliveryResults(),
            [
                $this->getExporter()->getResourceToExport()->getUri(),
                $this->columns,
                $this->getExporter()->getVariableToExport(),
                $this->getExporter()->getFiltersToExport(),
                $this->exportStrategy->getResultFormat()
            ],
            $label
        );
    }
}
