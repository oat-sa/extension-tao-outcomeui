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

use \core_kernel_classes_Resource;
use oat\oatbox\service\ServiceManager;
use oat\taoDelivery\model\execution\ServiceProxy;
use \tao_models_classes_table_Column;
use \tao_models_classes_table_DataProvider;

/**
 * Short description of class
 *
 * @access public
 * @package taoOutcomeUi
 */
class DeliveryExecutionDataProvider implements tao_models_classes_table_DataProvider
{

    const PROP_STARTED_AT = 'started_at';
    const PROP_FINISHED_AT = 'finished_at';
    const PROP_DELIVERY_EXECUTION_ID = 'delivery_execution_id';
    const PROP_USER_ID = 'user_id';

    /**
     * @var array
     */
    public $cache = [];

    /**
     * @param $resources
     * @param $columns
     * @return mixed|void
     * @throws \common_Exception
     * @throws \common_exception_Error
     * @throws \common_exception_NotFound
     */
    public function prepare($resources, $columns)
    {
        $this->cache = [];

        /** @var ServiceProxy $service */
        $service = $this->getServiceLocator()->get(ServiceProxy::SERVICE_ID);
        foreach ($resources as $identifier) {
            $de = $service->getDeliveryExecution($identifier);
            /** @var DeliveryExecutionColumn $column */
            foreach ($columns as $column) {
                switch ($column->getIdentifier()) {
                    case self::PROP_STARTED_AT:
                        //$column->setContextIdentifier($identifier);
                        $this->cache[$identifier][$column->getIdentifier()] = $de->getStartTime() ?: '';
                        break;
                    case self::PROP_FINISHED_AT:
                        //$column->setContextIdentifier($identifier);
                        $this->cache[$identifier][$column->getIdentifier()] = $de->getFinishTime() ?: '';
                        break;
                    case self::PROP_USER_ID:
                        $this->cache[$identifier][$column->getIdentifier()] = $de->getUserIdentifier() ?: '';
                        break;
                    case self::PROP_DELIVERY_EXECUTION_ID:
                        $this->cache[$identifier][$column->getIdentifier()] = $de->getIdentifier() ?: '';
                        break;
                    default:
                        throw new \common_exception_Error('Undefined property ' . $column->getIdentifier());
                }
            }
        }
    }

    /**
     * @param core_kernel_classes_Resource $resource
     * @param tao_models_classes_table_Column $column
     * @return array|string
     */
    public function getValue(core_kernel_classes_Resource $resource, tao_models_classes_table_Column $column)
    {
        $return = [];
        if (
            array_key_exists($resource->getUri(), $this->cache)
            && array_key_exists($column->getIdentifier(), $this->cache[$resource->getUri()])
        ) {
            $return[$resource->getUri()] = [$this->cache[$resource->getUri()][$column->getIdentifier()]];
        } else {
            \common_Logger::d('no data for resource: ' . $resource->getUri() . ' column: ' . $column->getIdentifier());
        }

        return $return;
    }

    protected function getServiceLocator()
    {
        return ServiceManager::getServiceManager();
    }
}
