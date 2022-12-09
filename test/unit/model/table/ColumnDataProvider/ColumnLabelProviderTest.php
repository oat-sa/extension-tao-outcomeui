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
use oat\taoOutcomeUi\model\table\ColumnDataProvider\ColumnLabelProvider;
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

class ColumnLabelProviderTest extends TestCase
{
    use ServiceManagerMockTrait;

    private const EXPECTED_LABEL_SEPARATOR = '-';
    private const EXPECTED_LABEL = 'label';
    private const EXPECTED_REF_ID = 'ref_id';
    private const EXPECTED_CONTEXT_LABEL = 'context_label';
    private const EXPECTED_IDENTIFIER = 'identifier';

    private const EXPECTED_TT_CONTEXT_TYPE = 'test_taker';
    private const EXPECTED_DELIVERY_CONTEXT_TYPE = 'delivery';
    private const EXPECTED_URI = 'http://tao#rdf';
    private const EXPECTED_CONTEXT_IDENTIFIER = 'context_identifier';
    private const EXPECTED_DE_CONTEXT_IDENTIFIER = 'delivery_execution';
    private const EXPECTED_TRACE_IDENTIFIER = 'trace_variable';
    private const EXPECTED_COLUMN_TYPE = 'column_type';


    /** @var ItemResultStrategy|MockObject */
    private $itemResultStrategyMock;
    private ColumnLabelProvider $subject;

    protected function setUp(): void
    {
        $this->itemResultStrategyMock = $this->createMock(ItemResultStrategy::class);
        $this->subject = new ColumnLabelProvider($this->itemResultStrategyMock);
    }

    public function testProvideForNonVariableColumn(): void
    {
        $this->configureItemResultStrategyMockWithNever();

        $columnMock = $this->createMock(tao_models_classes_table_Column::class);
        $columnMock->expects(self::once())->method('getLabel')->willReturn(self::EXPECTED_LABEL);

        self::assertEquals(
            self::EXPECTED_LABEL,
            $this->subject->provide($columnMock)
        );
    }

    public function testProvideForVariableColumnWithLabelItemRefBasedWithItemRefId(): void
    {
        $columnMock = $this->createMock(VariableColumn::class);
        $columnMock->expects(self::never())->method('getLabel');
        $columnMock
            ->expects(self::exactly(2))
            ->method('getRefId')
            ->willReturn(self::EXPECTED_REF_ID);
        $columnMock
            ->expects(self::exactly(1))
            ->method('getContextLabel')
            ->willReturn(self::EXPECTED_CONTEXT_LABEL);
        $columnMock
            ->expects(self::exactly(1))
            ->method('getIdentifier')
            ->willReturn(self::EXPECTED_IDENTIFIER);

        $this->itemResultStrategyMock
            ->expects(self::once())
            ->method('isItemInstanceLabelItemRefBased')
            ->willReturn(true);
        $this->itemResultStrategyMock->expects(self::never())->method('isItemInstanceItemRefBased');

        self::assertEquals(
            implode(
                self::EXPECTED_LABEL_SEPARATOR,
                [self::EXPECTED_REF_ID, self::EXPECTED_CONTEXT_LABEL, self::EXPECTED_IDENTIFIER]
            ),
            $this->subject->provide($columnMock)
        );
    }

    /**
     * @dataProvider provideVariableColumnClasses
     */
    public function testProvideFromColumnArrayForVariableColumnWithLabelItemRefBasedWithItemRefId(
        $columnClass
    ): void {

        $this->itemResultStrategyMock
            ->expects(self::once())
            ->method('isItemInstanceLabelItemRefBased')
            ->willReturn(true);
        $this->itemResultStrategyMock->expects(self::never())->method('isItemInstanceItemRefBased');

        self::assertEquals(
            implode(
                self::EXPECTED_LABEL_SEPARATOR,
                [self::EXPECTED_REF_ID, self::EXPECTED_CONTEXT_LABEL, self::EXPECTED_IDENTIFIER]
            ),
            $this->subject->provideFromColumnArray([
                'contextId' => self::EXPECTED_CONTEXT_IDENTIFIER,
                'contextLabel' => self::EXPECTED_CONTEXT_LABEL,
                'variableIdentifier' => self::EXPECTED_IDENTIFIER,
                'columnType' => self::EXPECTED_COLUMN_TYPE,
                'refId' => self::EXPECTED_REF_ID,
                'type' => $columnClass
            ])
        );
    }

    public function testProvideForVariableColumnWithLabelItemRefBasedWithoutItemRefId(): void
    {
        $columnMock = $this->createMock(VariableColumn::class);
        $columnMock->expects(self::once())->method('getLabel')->willReturn(self::EXPECTED_LABEL);
        $columnMock
            ->expects(self::exactly(1))
            ->method('getRefId')
            ->willReturn(null);
        $columnMock
            ->expects(self::never())
            ->method('getContextLabel');
        $columnMock
            ->expects(self::never())
            ->method('getIdentifier');

        $this->itemResultStrategyMock
            ->expects(self::once())
            ->method('isItemInstanceLabelItemRefBased')
            ->willReturn(true);
        $this->itemResultStrategyMock
            ->expects(self::once())
            ->method('isItemInstanceItemRefBased')
            ->willReturn(false);

        self::assertEquals(
            self::EXPECTED_LABEL,
            $this->subject->provide($columnMock)
        );
    }

    /**
     * @dataProvider provideVariableColumnClasses
     */
    public function testProvideFromColumnArrayForVariableColumnWithLabelItemRefBasedWithoutItemRefId(
        $columnClass
    ): void {
        $this->itemResultStrategyMock
            ->expects(self::once())
            ->method('isItemInstanceLabelItemRefBased')
            ->willReturn(true);
        $this->itemResultStrategyMock
            ->expects(self::once())
            ->method('isItemInstanceItemRefBased')
            ->willReturn(false);

        self::assertEquals(
            self::EXPECTED_LABEL . '-' . self::EXPECTED_IDENTIFIER,
            $this->subject->provideFromColumnArray([
                'contextId' => self::EXPECTED_CONTEXT_IDENTIFIER,
                'contextLabel' => self::EXPECTED_LABEL,
                'variableIdentifier' => self::EXPECTED_IDENTIFIER,
                'columnType' => self::EXPECTED_COLUMN_TYPE,
                'type' => $columnClass
            ])
        );
    }

    public function testProvideForVariableColumnWithItemRefBasedWithItemRefId(): void
    {
        $columnMock = $this->createMock(VariableColumn::class);
        $columnMock->expects(self::never())->method('getLabel');
        $columnMock
            ->expects(self::exactly(2))
            ->method('getRefId')
            ->willReturn(self::EXPECTED_REF_ID);
        $columnMock
            ->expects(self::never())
            ->method('getContextLabel');
        $columnMock
            ->expects(self::exactly(1))
            ->method('getIdentifier')
            ->willReturn(self::EXPECTED_IDENTIFIER);

        $this->itemResultStrategyMock
            ->expects(self::once())
            ->method('isItemInstanceLabelItemRefBased')
            ->willReturn(false);
        $this->itemResultStrategyMock
            ->expects(self::once())
            ->method('isItemInstanceItemRefBased')
            ->willReturn(true);

        self::assertEquals(
            implode(
                self::EXPECTED_LABEL_SEPARATOR,
                [self::EXPECTED_REF_ID, self::EXPECTED_IDENTIFIER]
            ),
            $this->subject->provide($columnMock)
        );
    }

    /**
     * @dataProvider provideVariableColumnClasses
     */
    public function testProvideFromColumnArrayForVariableColumnWithItemRefBasedWithItemRefId(
        $columnClass
    ): void {
        $this->itemResultStrategyMock
            ->expects(self::once())
            ->method('isItemInstanceLabelItemRefBased')
            ->willReturn(false);
        $this->itemResultStrategyMock
            ->expects(self::once())
            ->method('isItemInstanceItemRefBased')
            ->willReturn(true);

        self::assertEquals(
            self::EXPECTED_REF_ID . '-' . self::EXPECTED_IDENTIFIER,
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

    public function testProvideForVariableColumnWithItemRefBasedWithoutItemRefId(): void
    {
        $columnMock = $this->createMock(VariableColumn::class);
        $columnMock->expects(self::once())->method('getLabel')->willReturn(self::EXPECTED_LABEL);
        $columnMock
            ->expects(self::exactly(1))
            ->method('getRefId')
            ->willReturn(null);
        $columnMock
            ->expects(self::never())
            ->method('getContextLabel');
        $columnMock
            ->expects(self::never())
            ->method('getIdentifier');

        $this->itemResultStrategyMock
            ->expects(self::once())
            ->method('isItemInstanceLabelItemRefBased')
            ->willReturn(false);
        $this->itemResultStrategyMock
            ->expects(self::once())
            ->method('isItemInstanceItemRefBased')
            ->willReturn(true);

        self::assertEquals(
            self::EXPECTED_LABEL,
            $this->subject->provide($columnMock)
        );
    }

    /**
     * @dataProvider provideVariableColumnClasses
     */
    public function testProvideFromColumnArrayForVariableColumnWithItemRefBasedWithoutItemRefId(
        $columnClass
    ): void {
        $this->itemResultStrategyMock
            ->expects(self::once())
            ->method('isItemInstanceLabelItemRefBased')
            ->willReturn(false);
        $this->itemResultStrategyMock
            ->expects(self::once())
            ->method('isItemInstanceItemRefBased')
            ->willReturn(true);

        self::assertEquals(
            self::EXPECTED_LABEL . '-' . self::EXPECTED_IDENTIFIER,
            $this->subject->provideFromColumnArray([
                'contextId' => self::EXPECTED_CONTEXT_IDENTIFIER,
                'contextLabel' => self::EXPECTED_LABEL,
                'variableIdentifier' => self::EXPECTED_IDENTIFIER,
                'columnType' => self::EXPECTED_COLUMN_TYPE,
                'type' => $columnClass
            ])
        );
    }

    /**
     * @dataProvider provideNonVariableColumnArrays
     */
    public function testProvideFromColumnArrayForNonVariableColumn(string $expectedLabel, array $columnArray): void
    {
        $this->configureItemResultStrategyMockWithNever();
        $this->preparePropertyColumnOntology(self::EXPECTED_URI);

        self::assertEquals(
            $expectedLabel,
            $this->subject->provideFromColumnArray($columnArray)
        );
    }

    /**
     * @dataProvider provideColumnDataLabel
     */
    public function testCreateLabel(string $expected, array $elements): void
    {
        self::assertEquals(
            $expected,
            $this->subject->createLabel(...$elements)
        );
    }

    public function provideNonVariableColumnArrays(): array
    {
        return [
            [
                self::EXPECTED_URI,
                [
                    'contextType' => self::EXPECTED_TT_CONTEXT_TYPE,
                    'prop' => self::EXPECTED_URI,
                    'type' => ContextTypePropertyColumn::class
                ]
            ], [
                self::EXPECTED_URI,
                [
                    'contextType' => self::EXPECTED_DELIVERY_CONTEXT_TYPE,
                    'prop' => self::EXPECTED_URI,
                    'type' => ContextTypePropertyColumn::class
                ]
            ], [
                self::EXPECTED_URI, [
                    'prop' => self::EXPECTED_URI,
                    'type' => TestCenterColumn::class
                ]
            ], [
                self::EXPECTED_LABEL, [
                    'label' => self::EXPECTED_LABEL,
                    'type' => TraceVariableColumn::class
                ]
            ], [
                self::EXPECTED_LABEL, [
                    'label' => self::EXPECTED_LABEL,
                    'variableIdentifier' => self::EXPECTED_IDENTIFIER,
                    'type' => DeliveryExecutionColumn::class
                ]
            ]
        ];
    }

    public function provideVariableColumnClasses(): array
    {
        return [
            [GradeColumn::class],
            [ResponseColumn::class]
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

    public function provideColumnDataLabel(): array
    {
        return [
            ['one', ['one']],
            ['one-two', ['one', 'two']],
            ['one-two-three', ['one', 'two', 'three']],
            ['one-two-three-four', ['one', 'two', 'three', 'four']]
        ];
    }
}
