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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoOutcomeUi\model\export;

use oat\tao\model\export\implementation\sql\ExportedColumn;
use oat\tao\model\export\implementation\SqlExporter;
use oat\taoOutcomeUi\model\table\VariableColumn;

/**
 * SingleDeliveryResultsExporter
 *
 * @author Andrey Niahrou
 */
class SingleDeliverySqlResultsExporter extends SingleDeliveryResultsExporter implements ResultsExporterInterface
{
    public const RESULT_FORMAT = 'SQL';

    private $mappingVarTypes = [
        'integer'    => ExportedColumn::TYPE_INTEGER,
        'boolean'    => ExportedColumn::TYPE_BOOLEAN,
        'identifier' => ExportedColumn::TYPE_VARCHAR,
        'duration'   => ExportedColumn::TYPE_DECIMAL,
        'float'      => ExportedColumn::TYPE_DECIMAL
    ];

    private $mappingFieldsTypes = [
        'Test Taker ID'             => ExportedColumn::TYPE_VARCHAR,
        'Test Taker'                => ExportedColumn::TYPE_VARCHAR,
        'Login'                     => ExportedColumn::TYPE_VARCHAR,
        'First Name'                => ExportedColumn::TYPE_VARCHAR,
        'Last Name'                 => ExportedColumn::TYPE_VARCHAR,
        'Mail'                      => ExportedColumn::TYPE_VARCHAR,
        'Interface Language'        => ExportedColumn::TYPE_VARCHAR,
        'Group'                     => ExportedColumn::TYPE_VARCHAR,
        'Delivery'                  => ExportedColumn::TYPE_VARCHAR,
        'Title'                     => ExportedColumn::TYPE_VARCHAR,
        'Start Date'                => ExportedColumn::TYPE_TIMESTAMP,
        'End Date'                  => ExportedColumn::TYPE_TIMESTAMP,
        'Display Order'             => ExportedColumn::TYPE_VARCHAR,
        'Access'                    => ExportedColumn::TYPE_VARCHAR,
        'Runtime'                   => ExportedColumn::TYPE_VARCHAR,
        'Delivery container serial' => ExportedColumn::TYPE_VARCHAR,
        'Delivery origin'           => ExportedColumn::TYPE_VARCHAR,
        'Compilation Directory'     => ExportedColumn::TYPE_VARCHAR,
        'Compilation Time'          => ExportedColumn::TYPE_INTEGER,
        'Start Delivery Execution'  => ExportedColumn::TYPE_TIMESTAMP,
        'End Delivery Execution'    => ExportedColumn::TYPE_TIMESTAMP,
        'Max. Executions (default: unlimited)' => ExportedColumn::TYPE_VARCHAR,
    ];

    /**
     * @param SqlExporter $exporter
     * @return string
     */
    protected function getExportData($exporter)
    {
        return $exporter->export();
    }

    /**
     * @param array $result
     * @return SqlExporter
     */
    protected function getExporter($result)
    {
        foreach ($this->getColumnsToExport() as $columnData) {
            if ($columnData instanceof VariableColumn) {
                $type = $columnData->getBaseType() && isset($this->mappingVarTypes[$columnData->getBaseType()])
                    ? $this->mappingVarTypes[$columnData->getBaseType()]
                    : ExportedColumn::TYPE_VARCHAR;

                $this->mappingFieldsTypes[$columnData->getLabel()] = $type;
            }
        }
        return new SqlExporter($result, $this->mappingFieldsTypes, 'result_table');
    }
}
