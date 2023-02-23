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
 * Copyright (c) 2021  (original work) Open Assessment Technologies SA;
 */

namespace oat\taoOutcomeUi\unit\model\Builder;

use oat\generis\test\ServiceManagerMockTrait;
use oat\taoOutcomeUi\model\Builder\ResultsServiceBuilder;
use oat\taoOutcomeUi\model\ResultsService;
use oat\taoOutcomeUi\model\Wrapper\ResultServiceWrapper;
use oat\taoResultServer\models\classes\ResultManagement;
use oat\taoResultServer\models\classes\ResultServerService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ResultsServiceBuilderTest extends TestCase
{
    use ServiceManagerMockTrait;

    /** @var ResultsService */
    private $subject;

    /** @var ResultServiceWrapper|MockObject */
    private $resultServiceWrapper;

    /** @var ResultServerService|MockObject */
    private $resultServerService;

    protected function setUp(): void
    {
        $this->resultServiceWrapper = $this->createMock(ResultServiceWrapper::class);
        $this->resultServerService = $this->createMock(ResultServerService::class);

        $this->subject = new ResultsServiceBuilder();
        $this->subject->setServiceLocator(
            $this->getServiceManagerMock(
                [
                    ResultServiceWrapper::SERVICE_ID => $this->resultServiceWrapper,
                    ResultServerService::SERVICE_ID => $this->resultServerService,
                ]
            )
        );
    }

    public function testBuild(): void
    {
        $resultService = $this->createMock(ResultsService::class);
        $resultManagement = $this->createMock(ResultManagement::class);

        $this->resultServiceWrapper
            ->method('getService')
            ->willReturn($resultService);

        $this->resultServerService
            ->method('getResultStorage')
            ->willReturn($resultManagement);


        $resultService
            ->expects($this->once())
            ->method('setImplementation')
            ->with($resultManagement);

        $this->assertInstanceOf(ResultsService::class, $this->subject->build());
    }
}
