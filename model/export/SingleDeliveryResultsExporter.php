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

use common_exception_InvalidArgumentType;
use common_exception_NotFound;
use core_kernel_classes_Resource;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\filesystem\FileSystemService;
use oat\tao\model\export\implementation\AbstractFileExporter;
use oat\tao\model\export\implementation\CsvExporter;
use oat\tao\model\taskQueue\Task\FilesystemAwareTrait;
use oat\taoOutcomeUi\model\ResultsService;
use oat\taoOutcomeUi\model\table\ContextTypePropertyColumn;
use oat\taoOutcomeUi\model\table\VariableColumn;
use oat\taoOutcomeUi\model\table\VariableDataProvider;
use tao_models_classes_table_Column as TableColumn;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * SingleDeliveryResultsExporter
 *
 * @author Gyula Szucs <gyula@taotesting.com>
 */
class SingleDeliveryResultsExporter implements ResultsExporterInterface
{
    use OntologyAwareTrait;
    use ServiceLocatorAwareTrait;
    use FilesystemAwareTrait;

    public const RESULT_FORMAT = 'CSV';
    private core_kernel_classes_Resource $delivery;
    private ResultsService $resultsService;
    private array $columnsToExport = [];

    /**
     * @var TableColumn[]
     */
    private array $builtColumns = [];

    /**
     * Which submitted variables are we exporting?
     *
     * Possible values:
     *  - lastSubmitted (default)
     *  - firstSubmitted
     *
     * @var string
     */
    private string $variableToExport = ResultsService::VARIABLES_FILTER_LAST_SUBMITTED;
    private array $storageOptions = [];
    private ColumnsProvider $columnsProvider;
    private array $filters = [];
    public const CHUNK_SIZE = 100;

    /**
     * @throws common_exception_NotFound
     */
    public function __construct(
        core_kernel_classes_Resource $delivery,
        ResultsService $resultsService,
        ColumnsProvider $columnsProvider
    ) {
        $this->delivery = $this->getResource($delivery);

        if (!$this->delivery->exists()) {
            throw new common_exception_NotFound(
                sprintf(
                    'Results Exporter: delivery "%s" does not exist.',
                    $this->delivery->getUri()
                )
            );
        }

        $this->resultsService = $resultsService;
        $this->columnsProvider = $columnsProvider;
    }

    public function getResultFormat(): string
    {
        return static::RESULT_FORMAT;
    }

    /**
     * @inheritdoc
     */
    public function getResourceToExport(): core_kernel_classes_Resource
    {
        return $this->delivery;
    }

    /**
     * @inheritdoc
     */
    public function setColumnsToExport($columnsToExport)
    {
        $this->columnsToExport = $columnsToExport;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getColumnsToExport(): array
    {
        if (empty($this->builtColumns)) {
            if (!empty($this->columnsToExport)) {
                $columns = $this->columnsToExport;
            } else {
                $variables = array_merge(
                    $this->columnsProvider->getGradeColumns(),
                    $this->columnsProvider->getResponseColumns()
                );
                $columns = array_merge(
                    $this->columnsProvider->getTestTakerColumns(),
                    $this->columnsProvider->getDeliveryColumns(),
                    $variables,
                    $this->columnsProvider->getDeliveryExecutionColumns()
                );
            }

            // build column objects
            $this->builtColumns = $this->buildColumns($columns);
        }

        return $this->builtColumns;
    }

    /**
     * @inheritdoc
     */
    public function setVariableToExport($variableToExport)
    {
        $allowedFilters = [
            ResultsService::VARIABLES_FILTER_ALL,
            ResultsService::VARIABLES_FILTER_FIRST_SUBMITTED,
            ResultsService::VARIABLES_FILTER_LAST_SUBMITTED,
        ];
        if (!in_array($variableToExport, $allowedFilters)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Results Exporter: wrong submitted variable "%s"',
                    $variableToExport
                )
            );
        }

        $this->variableToExport = $variableToExport;

        return $this;
    }

    public function setFiltersToExport($filters)
    {
        $this->filters = $filters;
        return $this;
    }

    public function getFiltersToExport()
    {
        return $this->filters;
    }

    /**
     * @inheritdoc
     */
    public function getVariableToExport()
    {
        return $this->variableToExport;
    }

    /**
     * @inheritdoc
     */
    public function setStorageOptions(array $storageOptions)
    {
        $this->storageOptions = $storageOptions;

        return $this;
    }

    public function getData(): array
    {
        $results = $this->resultsService->getResultsByDelivery(
            $this->getResourceToExport(),
            $this->storageOptions,
            $this->getFiltersToExport()
        );

        $cells = $this->resultsService->getCellsByResults(
            $results,
            $this->getColumnsToExport(),
            $this->getVariableToExport(),
            $this->getFiltersToExport(),
            0,
            PHP_INT_MAX
        );

        if ($cells === null) {
            $cells = [];
        }

        // flattening data: only 'cell' is what we need
        return array_map(function ($row) {
            return $row['cell'];
        }, $cells);
    }

    private function sortByStartDate(&$data)
    {
        usort($data, function ($a, $b) {
            $bDate = $b[ColumnsProvider::LABEL_START_DELIVERY_EXECUTION] ?? null;
            $aDate = $a[ColumnsProvider::LABEL_START_DELIVERY_EXECUTION] ?? null;
            $startB = $bDate ? strtotime($bDate) : 0;
            $startA = $aDate ? strtotime($aDate) : 0;
            return $startB - $startA;
        });
        $data = array_reverse($data);
    }

    /**
     * @param array $results
     * @param int $offset
     * @param null $limit
     * @return array
     * @throws \common_Exception
     * @throws \common_exception_Error
     */
    private function getCells(array $results, int $offset = 0, $limit = null): ?array
    {
        $cells = $this->resultsService->getCellsByResults(
            $results,
            $this->getColumnsToExport(),
            $this->getVariableToExport(),
            $this->getFiltersToExport(),
            $offset,
            $limit
        );

        if ($cells === null) {
            return null;
        }
        // flattening data: only 'cell' is what we need
        return array_map(function ($row) {
            return $row['cell'];
        }, $cells);
    }

    /**
     * @inheritdoc
     */
    public function export($destination = null): string
    {
        $columnNames = $this->resultsService->getColumnNames($this->getColumnsToExport());

        $data = $this->resultsService->getResultsByDelivery(
            $this->getResourceToExport(),
            $this->storageOptions,
            $this->getFiltersToExport()
        );

        $offset = 0;

        $result = [];

        // getCells() consumes much memory inside it, so let's collect cells iteratively
        do {
            $cells = $this->getCells($data, $offset, self::CHUNK_SIZE);
            $offset += self::CHUNK_SIZE;
            if ($cells === null) {
                break;
            }
            foreach ($cells as $row) {
                $rowResult = [];
                foreach ($row as $rowKey => $rowVal) {
                    $rowResult[$rowKey] = $rowVal[0];
                }
                $result[] = $rowResult;
            }
        } while ($cells !== null);

        $this->sortByStartDate($result);

        array_unshift($result, $columnNames);

        $exporter = $this->getExporter($result);

        unset($columnNames, $data, $result);

        return is_null($destination)
            ? $this->saveStringToStorage($this->getExportData($exporter), $this->getFileName())
            : $this->saveToLocal($exporter, $destination);
    }

    protected function getExporter(array $result): AbstractFileExporter
    {
        return new CsvExporter($result);
    }

    /**
     * @param CsvExporter $exporter
     * @return string
     * @throws common_exception_InvalidArgumentType
     */
    protected function getExportData(AbstractFileExporter $exporter): string
    {
        return $exporter->export(false, false, ',', '"', false);
    }

    /**
     * @throws common_exception_InvalidArgumentType
     */
    private function saveToLocal(AbstractFileExporter $exporter, string $destination): string
    {
        $fullPath = realpath($destination) . DIRECTORY_SEPARATOR . $this->getFileName();

        file_put_contents($fullPath, $this->getExportData($exporter));

        return $fullPath;
    }

    private function getFileName(): string
    {
        return 'results_export_'
            . strtolower(\tao_helpers_Display::textCleaner($this->delivery->getLabel(), '*'))
            . '_'
            . \tao_helpers_Uri::getUniqueId($this->delivery->getUri())
            . '_'
            . date('YmdHis') . rand(10, 99) //more unique name
            . '.' . strtolower($this->getResultFormat());
    }

    /**
     * Build the column objects from the provided array of decoded column values. For example:
     *
     * [
     *  type = "oat\taoOutcomeUi\model\table\ContextTypePropertyColumn"
     *  label = "Test Taker"
     *  prop = "http://www.w3.org/2000/01/rdf-schema#label"
     *  contextType = "test_taker"
     * ]
     * [
     *  type = "oat\taoOutcomeUi\model\table\GradeColumn"
     *  label = "Planets and moons-SCORE"
     *  contextId = "http://taoplatform.loc/tao.rdf#i1499248290562399"
     *  contextLabel = "Planets and moons"
     *  variableIdentifier = "SCORE"
     * ]
     *
     * @return TableColumn[]
     */
    private function buildColumns(array $columnsData): array
    {
        $columns = [];
        $dataProvider = new VariableDataProvider();

        foreach ($columnsData as $column) {
            if (!isset($column['type']) || !is_subclass_of($column['type'], TableColumn::class)) {
                throw new \RuntimeException('Column type not specified or wrong type provided');
            }

            $column = TableColumn::buildColumnFromArray($column);
            if (!is_null($column)) {
                if ($column instanceof VariableColumn) {
                    $column->setDataProvider($dataProvider);
                }

                if ($column instanceof ContextTypePropertyColumn && $column->getProperty()->getUri() == RDFS_LABEL) {
                    $column->label = $column->isTestTakerType() ? __('Test Taker') : __('Delivery');
                }

                $columns[] = $column;
            }
        }

        return $columns;
    }

    /**
     * @see FilesystemAwareTrait::getFileSystemService()
     */
    protected function getFileSystemService()
    {
        return $this->getServiceLocator()
            ->get(FileSystemService::SERVICE_ID);
    }
}
