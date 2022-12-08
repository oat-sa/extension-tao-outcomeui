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

namespace oat\taoOutcomeUi\model\ContainerServiceProvider;

use oat\generis\model\DependencyInjection\ContainerServiceProviderInterface;
use oat\taoOutcomeUi\model\ItemResultStrategy;
use oat\taoOutcomeUi\model\table\ColumnDataProvider\ColumnIdProvider;
use oat\taoOutcomeUi\model\table\ColumnDataProvider\ColumnLabelProvider;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\env;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

class ContainerServiceProvider implements ContainerServiceProviderInterface
{
    private const DEFAULT_ITEM_RESULT_STRATEGY = 'defaultItemResultStrategy';

    public function __invoke(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();
        $parameters = $configurator->parameters();

        $parameters->set(self::DEFAULT_ITEM_RESULT_STRATEGY, 'item_instance_label_item_ref');
        $parameters->set(
            ItemResultStrategy::ENV_ITEM_RESULT_STRATEGY,
            (string)env(ItemResultStrategy::ENV_ITEM_RESULT_STRATEGY)->default(self::DEFAULT_ITEM_RESULT_STRATEGY)
        );

        $services->set(ItemResultStrategy::class)
            ->public()
            ->args([param(ItemResultStrategy::ENV_ITEM_RESULT_STRATEGY)]);

        $services->set(ColumnIdProvider::class)
            ->public()
            ->args([service(ItemResultStrategy::class)]);

        $services->set(ColumnLabelProvider::class)
            ->public()
            ->args([service(ItemResultStrategy::class)]);
    }
}
