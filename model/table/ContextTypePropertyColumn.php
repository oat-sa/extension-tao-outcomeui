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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoOutcomeUi\model\table;

/**
 * ContextTypePropertyColumn
 *
 * @author Gyula Szucs <gyula@taotesting.com>
 */
class ContextTypePropertyColumn extends \tao_models_classes_table_PropertyColumn
{
    const CONTEXT_TYPE_TEST_TAKER = 'test_taker';
    const CONTEXT_TYPE_DELIVERY = 'delivery';

    public $contextType;

    /**
     * ContextAwarePropertyColumn constructor.
     *
     * @param string                        $contextType
     * @param \core_kernel_classes_Property $property
     */
    public function __construct($contextType, \core_kernel_classes_Property $property)
    {
        parent::__construct($property);

        $this->setContextType($contextType);
    }

    /**
     * @param array $data
     * @return ContextTypePropertyColumn
     */
    protected static function fromArray($data)
    {
        return new self($data['contextType'], new \core_kernel_classes_Property($data['prop']));
    }

    /**
     * @param string $contextType
     * @throws \common_exception_InvalidArgumentType
     */
    public function setContextType($contextType)
    {
        if (!in_array($contextType, [self::CONTEXT_TYPE_TEST_TAKER, self::CONTEXT_TYPE_DELIVERY])) {
            throw new \common_exception_InvalidArgumentType('Not valid context type "'. $contextType .'"');
        }

        $this->contextType = $contextType;
    }

    /**
     * @return string
     */
    public function getContextType()
    {
        return $this->contextType;
    }

    /**
     * @return bool
     */
    public function isTestTakerType()
    {
        return $this->getContextType() == self::CONTEXT_TYPE_TEST_TAKER;
    }

    /**
     * @return bool
     */
    public function isDeliveryType()
    {
        return $this->getContextType() == self::CONTEXT_TYPE_DELIVERY;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $result = parent::toArray();

        $result['contextType'] = $this->getContextType();

        return $result;
    }
}