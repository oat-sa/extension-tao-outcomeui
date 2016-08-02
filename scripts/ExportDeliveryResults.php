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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA;
 *
 *
 */

namespace oat\taoOutcomeUi\scripts;

use common_report_Report as Report;
use oat\oatbox\action\Action;
use oat\taoOutcomeUi\model\table\VariableColumn;
use oat\taoOutcomeUi\model\table\VariableDataProvider;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use oat\tao\model\export\implementation\CsvExporter;
use oat\oatbox\action\ResolutionException;
use oat\taoOutcomeUi\model\ResultsService;

//Load extension to define necessary constants.
\common_ext_ExtensionsManager::singleton()->getExtensionById('taoOutcomeUi');
\common_ext_ExtensionsManager::singleton()->getExtensionById('taoDeliveryRdf');

/**
 * Class ExportDeliveryResults
 * @package oat\taoOutcomeUi\scripts
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 *
 * Run example:
 * ```
 * sudo -u www-data php index.php 'oat\taoOutcomeUi\scripts\ExportDeliveryResults' <deliveryId> <filePath> [format]
 * ```
 *
 * Default format is CSV
 */
class ExportDeliveryResults implements Action, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * @var array Available script modes
     */
    static $formats = ['csv'];

    /**
     * @var \Report
     */
    protected $report;

    /**
     * @var array
     */
    protected $params;

    /**
     * @param $params
     * @return Report
     */
    public function __invoke($params)
    {
        $this->params = $params;

        try {
            $this->process();
        } catch (ResolutionException $e) {
            $this->report = new Report(
                Report::TYPE_ERROR,
                $e->getMessage()
            );
        }

        return $this->report;
    }

    /**
     * Process action call
     *
     * @throws ResolutionException
     */
    private function process()
    {
        if (empty($this->params)) {
            throw new ResolutionException(__('Parameters were not given. Expected Syntax: ExportDeliveryResults <deliveryId> <filePath> [format]'));
        }

        $data = $this->getData();
        $format = $this->getFormat();
        $path = $this->getPath();

        switch ($format) {
            case 'csv':
                $exporter = new CsvExporter($data);
                break;
        }

        $result = $exporter->export(true, false, ';');

        file_put_contents($path, $result);

        $this->report = new Report(
            Report::TYPE_SUCCESS,
            'Results successfully exported'
        );
    }

    /**
     * @return array
     */
    private function getData()
    {
        $delivery = new \core_kernel_classes_Resource($this->params[0]);
        $resultsService = ResultsService::singleton();
        $filter = 'lastSubmitted';

        $columns = [];

        $testtaker = new \tao_models_classes_table_PropertyColumn(new \core_kernel_classes_Property(PROPERTY_RESULT_OF_SUBJECT));
        $testTakerColumn[] = $testtaker->toArray();
        $cols = array_merge(
            $testTakerColumn,
            $resultsService->getVariableColumns($delivery, CLASS_OUTCOME_VARIABLE, $filter),
            $resultsService->getVariableColumns($delivery, CLASS_RESPONSE_VARIABLE, $filter)
        );

        $dataProvider = new VariableDataProvider();
        foreach ($cols as $col) {
            $column = \tao_models_classes_table_Column::buildColumnFromArray($col);
            if (!is_null($column)) {
                if($column instanceof VariableColumn){
                    $column->setDataProvider($dataProvider);
                }
                $columns[] = $column;
            }
        }
        $columns[0]->label = __("Test taker");
        $rows = $resultsService->getResultsByDelivery($delivery, $columns, $filter);
        $columnNames = array_reduce($columns, function ($carry, $item) {
            $carry[] = $item->label;
            return $carry;
        });
        $result = [];
        foreach ($rows as $row) {
            $rowResult = [];
            foreach ($row['cell'] as $rowKey => $rowVal) {
                $rowResult[$columnNames[$rowKey]] = $rowVal[0];
            }
            $result[] = $rowResult;
        }

        //If there are no executions yet, the file is exported but contains only the header
        if (empty($result)) {
            $result = [array_fill_keys($columnNames, '')];
        }

        return $result;
    }

    /**
     * @return mixed
     * @throws ResolutionException
     */
    private function getPath()
    {
        if (!isset($this->params[1])) {
            throw new ResolutionException('Path was not specified');
        }
        $path = $this->params[1];
        if (file_exists($path)) {
            throw new ResolutionException(__('File "%s" already exists', $path));
        }
        return $path;
    }

    /**
     * Get export format
     * @throws ResolutionException
     * @return string
     */
    private function getFormat()
    {
        if (isset($this->params[2])) {
            $format = $this->params[2];
            if (!in_array($format, self::$formats)) {
                throw new ResolutionException('Wrong format was specified');
            }
        } else {
            $format = self::$formats[0];
        }

        return $format;
    }
}