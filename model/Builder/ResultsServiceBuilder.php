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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\taoOutcomeUi\model\Builder;

use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\service\ConfigurableService;
use oat\taoOutcomeUi\model\ResultsService;
use oat\taoOutcomeUi\model\Wrapper\ResultServiceWrapper;
use oat\taoResultServer\models\classes\ResultServerService;

class ResultsServiceBuilder extends ConfigurableService
{
    use OntologyAwareTrait;

    public function build(): ResultsService
    {
        $service = $this->getResultServiceWrapper()->getService();

        $resultServerService = $this->getResultServerService();

        $service->setImplementation($resultServerService->getResultStorage());

        return $service;
    }

    private function getResultServiceWrapper(): ResultServiceWrapper
    {
        return $this->getServiceLocator()
            ->get(ResultServiceWrapper::SERVICE_ID);
    }

    private function getResultServerService(): ResultServerService
    {
        return $this->getServiceLocator()
            ->get(ResultServerService::SERVICE_ID);
    }
}
