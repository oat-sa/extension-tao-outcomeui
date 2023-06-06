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

use core_kernel_classes_Resource;
use oat\generis\test\ServiceManagerMockTrait;
use oat\generis\test\TestCase;
use oat\taoOutcomeUi\model\ItemResultStrategy;
use oat\taoOutcomeUi\model\ResultsService;
use oat\taoOutcomeUi\model\table\ColumnDataProvider\ColumnIdProvider;
use oat\taoOutcomeUi\model\table\GradeColumn;
use oat\taoOutcomeUi\model\table\ResponseColumn;
use oat\taoOutcomeUi\model\Wrapper\ResultServiceWrapper;
use oat\taoQtiTest\models\QtiTestCompilerIndex;
use oat\taoResultServer\models\classes\ResultServerService;
use ReflectionClass;
use ReflectionException;
use stdClass;
use taoResultServer_models_classes_OutcomeVariable;
use taoResultServer_models_classes_ResponseVariable;
use oat\taoResultServer\models\classes\ResultManagement;
use taoResultServer_models_classes_Variable as Variable;

class ResultsServiceTest extends TestCase
{
    use ServiceManagerMockTrait;

    private const EXPECTED_DELIVERY_URI = 'http://tao#delivery';
    private const EXPECTED_ITEM_URI = 'http://tao#item';
    private const EXPECTED_TEST_URI = 'http://tao#item';
    private const EXPECTED_DE_URI = 'http://tao#delivery_execution';

    /** @var ResultsService */
    private $subject;

    protected function setUp(): void
    {
        if (!defined('DEFAULT_LANG')) {
            define('DEFAULT_LANG', 'en_ES');
        }
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
    public function testExtractTestVariables(
        array $expectedOutput,
        array $variableObjects,
        array $wantedTypes,
        string $filter = null
    ) {
        if ($filter === null) {
            $this->assertSame($expectedOutput, $this->subject->extractTestVariables($variableObjects, $wantedTypes));
        } else {
            $this->assertSame(
                $expectedOutput,
                $this->subject->extractTestVariables($variableObjects, $wantedTypes, $filter)
            );
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
        $callIdItem = 'https://sds-tao.docker.localhost/ontologies/tao.rdf#i5e43f610586e6866601988b391f3a4.item-1.0';
        return [
            [
                [
                    $responseVariable,
                    $outcomeVariable,
                ],
                [
                    [(object)[
                        'callIdItem' => $callIdItem,
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
                        'callIdItem' => $callIdItem,
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
                        'callIdItem' => $callIdItem,
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
                        'callIdItem' => $callIdItem,
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
                'delivery_execution_id',
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
                'delivery_execution_id',
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
                'delivery_execution_id',
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
                'delivery_execution_id',
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
                'delivery_execution_id',
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
    public function testFilterData($expected, $row, $filters, $deliveryExecutionIdentifier)
    {
        $class = new ReflectionClass(ResultsService::class);
        $method = $class->getMethod('filterData');
        $method->setAccessible(true);
        $resultsService = new ResultsService();
        self::assertSame(
            $expected,
            $method->invokeArgs($resultsService, [$row, $filters, $deliveryExecutionIdentifier])
        );
    }

    public function testGetCellsByResults()
    {
        $service = new ResultsService();
        $this->assertEquals(null, $service->getCellsByResults([], [], []));
    }

    /**
     * @dataProvider provideVariableColumnMap
     */
    public function testGetVariableColumnsWithoutVariablesInStorage(string $variableClass): void
    {
        $uri = 'http://tao#delivery';
        $delivery = $this->createMock(core_kernel_classes_Resource::class);
        $resultServiceWrapperMock = $this->createMock(ResultServiceWrapper::class);
        $resultServerServiceMock = $this->createMock(ResultServerService::class);
        $resultManagementMock = $this->createMock(ResultManagement::class);
        $itemResultStrategyMock = $this->createMock(ItemResultStrategy::class);

        $delivery->method('getUri')->willReturn($uri);
        $resultManagementMock
            ->method('getResultByDelivery')
            ->with([$uri], [])
            ->willReturn([['deliveryResultIdentifier' => 'http://tao#delivery_execution']]);
        $resultManagementMock->method('getDeliveryVariables')->willReturn([]);
        $resultServerServiceMock
            ->expects(self::once())
            ->method('getResultStorage')
            ->willReturn($resultManagementMock);
        $resultServiceWrapperMock
            ->expects(self::once())
            ->method('getOption')
            ->with(ResultServiceWrapper::RESULT_COLUMNS_CHUNK_SIZE_OPTION)
            ->willReturn(1);
        $itemResultStrategyMock->expects(self::never())->method('isItemEntityBased');

        $this->subject->setServiceManager($this->getServiceManagerMock([
            ResultServiceWrapper::SERVICE_ID => $resultServiceWrapperMock,
            ResultServerService::SERVICE_ID => $resultServerServiceMock,
            ItemResultStrategy::class => $itemResultStrategyMock
        ]));

        $columns = $this->subject->getVariableColumns(
            $delivery,
            $variableClass
        );
        self::assertEmpty($columns);
    }

    /**
     * @dataProvider  provideVariableColumnMap
     */
    public function testGetVariableColumnsForItemOutcomeVariablesWitNotItemEntityStrategy(
        string $variableClass,
        string $columnClass,
        int $expectedCountForInstanceStrategy,
        int $expectedCountForEntityStrategy,
        array $variables
    ): void {
        $delivery = $this->createMock(core_kernel_classes_Resource::class);
        $resultServiceWrapperMock = $this->createMock(ResultServiceWrapper::class);
        $resultServerServiceMock = $this->createMock(ResultServerService::class);
        $resultManagementMock = $this->createMock(ResultManagement::class);
        $itemResultStrategyMock = $this->createMock(ItemResultStrategy::class);

        $delivery->method('getUri')->willReturn(self::EXPECTED_DELIVERY_URI);
        $resultManagementMock
            ->method('getResultByDelivery')
            ->with([self::EXPECTED_DELIVERY_URI], [])
            ->willReturn([['deliveryResultIdentifier' => self::EXPECTED_DE_URI]]);
        $resultManagementMock
            ->method('getDeliveryVariables')
            ->willReturn($variables);
        $resultServerServiceMock
            ->expects(self::once())
            ->method('getResultStorage')
            ->willReturn($resultManagementMock);
        $resultServiceWrapperMock
            ->expects(self::once())
            ->method('getOption')
            ->with(ResultServiceWrapper::RESULT_COLUMNS_CHUNK_SIZE_OPTION)
            ->willReturn(1);
        $itemResultStrategyMock
            ->method('isItemEntityBased')
            ->willReturn(false);

        $this->subject->setServiceManager($this->getServiceManagerMock([
            ResultServiceWrapper::SERVICE_ID => $resultServiceWrapperMock,
            ResultServerService::SERVICE_ID => $resultServerServiceMock,
            ItemResultStrategy::class => $itemResultStrategyMock,
            ColumnIdProvider::class => new ColumnIdProvider($itemResultStrategyMock)
        ]));
        $reflectionClass = new ReflectionClass($this->subject);
        $reflectionProperty = $reflectionClass->getProperty('indexerCache');
        $indexerCacheMock = $this->createMock(QtiTestCompilerIndex::class);
        $indexerCacheMock
            ->method('getItemValue')
            ->with(self::EXPECTED_ITEM_URI, DEFAULT_LANG, 'label')
            ->willReturn('label');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->subject, [self::EXPECTED_DELIVERY_URI => $indexerCacheMock]);

        $columns = $this->subject->getVariableColumns(
            $delivery,
            $variableClass
        );

        self::assertCount($expectedCountForInstanceStrategy, $columns);
        $expectedIndex = 0;
        foreach ($columns as $index => $column) {
            self::assertEquals($expectedIndex, $index);
            self::assertEquals($columnClass, $column['type']);
            $expectedIndex++;
        }
    }

    /**
     * @dataProvider  provideVariableColumnMap
     */
    public function testGetVariableColumnsForItemOutcomeVariablesWitItemEntityStrategy(
        string $variableClass,
        string $columnClass,
        int $expectedCountForInstanceStrategy,
        int $expectedCountForEntityStrategy,
        array $variables
    ): void {
        $delivery = $this->createMock(core_kernel_classes_Resource::class);
        $resultServiceWrapperMock = $this->createMock(ResultServiceWrapper::class);
        $resultServerServiceMock = $this->createMock(ResultServerService::class);
        $resultManagementMock = $this->createMock(ResultManagement::class);
        $itemResultStrategyMock = $this->createMock(ItemResultStrategy::class);

        $delivery->method('getUri')->willReturn(self::EXPECTED_DELIVERY_URI);
        $resultManagementMock
            ->method('getResultByDelivery')
            ->with([self::EXPECTED_DELIVERY_URI], [])
            ->willReturn([['deliveryResultIdentifier' => self::EXPECTED_DE_URI]]);
        $resultManagementMock
            ->method('getDeliveryVariables')
            ->willReturn($variables);
        $resultServerServiceMock
            ->expects(self::once())
            ->method('getResultStorage')
            ->willReturn($resultManagementMock);
        $resultServiceWrapperMock
            ->expects(self::once())
            ->method('getOption')
            ->with(ResultServiceWrapper::RESULT_COLUMNS_CHUNK_SIZE_OPTION)
            ->willReturn(1);
        $itemResultStrategyMock
            ->method('isItemEntityBased')
            ->willReturn(true);

        $this->subject->setServiceManager($this->getServiceManagerMock([
            ResultServiceWrapper::SERVICE_ID => $resultServiceWrapperMock,
            ResultServerService::SERVICE_ID => $resultServerServiceMock,
            ItemResultStrategy::class => $itemResultStrategyMock,
            ColumnIdProvider::class => new ColumnIdProvider($itemResultStrategyMock)
        ]));
        $reflectionClass = new ReflectionClass($this->subject);
        $reflectionProperty = $reflectionClass->getProperty('indexerCache');
        $indexerCacheMock = $this->createMock(QtiTestCompilerIndex::class);
        $indexerCacheMock
            ->method('getItemValue')
            ->with(self::EXPECTED_ITEM_URI, DEFAULT_LANG, 'label')
            ->willReturn('label');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->subject, [self::EXPECTED_DELIVERY_URI => $indexerCacheMock]);

        $columns = $this->subject->getVariableColumns(
            $delivery,
            $variableClass
        );
        self::assertCount($expectedCountForEntityStrategy, $columns);
        $expectedIndex = 0;
        foreach ($columns as $index => $column) {
            self::assertEquals($expectedIndex, $index);
            self::assertEquals($columnClass, $column['type']);
            $expectedIndex++;
        }
    }


    public function provideVariableColumnMap(): array
    {
        return [
            [
                taoResultServer_models_classes_OutcomeVariable::class,
                GradeColumn::class,
                2,
                1,
                [
                    [(object)[
                        'callIdItem' => sprintf('%s.%s.0', self::EXPECTED_DE_URI, 'item-1'),
                        'item' => self::EXPECTED_ITEM_URI,
                        'test' => self::EXPECTED_TEST_URI,
                        'deliveryResultIdentifier' => self::EXPECTED_DE_URI,
                        'variable' => $this->getOutcomeVariable()
                    ]],
                    [(object)[
                        'callIdItem' => sprintf('%s.%s.0', self::EXPECTED_DE_URI, 'item-2'),
                        'item' => self::EXPECTED_ITEM_URI,
                        'test' => self::EXPECTED_TEST_URI,
                        'deliveryResultIdentifier' => self::EXPECTED_DE_URI,
                        'variable' => $this->getOutcomeVariable()
                    ]]
                ]
            ],
            [
                taoResultServer_models_classes_ResponseVariable::class,
                ResponseColumn::class,
                3,
                3,
                [
                    [(object)[
                        'callIdItem' => sprintf('%s.%s.0', self::EXPECTED_DE_URI, 'item-1'),
                        'item' => self::EXPECTED_ITEM_URI,
                        'test' => self::EXPECTED_TEST_URI,
                        'deliveryResultIdentifier' => self::EXPECTED_DE_URI,
                        'variable' => $this->getResponseVariable('response value', 'response')
                    ]],
                    [(object)[
                        'callIdItem' => sprintf('%s.%s.0', self::EXPECTED_DE_URI, 'item-2'),
                        'item' => self::EXPECTED_ITEM_URI,
                        'test' => self::EXPECTED_TEST_URI,
                        'deliveryResultIdentifier' => self::EXPECTED_DE_URI,
                        'variable' => ($this->getResponseVariable())->setCorrectResponse('correct')
                    ]]
                ]
            ],
        ];
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
