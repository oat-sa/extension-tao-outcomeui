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
 * Copyright (c) 2020  (original work) Open Assessment Technologies SA;
 */

namespace oat\taoOutcomeUi\unit\model\export;

use oat\generis\model\data\Ontology;
use oat\generis\test\TestCase;
use oat\taoOutcomeUi\model\export\ColumnsProvider;
use oat\taoOutcomeUi\model\export\SingleDeliverySqlResultsExporter;
use oat\taoOutcomeUi\model\ResultsService;
use oat\taoOutcomeUi\model\table\GradeColumn;
use oat\taoOutcomeUi\model\table\ResponseColumn;
use taoResultServer_models_classes_Variable as Variable;

class SingleDeliverySqlResultExporterTest extends TestCase
{
    private $dataFixture = [
        [
            'Test Taker ID' => 'http://nec-pr.docker.localhost/tao.rdf#i5f16bd028eb6e202ad4b5d184\'f67e22',
            'Compilation Time' => 1594828375,
            'Field Without Type' => 12345
        ],
        [
            'Test Taker ID' => 'http://nec-pr.docker.localhost/tao.rdf#i5f16bd028eb6e202ad4b5d43f67e24',
            'Compilation Time' => 1594828388,
            'Field Without Type' => 33333
        ],
        [
            'Tets Variable Grade Column' => 'http://nec-pr.docker.localhost/tao.rdf#i5f16bd028eb6ee202ae213123121231',
            'Compilation Time' => 159482838228,
            'Field Without Type' => 3311333
        ],
        [
            'Tets Variable Response Column' => 'http://nec-pr.docker.localhost/tao.rdf#i5f16bd028eb6ee202ae11111',
            'Compilation Time' => 1594838228,
            'Field Without Type' => 331111
        ]
    ];

    /**
     * @throws \common_exception_NotFound
     */
    public function testGetExporterAndGetExportData()
    {
        $deliveryMock = $this->createMock(\core_kernel_classes_Resource::class);
        $deliveryMock->method('exists')->willReturn(true);

        $resultServiceMock = $this->createMock(ResultsService::class);

        $columnsProviderMock = $this->createMock(ColumnsProvider::class);

        $modelMock = $this->createMock(Ontology::class);
        $modelMock->expects($this->once())
            ->method('getResource')
            ->willReturn($deliveryMock);

        $singleDeliveryExporter = new SingleDeliverySqlResultExporterMock($modelMock, $deliveryMock, $resultServiceMock, $columnsProviderMock);

        $variableColumnsToExport = [
            new GradeColumn(
                'testGradeContextIdentifier',
                'Tets Variable Grade Column',
                'testGradeIdentifier',
                Variable::TYPE_VARIABLE_IDENTIFIER),
            new ResponseColumn(
                'testResponseContextIdentifier',
                'Tets Variable Response Column',
                'testResponseIdentifier',
                'nonexistent type')
        ];
        $singleDeliveryExporter->setFixtureColumnsToExport($variableColumnsToExport);

        $exporter = $singleDeliveryExporter->getExporterMock($this->dataFixture);

        $sqlExpected = file_get_contents('taoOutcomeUi/test/unit/model/export/sqlFixture.sql');

        $sql = $singleDeliveryExporter->getExportDataMock($exporter);

        $this->assertEquals($sqlExpected, $sql);
    }

}

class SingleDeliverySqlResultExporterMock extends SingleDeliverySqlResultsExporter
{
    private $columns = [];

    public function __construct($ontology, $delivery, ResultsService $resultsService, ColumnsProvider $columnsProvider)
    {
        $this->setModel($ontology);
        parent::__construct($delivery, $resultsService, $columnsProvider);
    }

    public function getExporterMock($data)
    {
        return $this->getExporter($data);
    }

    public function getColumnsToExport()
    {
        return $this->columns;
    }

    public function setFixtureColumnsToExport($columns)
    {
        $this->columns = $columns;
    }

    public function getExportDataMock($exporter)
    {
        return $this->getExportData($exporter);
    }
}
