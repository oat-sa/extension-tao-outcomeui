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
 *
 * @author Oleksandr Zagovorychev <zagovorichev@gmail.com>
 */

namespace oat\taoOutcomeUi\model\table;

/**
 * Represents a delivery executions columns
 *
 * @access public
 */
class DeliveryExecutionColumn extends \tao_models_classes_table_Column
{
    private static $dataProvider;
    private $identifier;

    public function __construct($label, $identifier)
    {
        parent::__construct($label);
        $this->identifier = $identifier;
    }

    /**
     * @param $array
     * @return DeliveryExecutionColumn|\tao_models_classes_table_Column
     * @throws \common_exception_Error
     */
    protected static function fromArray($array)
    {
        if (!array_key_exists('label', $array) || !array_key_exists('variableIdentifier', $array)) {
            throw new \common_exception_Error('Incorrect data description');
        }
        return new self($array['label'], $array['variableIdentifier']);
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function getContextIdentifier()
    {
        return 'delivery_execution';
    }

    public function getDataProvider()
    {
        if (!self::$dataProvider) {
            self::$dataProvider = new DeliveryExecutionDataProvider();
        }
        return self::$dataProvider;
    }

    public function toArray()
    {
        $res = parent::toArray();
        $res['variableIdentifier'] = $this->getIdentifier();
        return $res;
    }
}
