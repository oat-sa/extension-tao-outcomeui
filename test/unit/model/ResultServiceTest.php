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
 * Copyright (c) 2018 Open Assessment Technologies S.A.
 */

namespace oat\taoOutcomeUi\test\unit\model;


use oat\generis\test\InvokeNonPublicMethodTrait;
use oat\generis\test\TestCase;
use oat\taoOutcomeUi\model\ResultsService;
use oat\taoOutcomeUi\test\unit\model\fixture\ResultServiceTestFixtureTrait;

class ResultServiceTest extends TestCase
{
    use InvokeNonPublicMethodTrait;
    use ResultServiceTestFixtureTrait;

    public function testGetStructuredVariables()
    {
        $mock = $this->getMockBuilder(ResultsService::class)->disableOriginalConstructor()
            ->setMethods(['getOrderedItemVariablesByResultIdentifierAndWantedTypes', 'getItemInfos'])
            ->getMockForAbstractClass();

        $mock->expects($this->any())
            ->method('getItemInfos')
            ->willReturn([
                'itemModel' => '---',
                'label' => 'Associate Things',
                'uri' => 'http://www.taotesting.com/ontologies/ionut.rdf#i1532536164849216',
        ]);

        $mock->expects($this->once())
            ->method('getOrderedItemVariablesByResultIdentifierAndWantedTypes')
            ->willReturn($this->getItemVariableStructureOrdered());

        $structuredVariables = $mock->getStructuredVariables(
            $this->resultIdentifierFixture,
            ResultsService::VARIABLES_FILTER_ALL
        );

        $this->assertEquals(
            $this->getStructuredItemVariableStructureOrdered(),
            $structuredVariables
        );
    }

    public function testGetSortedItemVariablesByEpoch()
    {
        $resultService = ResultsService::singleton();

        $this->assertEquals(
            $this->getItemVariableStructureOrdered(),
            $this->invokeMethod(
                $resultService,
                'getSortedItemVariablesByEpoch',
                [$this->getItemVariableStructureShuffled()]
            )
        );
    }

    /**
     * @param $expected
     * @param $variableA
     * @param $variableB
     *
     * @throws \ReflectionException
     *
     * @dataProvider provideCasesForIsItemVariableAttemptEpochGreater
     */
    public function testIsItemVariableAttemptEpochGreater($expected, $variableA, $variableB)
    {
        $resultService = ResultsService::singleton();

        $this->assertEquals(
            $expected,
            $this->invokeMethod(
                $resultService,
                'isItemVariableAttemptEpochGreater',
                [$variableA, $variableB]
            )
        );
    }

    public function provideCasesForIsItemVariableAttemptEpochGreater()
    {
        $variableA = (object)['variable' => $this->getResponseVariableWithEpoch($this->epochAFixture)];
        $variableB = (object)['variable' => $this->getResponseVariableWithEpoch($this->epochBFixture)];

        return [
            'lessThan' => [
                -1,
                $variableA,
                $variableB,
            ],
            'equeals' => [
                0,
                $variableA,
                $variableA,
            ],
            'greaterThan' => [
                1,
                $variableB,
                $variableA,
            ],
        ];
    }

    /**
     * @param $expected
     * @param $epoch
     *
     * @throws \ReflectionException
     *
     * @dataProvider provideCasesForTestGetUTimeFromEpoch
     */
    public function testGetUTimeFromEpoch($expected, $epoch)
    {
        $resultService = ResultsService::singleton();

        $this->assertEquals(
            $expected,
            $this->invokeMethod(
                $resultService,
                'getUTimeFromEpoch',
                [$epoch]
            )
        );
    }

    public function provideCasesForTestGetUTimeFromEpoch()
    {
        return [
            'variableA' => [
                $this->epochAFloatFixture,
                $this->epochAFixture,
            ],
            'variableB' => [
                $this->epochBFloatFixture,
                $this->epochBFixture,
            ],
        ];
    }

    /**
     * @param $expected
     * @param $filter
     * @param $itemVariable
     *
     * @throws \ReflectionException
     *
     * @dataProvider provideCasesForTestGetFilteredItemVariableAttempts
     */
    public function testGetFilteredItemVariableAttempts($expected, $filter, $itemVariable)
    {
        $resultService = ResultsService::singleton();

        $this->assertEquals(
            $expected,
            $this->invokeMethod(
                $resultService,
                'getFilteredItemVariableAttempts',
                [$filter, $itemVariable]
            )
        );
    }

    public function provideCasesForTestGetFilteredItemVariableAttempts()
    {
        return [
            'all' => [
                $this->getItemVariableAttemptAttemptsOrdered(),
                ResultsService::VARIABLES_FILTER_ALL,
                $this->getItemVariableAttemptAttemptsOrdered(),
            ],
            'first' => [
                [$this->getItemVariableFirstAttemptAttempt()],
                ResultsService::VARIABLES_FILTER_FIRST_SUBMITTED,
                $this->getItemVariableAttemptAttemptsOrdered(),
            ],
            'last' => [
                [$this->getItemVariableThirdAttemptAttempt()],
                ResultsService::VARIABLES_FILTER_LAST_SUBMITTED,
                $this->getItemVariableAttemptAttemptsOrdered(),
            ],
        ];
    }

    public function testFilterVariablesFirstEpoch()
    {
        $resultService = ResultsService::singleton();

        $this->assertEquals(
            $this->getItemVariableFirstAttemptAttempt(),
            $this->invokeMethod(
                $resultService,
                'filterVariablesFirstEpoch',
                [$this->getItemVariableAttemptAttemptsOrdered()]
            )
        );
    }

    public function testFilterVariablesLastEpoch()
    {
        $resultService = ResultsService::singleton();

        $this->assertEquals(
            $this->getItemVariableThirdAttemptAttempt(),
            $this->invokeMethod(
                $resultService,
                'filterVariablesLastEpoch',
                [$this->getItemVariableAttemptAttemptsOrdered()]
            )
        );
    }

    /**
     * @param $expected
     * @param $variable
     *
     * @throws \ReflectionException
     *
     * @dataProvider provideCasesForTestIsCorrectResponse
     */
    public function testIsCorrectResponse($expected, $variable)
    {
        $resultService = ResultsService::singleton();

        $this->assertEquals(
            $expected,
            $this->invokeMethod(
                $resultService,
                'isCorrectResponse',
                [$variable]
            )
        );
    }

    public function provideCasesForTestIsCorrectResponse()
    {
        return [
            'emptyString' => [
                'unscored',
                '',
            ],
            'emptyArray' => [
                'unscored',
                [],
            ],
            'null' => [
                'unscored',
                null,
            ],
            'false' => [
                'unscored',
                null,
            ],
            'validCorrectResponseVariable' => [
                'correct',
                $this->getResponseVariableWithCorrectResponse(1),
            ],
            'validIncorrectResponseVariable' => [
                'incorrect',
                $this->getResponseVariableWithCorrectResponse(0),
            ],
        ];
    }
}
