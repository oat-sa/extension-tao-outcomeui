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
use oat\taoOutcomeUi\model\table\VariableColumn;
use tao_models_classes_table_Column;

class ColumnLabelProvider
{
    private const LABEL_SEPARATOR = '-';

    private ItemResultStrategy $itemResultStrategy;

    public function __construct(ItemResultStrategy $itemResultStrategy)
    {
        $this->itemResultStrategy = $itemResultStrategy;
    }

    public function provide(tao_models_classes_table_Column $column): string
    {
        if ($column instanceof VariableColumn) {
            if (
                $this->itemResultStrategy->isItemInstanceLabelItemRefBased()
                && $column->getRefId() !== null
            ) {
                return $this->createLabel(
                    $column->getRefId(),
                    $column->getContextLabel(),
                    $column->getIdentifier()
                );
            }

            if (
                $this->itemResultStrategy->isItemInstanceItemRefBased()
                && $column->getRefId() !== null
            ) {
                return $this->createLabel($column->getRefId(), $column->getIdentifier());
            }
        }

        return $column->getLabel();
    }

    public function provideFromColumnArray(array $columnArray): string
    {
        return $this->provide(tao_models_classes_table_Column::buildColumnFromArray($columnArray));
    }

    public function createLabel(string ...$elements): string
    {
        return implode(self::LABEL_SEPARATOR, $elements);
    }
}
