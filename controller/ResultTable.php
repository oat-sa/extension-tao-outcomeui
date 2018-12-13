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
 * Copyright (c) 2009-2012 (original work) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 *               2012-2017 Open Assessment Technologies SA;
 *
 */

namespace oat\taoOutcomeUi\controller;

use \common_Exception;
use oat\tao\model\taskQueue\TaskLogActionTrait;
use oat\taoOutcomeUi\model\export\ColumnsProvider;
use oat\generis\model\OntologyAwareTrait;
use oat\taoOutcomeUi\model\export\ResultsExporter;
use oat\taoOutcomeUi\model\ResultsService;
use oat\taoOutcomeUi\model\table\ResultsPayload;
use tao_helpers_Uri;
use oat\taoDeliveryRdf\model\DeliveryAssemblyService;

/**
 * Delivery Results export functionalities
 *
 * @author Gyula Szucs <gyula@taotesting.com>
 */
class ResultTable extends \tao_actions_CommonModule
{
    const PARAMETER_COLUMNS = 'columns';
    const PARAMETER_DELIVERY_URI = 'uri';
    const PARAMETER_FILTER = 'filter';

    use OntologyAwareTrait;
    use TaskLogActionTrait;

    /**
     * Return the Result Table entry page displaying the datatable and the filters to be applied.
     *
     * @throws \tao_models_classes_MissingRequestParameterException
     */
    public function index()
    {
        $deliveryService = DeliveryAssemblyService::singleton();
        if($this->getRequestParameter('classUri') !== $deliveryService->getRootClass()->getUri()) {
            $filter = $this->getRequestParameter('filter');
            $uri = $this->getRequestParameter('uri');
            if (!\common_Utils::isUri(tao_helpers_Uri::decode($uri))) {
                throw new \tao_models_classes_MissingRequestParameterException('uri');
            }
            $this->setData('filter', $filter);
            $this->setData('uri', $uri);
            $this->setView('resultTable.tpl');
        } else {
            $this->setData('type', 'info');
            $this->setData('error',__('No tests have been taken yet. As soon as a test-taker will take a test his results will be displayed here.'));
            $this->setView('index.tpl');
        }
    }

    /**
     * Feeds js datatable component with the values to be exported.
     *
     * @throws common_Exception
     */
    public function feedDataTable()
    {
        if (!$this->isXmlHttpRequest()) {
            throw new \Exception('Only ajax call allowed.');
        }

        if (!$this->hasRequestParameter(self::PARAMETER_COLUMNS)) {
            throw new common_Exception('Parameter "'. self::PARAMETER_COLUMNS .'" missing');
        }

        $this->returnJSON((new ResultsPayload($this->getExporterService()->getExporter()))->getPayload());
    }

    /**
     * Exports results by a single delivery.
     *
     * Only creating the export task.
     *
     * @throws \Exception
     */
    public function export()
    {
        if (!$this->isXmlHttpRequest()) {
            throw new \Exception('Only ajax call allowed.');
        }

        return $this->returnTaskJson($this->getExporterService()->createExportTask());
    }

    /**
     * Returns test taker metadata columns.
     *
     * @throws \Exception
     */
    public function getTestTakerColumns()
    {
        if (!$this->isXmlHttpRequest()) {
            throw new \Exception('Only ajax call allowed.');
        }

        return $this->returnJson([
            'columns' => $this->getColumnsProvider()->getTestTakerColumns(),
            'first'   => true
        ]);
    }

    /**
     * Returns delivery metadata columns.
     *
     * @throws \Exception
     */
    public function getDeliveryColumns()
    {
        if (!$this->isXmlHttpRequest()) {
            throw new \Exception('Only ajax call allowed.');
        }

        return $this->returnJson([
            'columns' => $this->getColumnsProvider()->getDeliveryColumns()
        ]);
    }

    /**
     * Returns grade columns.
     *
     * @throws \Exception
     */
    public function getGradeColumns()
    {
        if (!$this->isXmlHttpRequest()) {
            throw new \Exception('Only ajax call allowed.');
        }

        return $this->returnJson([
            'columns' => $this->getColumnsProvider()->getGradeColumns()
        ]);
    }

    /**
     * Returns response columns.
     *
     * @throws \Exception
     */
    public function getResponseColumns()
    {
        if (!$this->isXmlHttpRequest()) {
            throw new \Exception('Only ajax call allowed.');
        }

        return $this->returnJson([
            'columns' => $this->getColumnsProvider()->getResponseColumns()
        ]);
    }

    /**
     * @return ColumnsProvider
     */
    private function getColumnsProvider()
    {
        return new ColumnsProvider($this->getDeliveryUri(), ResultsService::singleton());
    }

    /**
     * @return ResultsExporter
     * @throws common_Exception
     */
    private function getExporterService()
    {
        /** @var ResultsExporter $exporter */
        $exporter = $this->propagate(new ResultsExporter($this->getDeliveryUri(), ResultsService::singleton()));

        if ($this->hasRequestParameter(self::PARAMETER_COLUMNS)) {
            $exporter->setColumnsToExport($this->getRawParameter(self::PARAMETER_COLUMNS));
        }

        if ($this->hasRequestParameter(self::PARAMETER_FILTER)) {
            $exporter->setVariableToExport($this->getRequestParameter(self::PARAMETER_FILTER));
        }

        return $exporter;
    }

    /**
     * @return string
     * @throws common_Exception
     */
    private function getDeliveryUri()
    {
        if (!$this->hasRequestParameter(self::PARAMETER_DELIVERY_URI)) {
            throw new common_Exception('Parameter "'. self::PARAMETER_DELIVERY_URI .'" missing');
        }

        return \tao_helpers_Uri::decode($this->getRequestParameter(self::PARAMETER_DELIVERY_URI));
    }
}
