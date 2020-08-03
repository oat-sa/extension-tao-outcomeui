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
        ]
    ];
    protected function setUp(): void
    {
    }

    /**
     * @throws \common_exception_NotFound
     */
    public function testGetExporterMock()
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

        $exporter = $singleDeliveryExporter->getExporterMock($this->dataFixture);

        $sqlExpected = file_get_contents(__DIR__, 'sqlFixture.sql');

        $sql = $singleDeliveryExporter->getExportDataMock($exporter);

        $this->assertEquals($sqlExpected, $sql);
    }

}

class SingleDeliverySqlResultExporterMock extends SingleDeliverySqlResultsExporter
{
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
        return [];
    }

    public function getExportDataMock($exporter)
    {
        return $this->getExportData($exporter);
    }
}
