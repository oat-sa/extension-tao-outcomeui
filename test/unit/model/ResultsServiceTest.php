<?php

namespace oat\taoOutcomeUi\unit\model;

use oat\generis\test\TestCase;
use oat\taoOutcomeRds\model\RdsResultStorage;
use oat\taoOutcomeUi\model\ResultsService;

class ResultsServiceTest extends TestCase
{
    /**
     * @dataProvider getVariablesFromObjectResultProvider
     *
     * @param $variables
     * @param $expectedVariablesCount
     * @throws \common_exception_Error
     */
    public function testGetVariablesFromObjectResult($variables, $expectedVariablesCount)
    {
        $service = ResultsService::singleton();

        $mock = $this->getMockBuilder(RdsResultStorage::class)->getMock();
        $mock->expects($this->once())
            ->method('getVariables')
            ->willReturn($variables);

        $service->setImplementation($mock);

        $return = $service->getVariablesFromObjectResult('itemResultFixture');

        $this->assertCount($expectedVariablesCount, $return);
    }

    public function getVariablesFromObjectResultProvider()
    {
        $variable = new \stdClass();
        $variable->variable = new \taoResultServer_models_classes_ResponseVariable();
        $variable->value = '#bar';

        $variable1 = new \stdClass();
        $variable1->variable = new \taoResultServer_models_classes_OutcomeVariable();
        $variable1->value = '#bar';

        return [
            [
                [[$variable]], 1,
            ],
            [
                [[$variable, $variable1]], 2
            ],
            [
                [
                    [$variable],
                    [$variable, $variable1]
                ], 3
            ]
        ];
    }
}