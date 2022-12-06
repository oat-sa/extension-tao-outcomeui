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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\taoOutcomeUi\model\table\ColumnDataProvider;

use oat\taoOutcomeUi\model\ItemResultStrategy;
use oat\taoOutcomeUi\model\table\ContextTypePropertyColumn;
use oat\taoOutcomeUi\model\table\DeliveryExecutionColumn;
use oat\taoOutcomeUi\model\table\TestCenterColumn;
use oat\taoOutcomeUi\model\table\TraceVariableColumn;
use oat\taoOutcomeUi\model\table\VariableColumn;
use tao_models_classes_table_Column;

class ColumnIdProvider
{
    private ItemResultStrategy $itemResultStrategy;

    public function __construct(ItemResultStrategy $itemResultStrategy)
    {
        $this->itemResultStrategy = $itemResultStrategy;
    }

    public function provide(tao_models_classes_table_Column $column): string
    {
        switch (get_class($column)){
            case ContextTypePropertyColumn::class:
                return $column->getProperty()->getUri() . '_' . $column->getContextType();
            case TestCenterColumn::class:
                return $column->getProperty()->getUri();
            case VariableColumn::class:
                return $this->provideVariableColumnId($column);
            case TraceVariableColumn::class:
            case DeliveryExecutionColumn::class:
                return $column->getContextIdentifier() . '_' . $column->getIdentifier();
            default:
                return $this->createColumnDataHash($column->getLabel());
        }
    }

    public function createColumnDataHash(string ...$elements): string
    {
        return md5(implode($elements));
    }

    private function provideVariableColumnId(VariableColumn $variableColumn): string
    {
        if (!$this->itemResultStrategy->isItemEntityBased()) {
            return $this->createColumnDataHash(
                $variableColumn->getContextIdentifier(),
                $variableColumn->getRefId() ?? '',
                $variableColumn->getIdentifier()
            );
        }

        return $variableColumn->getContextIdentifier() . '_' . $variableColumn->getIdentifier();
    }
}
