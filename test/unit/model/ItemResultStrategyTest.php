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
 * Copyright (c) 2019  (original work) Open Assessment Technologies SA;
 */

namespace oat\taoOutcomeUi\unit\model;

use oat\generis\test\TestCase;
use oat\taoOutcomeUi\model\ItemResultStrategy;

class ItemResultStrategyTest extends TestCase
{
    private const EXPECTED_ITEM_ENTITY_STRATEGY = 'item_entity';
    private const EXPECTED_ITEM_INSTANCE_LABEL_STRATEGY = 'item_instance_label';
    private const EXPECTED_ITEM_INSTANCE_ITEM_REF_STRATEGY = 'item_instance_item_ref';
    private const EXPECTED_ITEM_INSTANCE_LABEL_ITEM_REF_STRATEGY = 'item_instance_label_item_ref';

    public function testItemEntityStrategy(): void
    {
        $subject = $this->createSubject(self::EXPECTED_ITEM_ENTITY_STRATEGY);
        self::assertTrue($subject->isItemEntityBased());
        self::assertFalse($subject->isItemInstanceItemRefBased());
        self::assertFalse($subject->isItemInstanceLabelItemRefBased());
        self::assertFalse($subject->isItemInstanceLabelBased());
    }

    public function testItemInstanceItemRefStrategy(): void
    {
        $subject = $this->createSubject(self::EXPECTED_ITEM_INSTANCE_ITEM_REF_STRATEGY);
        self::assertFalse($subject->isItemEntityBased());
        self::assertTrue($subject->isItemInstanceItemRefBased());
        self::assertFalse($subject->isItemInstanceLabelItemRefBased());
        self::assertFalse($subject->isItemInstanceLabelBased());
    }

    public function testItemInstanceLabelStrategy(): void
    {
        $subject = $this->createSubject(self::EXPECTED_ITEM_INSTANCE_LABEL_STRATEGY);
        self::assertFalse($subject->isItemEntityBased());
        self::assertFalse($subject->isItemInstanceItemRefBased());
        self::assertFalse($subject->isItemInstanceLabelItemRefBased());
        self::assertTrue($subject->isItemInstanceLabelBased());
    }

    public function testItemInstanceItemRefLabelStrategy(): void
    {
        $subject = $this->createSubject(self::EXPECTED_ITEM_INSTANCE_LABEL_ITEM_REF_STRATEGY);
        self::assertFalse($subject->isItemEntityBased());
        self::assertFalse($subject->isItemInstanceItemRefBased());
        self::assertTrue($subject->isItemInstanceLabelItemRefBased());
        self::assertFalse($subject->isItemInstanceLabelBased());
    }

    private function createSubject(string $strategy): ItemResultStrategy
    {
        return new ItemResultStrategy($strategy);
    }
}
