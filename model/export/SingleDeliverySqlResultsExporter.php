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
use taoResultServer_models_classes_Variable as Variable;

/**
 * SingleDeliveryResultsExporter
 *
 * @author Andrey Niahrou
 */
class SingleDeliverySqlResultsExporter extends SingleDeliveryResultsExporter
{
    public const RESULT_FORMAT = 'SQL';

    private $mappingVarTypes = [
        Variable::TYPE_VARIABLE_INTEGER => ExportedColumn::TYPE_INTEGER,
        Variable::TYPE_VARIABLE_BOOLEAN => ExportedColumn::TYPE_BOOLEAN,
        Variable::TYPE_VARIABLE_IDENTIFIER => ExportedColumn::TYPE_VARCHAR,
        Variable::TYPE_VARIABLE_DURATION => ExportedColumn::TYPE_DECIMAL,
        Variable::TYPE_VARIABLE_FLOAT => ExportedColumn::TYPE_DECIMAL
    ];

    private $mappingFieldsTypes = [
        'Start Date'                => ExportedColumn::TYPE_TIMESTAMP,
        'End Date'                  => ExportedColumn::TYPE_TIMESTAMP,
        'Compilation Time'          => ExportedColumn::TYPE_INTEGER,
        'Start Delivery Execution'  => ExportedColumn::TYPE_TIMESTAMP,
        'End Delivery Execution'    => ExportedColumn::TYPE_TIMESTAMP
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
     * @inheritdoc
     */
    protected function getExporter(array $data)
    {
        foreach ($this->getColumnsToExport() as $columnData) {
            if ($columnData instanceof VariableColumn) {
                $type = $columnData->getColumnType() && isset($this->mappingVarTypes[$columnData->getColumnType()])
                    ? $this->mappingVarTypes[$columnData->getColumnType()]
                    : ExportedColumn::TYPE_VARCHAR;
                $this->mappingFieldsTypes[$columnData->getLabel()] = $type;
            }
        }
        return new SqlExporter($data, $this->mappingFieldsTypes, 'result_table');
    }
}
