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

namespace oat\taoOutcomeUi\unit\model\table\ColumnDataProvider;

use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use core_kernel_persistence_ResourceInterface;
use oat\generis\model\data\Ontology;
use oat\generis\model\data\RdfsInterface;
use oat\generis\model\OntologyRdfs;
use oat\generis\test\ServiceManagerMockTrait;
use oat\oatbox\service\ServiceManager;
use oat\taoOutcomeUi\model\ItemResultStrategy;
use oat\taoOutcomeUi\model\table\ColumnDataProvider\ColumnIdProvider;
use oat\taoOutcomeUi\model\table\ContextTypePropertyColumn;
use oat\taoOutcomeUi\model\table\DeliveryExecutionColumn;
use oat\taoOutcomeUi\model\table\GradeColumn;
use oat\taoOutcomeUi\model\table\ResponseColumn;
use oat\taoOutcomeUi\model\table\TestCenterColumn;
use oat\taoOutcomeUi\model\table\TraceVariableColumn;
use oat\taoOutcomeUi\model\table\VariableColumn;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use tao_models_classes_table_Column;

class ColumnIdProviderTest extends TestCase
{
    use ServiceManagerMockTrait;

    private const EXPECTED_LABEL = 'label';
    private const EXPECTED_TT_CONTEXT_TYPE = 'test_taker';
    private const EXPECTED_DELIVERY_CONTEXT_TYPE = 'delivery';
    private const EXPECTED_URI = 'http://tao#rdf';
    private const EXPECTED_CONTEXT_IDENTIFIER = 'context_identifier';
    private const EXPECTED_IDENTIFIER = 'identifier';
    private const EXPECTED_REF_ID = 'ref_id';
    private const EXPECTED_DE_CONTEXT_IDENTIFIER = 'delivery_execution';
    private const EXPECTED_TRACE_IDENTIFIER = 'trace_variable';
    private const EXPECTED_COLUMN_TYPE = 'column_type';


    private ColumnIdProvider $subject;
    /** @var ItemResultStrategy|MockObject */
    private $itemResultStrategyMock;

    protected function setUp(): void
    {
        $this->itemResultStrategyMock = $this->createMock(ItemResultStrategy::class);
        $this->subject = new ColumnIdProvider($this->itemResultStrategyMock);
    }

    public function testProvideForBaseColumn(): void
    {
        $this->configureItemResultStrategyMockWithNever();

        $baseColumnMock = $this->createMock(tao_models_classes_table_Column::class);
        $baseColumnMock->expects(self::once())->method('getLabel')->willReturn(self::EXPECTED_LABEL);

        self::assertEquals(md5(self::EXPECTED_LABEL), $this->subject->provide($baseColumnMock));
    }

    public function testProvideForContextTypePropertyColumn(): void
    {
        $this->configureItemResultStrategyMockWithNever();

        $contextTypePropertyColumnMock = $this->createMock(ContextTypePropertyColumn::class);
        $propertyMock = $this->createMock(core_kernel_classes_Property::class);
        $contextTypePropertyColumnMock
            ->expects(self::once())
            ->method('getContextType')
            ->willReturn(self::EXPECTED_TT_CONTEXT_TYPE);
        $contextTypePropertyColumnMock
            ->expects(self::once())
            ->method('getProperty')
            ->willReturn($propertyMock);
        $propertyMock->expects(self::once())->method('getUri')->willReturn(self::EXPECTED_URI);

        $expectedColumnId = sprintf(
            '%s_%s',
            self::EXPECTED_URI,
            self::EXPECTED_TT_CONTEXT_TYPE
        );
        self::assertEquals($expectedColumnId, $this->subject->provide($contextTypePropertyColumnMock));
    }

    /**
     * @dataProvider provideContextTypes
     */
    public function testProvideFromColumnArrayForContextTypePropertyColumn(string $expectedContextType): void
    {
        $this->configureItemResultStrategyMockWithNever();

        $expectedColumnId = sprintf(
            '%s_%s',
            self::EXPECTED_URI,
            $expectedContextType
        );
        $this->preparePropertyColumnOntology(self::EXPECTED_URI);

        self::assertEquals(
            $expectedColumnId,
            $this->subject->provideFromColumnArray([
                'contextType' => $expectedContextType,
                'prop' => self::EXPECTED_URI,
                'type' => ContextTypePropertyColumn::class
            ])
        );
    }

    public function testProvideForTestCenterColumn(): void
    {
        $this->configureItemResultStrategyMockWithNever();

        $testCenterColumnMock = $this->createMock(TestCenterColumn::class);
        $propertyMock = $this->createMock(core_kernel_classes_Property::class);
        $testCenterColumnMock
            ->expects(self::once())
            ->method('getProperty')
            ->willReturn($propertyMock);
        $propertyMock->expects(self::once())->method('getUri')->willReturn(self::EXPECTED_URI);

        self::assertEquals(self::EXPECTED_URI, $this->subject->provide($testCenterColumnMock));
    }

    public function testProvideFromColumnArrayForTestCenterColumn(): void
    {
        $this->configureItemResultStrategyMockWithNever();

        $this->preparePropertyColumnOntology(self::EXPECTED_URI);

        self::assertEquals(
            self::EXPECTED_URI,
            $this->subject->provideFromColumnArray([
                'prop' => self::EXPECTED_URI,
                'type' => TestCenterColumn::class
            ])
        );
    }

    public function testProvideForTraceVariableColumn(): void
    {
        $this->configureItemResultStrategyMockWithNever();

        $traceVariableColumnMock = $this->createMock(TraceVariableColumn::class);
        $traceVariableColumnMock
            ->expects(self::once())
            ->method('getContextIdentifier')
            ->willReturn(self::EXPECTED_CONTEXT_IDENTIFIER);
        $traceVariableColumnMock
            ->expects(self::once())
            ->method('getIdentifier')
            ->willReturn(self::EXPECTED_IDENTIFIER);

        $expectedColumnId = sprintf(
            '%s_%s',
            self::EXPECTED_CONTEXT_IDENTIFIER,
            self::EXPECTED_IDENTIFIER
        );
        self::assertEquals($expectedColumnId, $this->subject->provide($traceVariableColumnMock));
    }

    public function testProvideFromColumnArrayForTraceVariableColumn(): void
    {
        $this->configureItemResultStrategyMockWithNever();

        $expectedColumnId = sprintf(
            '%s_%s',
            self::EXPECTED_DE_CONTEXT_IDENTIFIER,
            self::EXPECTED_TRACE_IDENTIFIER
        );

        self::assertEquals(
            $expectedColumnId,
            $this->subject->provideFromColumnArray([
                'label' => self::EXPECTED_LABEL,
                'type' => TraceVariableColumn::class
            ])
        );
    }

    public function testProvideForDeliveryExecutionColumn(): void
    {
        $this->configureItemResultStrategyMockWithNever();

        $deliveryExecutionColumnMock = $this->createMock(DeliveryExecutionColumn::class);
        $deliveryExecutionColumnMock
            ->expects(self::once())
            ->method('getContextIdentifier')
            ->willReturn(self::EXPECTED_CONTEXT_IDENTIFIER);
        $deliveryExecutionColumnMock
            ->expects(self::once())
            ->method('getIdentifier')
            ->willReturn(self::EXPECTED_IDENTIFIER);

        $expectedColumnId = sprintf(
            '%s_%s',
            self::EXPECTED_CONTEXT_IDENTIFIER,
            self::EXPECTED_IDENTIFIER
        );
        self::assertEquals($expectedColumnId, $this->subject->provide($deliveryExecutionColumnMock));
    }

    public function testProvideFromColumnArrayForDeliveryExecutionColumn(): void
    {
        $this->configureItemResultStrategyMockWithNever();

        $expectedColumnId = sprintf(
            '%s_%s',
            self::EXPECTED_DE_CONTEXT_IDENTIFIER,
            self::EXPECTED_IDENTIFIER
        );

        self::assertEquals(
            $expectedColumnId,
            $this->subject->provideFromColumnArray([
                'label' => self::EXPECTED_LABEL,
                'variableIdentifier' => self::EXPECTED_IDENTIFIER,
                'type' => DeliveryExecutionColumn::class
            ])
        );
    }

    public function testProvideForVariableColumnWithItemEntityStrategy(): void
    {
        $this->itemResultStrategyMock
            ->expects(self::once())
            ->method('isItemEntityBased')
            ->willReturn(true);
        $variableColumnMock = $this->createMock(VariableColumn::class);
        $variableColumnMock
            ->expects(self::once())
            ->method('getContextIdentifier')
            ->willReturn(self::EXPECTED_CONTEXT_IDENTIFIER);
        $variableColumnMock
            ->expects(self::once())
            ->method('getIdentifier')
            ->willReturn(self::EXPECTED_IDENTIFIER);

        $expectedColumnId = sprintf(
            '%s_%s',
            self::EXPECTED_CONTEXT_IDENTIFIER,
            self::EXPECTED_IDENTIFIER
        );

        self::assertEquals($expectedColumnId, $this->subject->provide($variableColumnMock));
    }

    /**
     * @dataProvider provideVariableColumnClasses
     */
    public function testProvideFromColumnArrayForVariableColumnWithItemEntityStrategy(string $columnClass): void
    {
        $this->itemResultStrategyMock
            ->expects(self::once())
            ->method('isItemEntityBased')
            ->willReturn(true);
        $expectedColumnId = sprintf(
            '%s_%s',
            self::EXPECTED_CONTEXT_IDENTIFIER,
            self::EXPECTED_IDENTIFIER
        );

        self::assertEquals(
            $expectedColumnId,
            $this->subject->provideFromColumnArray([
                'contextId' => self::EXPECTED_CONTEXT_IDENTIFIER,
                'contextLabel' => self::EXPECTED_LABEL,
                'variableIdentifier' => self::EXPECTED_IDENTIFIER,
                'columnType' => self::EXPECTED_COLUMN_TYPE,
                'refId' => self::EXPECTED_REF_ID,
                'type' => $columnClass
            ])
        );
    }

    public function testProvideForVariableColumnWithNotItemEntityStrategyWithRefId(): void
    {
        $this->itemResultStrategyMock
            ->expects(self::once())
            ->method('isItemEntityBased')
            ->willReturn(false);
        $variableColumnMock = $this->createMock(VariableColumn::class);
        $variableColumnMock
            ->expects(self::once())
            ->method('getContextIdentifier')
            ->willReturn(self::EXPECTED_CONTEXT_IDENTIFIER);
        $variableColumnMock
            ->expects(self::once())
            ->method('getIdentifier')
            ->willReturn(self::EXPECTED_IDENTIFIER);

        $variableColumnMock
            ->expects(self::once())
            ->method('getRefId')
            ->willReturn(self::EXPECTED_REF_ID);

        self::assertEquals(
            md5(self::EXPECTED_CONTEXT_IDENTIFIER . self::EXPECTED_REF_ID . self::EXPECTED_IDENTIFIER),
            $this->subject->provide($variableColumnMock)
        );
    }

    /**
     * @dataProvider provideVariableColumnClasses
     */
    public function testProvideFromColumnArrayForVariableColumnWithNotItemEntityStrategyWithRefId(
        string $columnClass
    ): void {
        $this->itemResultStrategyMock
            ->expects(self::once())
            ->method('isItemEntityBased')
            ->willReturn(false);

        self::assertEquals(
            md5(self::EXPECTED_CONTEXT_IDENTIFIER . self::EXPECTED_REF_ID . self::EXPECTED_IDENTIFIER),
            $this->subject->provideFromColumnArray([
                'contextId' => self::EXPECTED_CONTEXT_IDENTIFIER,
                'contextLabel' => self::EXPECTED_LABEL,
                'variableIdentifier' => self::EXPECTED_IDENTIFIER,
                'columnType' => self::EXPECTED_COLUMN_TYPE,
                'refId' => self::EXPECTED_REF_ID,
                'type' => $columnClass
            ])
        );
    }

    public function testProvideForVariableColumnWithNotItemEntityStrategyWithoutRefId(): void
    {
        $this->itemResultStrategyMock
            ->expects(self::once())
            ->method('isItemEntityBased')
            ->willReturn(false);
        $variableColumnMock = $this->createMock(VariableColumn::class);
        $variableColumnMock
            ->expects(self::once())
            ->method('getContextIdentifier')
            ->willReturn(self::EXPECTED_CONTEXT_IDENTIFIER);
        $variableColumnMock
            ->expects(self::once())
            ->method('getIdentifier')
            ->willReturn(self::EXPECTED_IDENTIFIER);

        $variableColumnMock
            ->expects(self::once())
            ->method('getRefId')
            ->willReturn(null);

        self::assertEquals(
            md5(self::EXPECTED_CONTEXT_IDENTIFIER . self::EXPECTED_IDENTIFIER),
            $this->subject->provide($variableColumnMock)
        );
    }

    /**
     * @dataProvider provideVariableColumnClasses
     */
    public function testProvideFromColumnArrayForVariableColumnWithNotItemEntityStrategyWithoutRefId(
        string $columnClass
    ): void {
        $this->itemResultStrategyMock
            ->expects(self::once())
            ->method('isItemEntityBased')
            ->willReturn(false);

        self::assertEquals(
            md5(self::EXPECTED_CONTEXT_IDENTIFIER . self::EXPECTED_IDENTIFIER),
            $this->subject->provideFromColumnArray([
                'contextId' => self::EXPECTED_CONTEXT_IDENTIFIER,
                'contextLabel' => self::EXPECTED_LABEL,
                'variableIdentifier' => self::EXPECTED_IDENTIFIER,
                'columnType' => self::EXPECTED_COLUMN_TYPE,
                'type' => $columnClass
            ])
        );
    }

    public function testProvideFromColumnArrayForResponseColumn(): void
    {
        self::assertEquals(
            'context_identifier_identifier',
            $this->subject->provideFromColumnArray([
                'contextId' => self::EXPECTED_CONTEXT_IDENTIFIER,
                'contextLabel' => self::EXPECTED_LABEL,
                'variableIdentifier' => self::EXPECTED_IDENTIFIER,
                'columnType' => self::EXPECTED_COLUMN_TYPE,
                'refId' => self::EXPECTED_REF_ID,
                'type' => ResponseColumn::class
            ])
        );
    }

    /**
     * @dataProvider provideColumnDataHash
     */
    public function testCreateColumnDataHash(string $expected, array $elements): void
    {
        self::assertEquals(
            md5($expected),
            $this->subject->createColumnDataHash(...$elements)
        );
    }

    public function provideColumnDataHash(): array
    {
        return [
            ['one', ['one']],
            ['onetwo', ['one', 'two']],
            ['onetwothree', ['one', 'two', 'three']],
            ['onetwothreefour', ['one', 'two', 'three', 'four']]
        ];
    }

    public function provideContextTypes(): array
    {
        return [[self::EXPECTED_TT_CONTEXT_TYPE], [self::EXPECTED_DELIVERY_CONTEXT_TYPE]];
    }

    public function provideVariableColumnClasses(): array
    {
        return [
            [GradeColumn::class],
        ];
    }

    private function configureItemResultStrategyMockWithNever(): void
    {
        $this->itemResultStrategyMock->expects(self::never())->method('isItemInstanceLabelItemRefBased');
        $this->itemResultStrategyMock->expects(self::never())->method('isItemInstanceItemRefBased');
        $this->itemResultStrategyMock->expects(self::never())->method('isItemEntityBased');
        $this->itemResultStrategyMock->expects(self::never())->method('isItemInstanceLabelBased');
    }

    private function preparePropertyColumnOntology(string $uri): void
    {
        $ontologyMock = $this->createMock(Ontology::class);
        $propertyMock = $this->createMock(core_kernel_classes_Property::class);
        $resourceMock = $this->createMock(core_kernel_classes_Resource::class);
        $resourceMock->method('getUri')->willReturn($uri);
        $ontologyMock
            ->method('getProperty')
            ->with(OntologyRdfs::RDFS_LABEL)
            ->willReturn($propertyMock);
        $ontologyMock->method('getResource')->with($uri)->willReturn($resourceMock);
        $rdfsMock = $this->createMock(RdfsInterface::class);
        $ontologyMock
            ->method('getRdfsInterface')
            ->willReturn($rdfsMock);
        $resourceMock = $this->createMock(core_kernel_persistence_ResourceInterface::class);
        $rdfsMock
            ->method('getResourceImplementation')
            ->willReturn($resourceMock);
        $resourceMock->method('getPropertyValues')->willReturn([$uri]);

        ServiceManager::setServiceManager($this->getServiceManagerMock([
            Ontology::SERVICE_ID => $ontologyMock
        ]));
    }
}
