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
use oat\taoOutcomeUi\model\ResultsService;
use ReflectionClass;
use ReflectionException;
use stdClass;
use taoResultServer_models_classes_OutcomeVariable;
use taoResultServer_models_classes_ResponseVariable;
use oat\taoResultServer\models\classes\ResultManagement;
use taoResultServer_models_classes_Variable as Variable;

class ResultsServiceTest extends TestCase
{
    /** @var ResultsService */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new ResultsService();
    }

    /**
     * @dataProvider getVariablesFromObjectResultProvider
     *
     * @param $variables
     * @param $expectedVariablesCount
     *
     * @throws \common_exception_Error
     */
    public function testGetVariablesFromObjectResult($variables, $expectedVariablesCount)
    {
        $mock = $this->getMockBuilder(ResultManagement::class)->getMock();
        $mock->expects($this->once())
            ->method('getVariables')
            ->willReturn($variables);

        $this->subject->setImplementation($mock);

        $return = $this->subject->getVariablesFromObjectResult('itemResultFixture');

        $this->assertCount($expectedVariablesCount, $return);
    }

    public function getVariablesFromObjectResultProvider()
    {
        $variable = new stdClass();
        $variable->variable = new \taoResultServer_models_classes_ResponseVariable();
        $variable->value = '#bar';

        $variable1 = new stdClass();
        $variable1->variable = new \taoResultServer_models_classes_OutcomeVariable();
        $variable1->value = '#bar';

        return [
            [
                [[$variable]], 1,
            ],
            [
                [[$variable, $variable1]], 2,
            ],
            [
                [
                    [$variable],
                    [$variable, $variable1],
                ], 3,
            ],
        ];
    }

    /**
     * @dataProvider testExtractTestVariablesProvider
     */
    public function testExtractTestVariables(array $expectedOutput, array $variableObjects, array $wantedTypes, string $filter = null)
    {
        if ($filter === null) {
            $this->assertSame($expectedOutput, $this->subject->extractTestVariables($variableObjects, $wantedTypes));
        } else {
            $this->assertSame($expectedOutput, $this->subject->extractTestVariables($variableObjects, $wantedTypes, $filter));
        }
    }

    /**
     * @return array
     * @doesNotPerformAssertions
     */
    public function testExtractTestVariablesProvider()
    {
        $responseVariable = $this->getResponseVariable();
        $outcomeVariable = $this->getOutcomeVariable();

        return [
            [
                [
                    $responseVariable,
                    $outcomeVariable,
                ],
                [
                    [(object)[
                        'callIdItem' => 'https://sds-tao.docker.localhost/ontologies/tao.rdf#i5e43f610586e6866601988b391f3a4.item-1.0',
                        'variable' => $outcomeVariable,
                    ]],
                    [(object)[
                        'callIdItem' => null,
                        'variable' => $outcomeVariable,
                    ]],
                    [(object)[
                        'callIdItem' => null,
                        'variable' => $responseVariable,
                    ]],
                ],
                [
                    'taoResultServer_models_classes_OutcomeVariable',
                    'taoResultServer_models_classes_ResponseVariable',
                ],
            ],
            [
                [
                    0 => $responseVariable,
                    1 => $responseVariable,
                    2 => $outcomeVariable,
                    3 => $outcomeVariable,
                ],
                [
                    [(object)[
                        'callIdItem' => 'https://sds-tao.docker.localhost/ontologies/tao.rdf#i5e43f610586e6866601988b391f3a4.item-1.0',
                        'variable' => $outcomeVariable,
                    ]],
                    [(object)[
                        'callIdItem' => null,
                        'variable' => $outcomeVariable,
                    ]],
                    [(object)[
                        'callIdItem' => null,
                        'variable' => $outcomeVariable,
                    ]],
                    [(object)[
                        'callIdItem' => null,
                        'variable' => $responseVariable,
                    ]],
                    [(object)[
                        'callIdItem' => null,
                        'variable' => $responseVariable,
                    ]],
                ],
                [
                    'taoResultServer_models_classes_OutcomeVariable',
                    'taoResultServer_models_classes_ResponseVariable',
                ],
                ResultsService::VARIABLES_FILTER_ALL,
            ],
            [
                [
                    0 => $outcomeVariable,
                    2 => $responseVariable,
                ],
                [
                    [(object)[
                        'callIdItem' => 'https://sds-tao.docker.localhost/ontologies/tao.rdf#i5e43f610586e6866601988b391f3a4.item-1.0',
                        'variable' => $outcomeVariable,
                    ]],
                    [(object)[
                        'callIdItem' => null,
                        'variable' => $outcomeVariable,
                    ]],
                    [(object)[
                        'callIdItem' => null,
                        'variable' => $outcomeVariable,
                    ]],
                    [(object)[
                        'callIdItem' => null,
                        'variable' => $responseVariable,
                    ]],
                    [(object)[
                        'callIdItem' => null,
                        'variable' => $responseVariable,
                    ]],
                ],
                [
                    'taoResultServer_models_classes_OutcomeVariable',
                    'taoResultServer_models_classes_ResponseVariable',
                ],
                ResultsService::VARIABLES_FILTER_LAST_SUBMITTED,
            ],
            [
                [
                    0 => $responseVariable,
                    2 => $outcomeVariable,
                ],
                [
                    [(object)[
                        'callIdItem' => 'https://sds-tao.docker.localhost/ontologies/tao.rdf#i5e43f610586e6866601988b391f3a4.item-1.0',
                        'variable' => $outcomeVariable,
                    ]],
                    [(object)[
                        'callIdItem' => null,
                        'variable' => $outcomeVariable,
                    ]],
                    [(object)[
                        'callIdItem' => null,
                        'variable' => $outcomeVariable,
                    ]],
                    [(object)[
                        'callIdItem' => null,
                        'variable' => $responseVariable,
                    ]],
                    [(object)[
                        'callIdItem' => null,
                        'variable' => $responseVariable,
                    ]],
                ],
                [
                    'taoResultServer_models_classes_OutcomeVariable',
                    'taoResultServer_models_classes_ResponseVariable',
                ],
                ResultsService::VARIABLES_FILTER_FIRST_SUBMITTED,
            ],
        ];
    }

    public function filterDataProvider()
    {
        return [
            // exact time of the action
            [
                //expected
                true,
                // row
                [
                    ResultsService::DELIVERY_EXECUTION_STARTED_AT => [''],
                    // 2019-06-24T15:01:09
                    ResultsService::DELIVERY_EXECUTION_FINISHED_AT => ['0.86410800 1561388469'],
                ],
                // filters
                [
                    ResultsService::FILTER_START_FROM => '',
                    ResultsService::FILTER_START_TO => '',
                    // 2019-06-24T15:01:09
                    ResultsService::FILTER_END_FROM => '1561388469',
                    // 2019-06-24T15:01:09
                    ResultsService::FILTER_END_TO => '1561388469',
                ],
            ],
            // looking for data that is not in the range
            [
                //expected
                false,
                // row
                [
                    ResultsService::DELIVERY_EXECUTION_STARTED_AT => [''],
                    // 2019-06-24T15:01:09
                    ResultsService::DELIVERY_EXECUTION_FINISHED_AT => ['0.86410800 1561388469'],
                ],
                // filters
                [
                    ResultsService::FILTER_START_FROM => '',
                    ResultsService::FILTER_START_TO => '',
                    // 2019-06-25T00:00:00+00:00
                    ResultsService::FILTER_END_FROM => '1561420800',
                    // 2019-06-26T00:00:00+00:00
                    ResultsService::FILTER_END_TO => '1561507200',
                ],
            ],
            // data without finished date but with filters by finished date
            [
                //expected
                false,
                // row
                [
                    ResultsService::DELIVERY_EXECUTION_STARTED_AT => [''],
                    ResultsService::DELIVERY_EXECUTION_FINISHED_AT => [''],
                ],
                // filters
                [
                    ResultsService::FILTER_START_FROM => '',
                    ResultsService::FILTER_START_TO => '',
                    // 2019-06-25T00:00:00+00:00
                    ResultsService::FILTER_END_FROM => '1561420800',
                    // 2019-06-26T00:00:00+00:00
                    ResultsService::FILTER_END_TO => '1561507200',
                ],
            ],
            // all filters matched
            [
                //expected
                true,
                // row
                [
                    // 2019-01-01T00:00:00+00:00
                    ResultsService::DELIVERY_EXECUTION_STARTED_AT => ['0.86410800 1546300800'],
                    // 2019-06-24T15:01:09
                    ResultsService::DELIVERY_EXECUTION_FINISHED_AT => ['0.86410800 1561388469'],
                ],
                // filters
                [
                    // 2018-12-30T00:00:00+00:00
                    ResultsService::FILTER_START_FROM => '1546128000',
                    // 2019-01-01T00:00:01
                    ResultsService::FILTER_START_TO => '1546300801',
                    // 2019-06-24T00:00:00+00:00
                    ResultsService::FILTER_END_FROM => '1561334400',
                    // 2019-06-26T00:00:00+00:00
                    ResultsService::FILTER_END_TO => '1561507200',
                ],
            ],
            // start to is not matched
            [
                //expected
                false,
                // row
                [
                    // 2019-01-01T00:00:00+00:00
                    ResultsService::DELIVERY_EXECUTION_STARTED_AT => ['0.86410800 1546300800'],
                    // 2019-06-24T15:01:09
                    ResultsService::DELIVERY_EXECUTION_FINISHED_AT => ['0.86410800 1561388469'],
                ],
                // filters
                [
                    // 2018-12-30T00:00:00+00:00
                    ResultsService::FILTER_START_FROM => '1546128000',
                    // 2018-12-31T00:00:00
                    ResultsService::FILTER_START_TO => '1546214400',
                    // 2019-06-24T00:00:00+00:00
                    ResultsService::FILTER_END_FROM => '1561334400',
                    // 2019-06-26T00:00:00+00:00
                    ResultsService::FILTER_END_TO => '1561507200',
                ],
            ],
        ];
    }

    /**
     * @dataProvider filterDataProvider
     *
     * @param bool $expected
     * @param array $row
     * @param array $filters
     *
     * @throws ReflectionException
     */
    public function testFilterData($expected, $row, $filters)
    {
        $class = new ReflectionClass(ResultsService::class);
        $method = $class->getMethod('filterData');
        $method->setAccessible(true);
        $resultsService = new ResultsService();
        self::assertSame($expected, $method->invokeArgs($resultsService, [$row, $filters]));
    }

    private function getResponseVariable(
        $candidateResponse = 'UFQ0LjA5MTc1M1M=',
        $identifier = 'duration',
        $cardinality = 'single',
        $baseType = 'float',
        $epoch = '0.79269200 1581512215'
    ): taoResultServer_models_classes_ResponseVariable {
        return (new taoResultServer_models_classes_ResponseVariable())
            ->setCandidateResponse($candidateResponse)
            ->setIdentifier($identifier)
            ->setCardinality($cardinality)
            ->setBaseType($baseType)
            ->setEpoch($epoch);
    }

    private function getOutcomeVariable(
        $value = 'Y29tcGxldGVk',
        $identifier = 'completionStatus',
        $cardinality = 'single',
        $baseType = 'identifier',
        $epoch = '0.96025600 1581512219'
    ): taoResultServer_models_classes_OutcomeVariable {
        return (new taoResultServer_models_classes_OutcomeVariable())
            ->setValue($value)
            ->setIdentifier($identifier)
            ->setCardinality($cardinality)
            ->setBaseType($baseType)
            ->setEpoch($epoch);
    }

    /**
     * @throws ReflectionException
     */
    public function testDefineTypeColumn()
    {
        $class = new ReflectionClass(ResultsService::class);
        $method = $class->getMethod('defineTypeColumn');
        $method->setAccessible(true);
        $resultService = new ResultsService();

        $variable = new \taoResultServer_models_classes_ResponseVariable();
        $variable->setIdentifier('SCORE');
        $variable->setBaseType(Variable::TYPE_VARIABLE_INTEGER);
        $result = $method->invoke($resultService, $variable);
        $this->assertEquals($result, Variable::TYPE_VARIABLE_IDENTIFIER);

        $variable = new \taoResultServer_models_classes_ResponseVariable();
        $variable->setIdentifier('ANY');
        $variable->setBaseType(Variable::TYPE_VARIABLE_INTEGER);
        $result = $method->invoke($resultService, $variable);
        $this->assertEquals($result, Variable::TYPE_VARIABLE_INTEGER);

        $variable = new \taoResultServer_models_classes_ResponseVariable();
        $variable->setIdentifier('ANY');
        $variable->setCorrectResponse(true);
        $variable->setBaseType(Variable::TYPE_VARIABLE_INTEGER);
        $result = $method->invoke($resultService, $variable);
        $this->assertEquals($result, Variable::TYPE_VARIABLE_IDENTIFIER);

        $variable = new \taoResultServer_models_classes_ResponseVariable();
        $variable->setIdentifier('ANY');
        $variable->setCorrectResponse(false);
        $variable->setBaseType(Variable::TYPE_VARIABLE_INTEGER);
        $result = $method->invoke($resultService, $variable);
        $this->assertEquals($result, Variable::TYPE_VARIABLE_IDENTIFIER);

        $variable = new \taoResultServer_models_classes_ResponseVariable();
        $variable->setIdentifier('ANY');
        $variable->setCorrectResponse(null);
        $variable->setBaseType(Variable::TYPE_VARIABLE_INTEGER);
        $result = $method->invoke($resultService, $variable);
        $this->assertEquals($result, Variable::TYPE_VARIABLE_INTEGER);
    }
}
