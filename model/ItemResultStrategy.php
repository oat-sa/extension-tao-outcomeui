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

namespace oat\taoOutcomeUi\model;

class ItemResultStrategy
{
    public const ENV_ITEM_RESULT_STRATEGY = 'ITEM_RESULT_STRATEGY';

    private const ITEM_ENTITY_STRATEGY = 'item_entity';
    private const ITEM_INSTANCE_LABEL_STRATEGY = 'item_instance_label';
    private const ITEM_INSTANCE_ITEM_REF_STRATEGY = 'item_instance_item_ref';
    private const ITEM_INSTANCE_LABEL_ITEM_REF_STRATEGY = 'item_instance_label_item_ref';

    private string $strategy;

    public function __construct(string $strategy)
    {
        $this->strategy = $strategy;
    }

    public function isItemEntityBased(): bool
    {
        return $this->strategy === self::ITEM_ENTITY_STRATEGY;
    }

    public function isItemInstanceLabelBased(): bool
    {
        return $this->strategy === self::ITEM_INSTANCE_LABEL_STRATEGY;
    }

    public function isItemInstanceItemRefBased(): bool
    {
        return $this->strategy === self::ITEM_INSTANCE_ITEM_REF_STRATEGY;
    }

    public function isItemInstanceLabelItemRefBased(): bool
    {
        return $this->strategy === self::ITEM_INSTANCE_LABEL_ITEM_REF_STRATEGY;
    }
}
