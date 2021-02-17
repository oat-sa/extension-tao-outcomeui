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
 *
 *
 */

namespace oat\taoOutcomeUi\model\table;

use tao_models_classes_table_Column;

/**
 * Short description of class oat\taoOutcomeUi\model\table\TraceVariableColumn
 *
 * @access public
 * @author Sergey Lunev, <sergey.lunev@taotesting.com>
 * @package taoOutcomeUi
 */
class TraceVariableColumn extends tao_models_classes_table_Column
{
    private static $dataProvider;

    protected static function fromArray($array)
    {
        return new self($array['label']);
    }

    public function getDataProvider()
    {
        if (!self::$dataProvider) {
            self::$dataProvider = new TraceVariableDataProvider();
        }
        return self::$dataProvider;
    }

    public function getContextIdentifier()
    {
        return 'delivery_execution';
    }

    public function getIdentifier()
    {
        return 'trace_variable';
    }
}
