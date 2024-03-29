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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoOutcomeUi\test\integration\model;

use oat\generis\test\GenerisPhpUnitTestRunner;
use oat\oatbox\service\ServiceManager;
use oat\oatbox\user\User;
use oat\tao\test\TaoPhpUnitTestRunner;
use common_ext_ExtensionsManager;
use oat\taoDeliveryRdf\model\DeliveryContainerService;
use oat\taoResultServer\models\classes\ResultManagement;
use oat\taoOutcomeUi\model\ResultsService;
use oat\taoResultServer\models\classes\ResultServerService;
use taoResultServer_models_classes_OutcomeVariable as OutcomeVariable;
use taoResultServer_models_classes_ResponseVariable as ResponseVariable;
use taoResultServer_models_classes_TraceVariable as TraceVariable;
use core_kernel_classes_Resource;
use core_kernel_classes_Property;
use common_exception_Error;

/**
 * @TODO:: Because of usage of tao/models/classes/ClassServiceTrait.php in ResultService we can not mock storage and fix
 *         this test. We need to use ServiceLocatorAwareTrait to be able to set service locator with mocked services.
 *
 * This test case focuses on testing ResultsService.
 *
 * @author Lionel Lecaque <lionel@taotesting.com>
 *
 */
class ResultsServiceTest extends GenerisPhpUnitTestRunner
{
    /**
     *
     * @var ResultsService
     */
    protected $service;

    /**
     *
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    public function setUp(): void
    {
        TaoPhpUnitTestRunner::initTest();
        common_ext_ExtensionsManager::singleton()->getExtensionById('taoOutcomeUi');
        $this->service = ResultsService::singleton();
    }

    /**
     *
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function testGetRootClass()
    {
        $this->assertInstanceOf(\core_kernel_classes_Class::class, $this->service->getRootClass());
        $this->assertEquals(
            'http://www.tao.lu/Ontologies/TAOResult.rdf#DeliveryResult',
            $this->service->getRootClass()->getUri()
        );
    }

    /**
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function testGetImplementation()
    {
        $this->expectException(common_exception_Error::class);

        $this->service->getImplementation();
    }
    /**
     *
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function testSetImplementation()
    {
        $impProphecy = $this->prophesize(ResultManagement::class);

        $imp = $impProphecy->reveal();

        $this->service->setImplementation($imp);
        $this->assertEquals($imp, $this->service->getImplementation());
    }
    /**
     *
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function testGetItemResultsFromDeliveryResult()
    {
        $impProphecy = $this->prophesize(ResultManagement::class);

        $impProphecy->getRelatedItemCallIds('#fakeUri')->willReturn('#fakeDelivery');
        $imp = $impProphecy->reveal();
        $this->service->setImplementation($imp);

        $this->assertEquals(
            '#fakeDelivery',
            $this->service->getItemResultsFromDeliveryResult('#fakeUri')
        );
    }
    /**
     *
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function testGetDelivery()
    {
        $impProphecy = $this->prophesize(ResultManagement::class);
        $impProphecy->getDelivery('#fakeUri')->willReturn('#fakeDelivery');
        $imp = $impProphecy->reveal();

        $this->service->setImplementation($imp);

        $delivery = $this->service->getDelivery('#fakeUri');
        $this->assertInstanceOf(core_kernel_classes_Resource::class, $delivery);
        $this->assertEquals('#fakeDelivery', $delivery->getUri());
    }
    /**
     *
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function testGetVariablesFromObjectResult()
    {
        $impProphecy = $this->prophesize(ResultManagement::class);

        $variable = new \stdClass();
        $variable->variable = new ResponseVariable();
        $variable->value = '#bar';
        $impProphecy->getVariables('#foo')->willReturn([[$variable]]);
        $imp = $impProphecy->reveal();

        $this->service->setImplementation($imp);
        $this->assertContains([$variable], $this->service->getVariablesFromObjectResult('#foo'));
    }
    /**
     *
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function testGetVariableCandidateResponse()
    {
        $impProphecy = $this->prophesize(ResultManagement::class);

        $impProphecy->getVariableProperty('#foo', 'candidateResponse')->willReturn(true);
        $imp = $impProphecy->reveal();

        $this->service->setImplementation($imp);
        $this->assertTrue($this->service->getVariableCandidateResponse('#foo'));
    }
    /**
     *
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function testGetVariableBaseType()
    {
        $impProphecy = $this->prophesize(ResultManagement::class);

        $impProphecy->getVariableProperty('#foo', 'baseType')->willReturn(true);
        $imp = $impProphecy->reveal();

        $this->service->setImplementation($imp);
        $this->assertTrue($this->service->getVariableBaseType('#foo'));
    }

    /**
     * ResultService refactoring is required.
     *
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function testGetItemFromItemResult()
    {
        // @TODO: Refactor ResultService class to be able to mock dependencies and fix test.
        $this->markTestSkipped();

        $impProphecy = $this->prophesize(ResultManagement::class);

        $item = new \stdClass();
        $item->item = '#item';
        $impProphecy->getVariables('#foo')->willReturn([
            [
                $item
            ]
        ]);
        $imp = $impProphecy->reveal();

        $this->service->setImplementation($imp);

        // @todo fix "common_exception_Error: could not create resource from NULL"
        $resultItem = $this->service->getItemFromItemResult('#foo');
        $this->assertInstanceOf(core_kernel_classes_Resource::class, $resultItem);
        $this->assertEquals('#item', $resultItem->getUri());
    }
    /**
     *
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function testGetVariableDataFromDeliveryResult()
    {
        $impProphecy = $this->prophesize(ResultManagement::class);

        $first = microtime();

        $var = new \stdClass();
        $var->callIdTest = 'callId';
        $var->callIdItem = null;
        $responseVariable = new ResponseVariable();
        $responseVariable->setIdentifier('myID');
        $second = microtime();
        $responseVariable->setEpoch($second);
        $var->variable = $responseVariable;

        $var2 = new \stdClass();
        $var2->callIdTest = 'callId';
        $var2->callIdItem = null;
        $outcomeVariable = new OutcomeVariable();
        $outcomeVariable->setIdentifier('mySecondID');
        $outcomeVariable->setEpoch($first);
        $var2->variable = $outcomeVariable;
        $impProphecy->getRelatedTestCallIds("#fakeUri")->willReturn("#fakeTestUri");
        $impProphecy->getVariables('#fakeTestUri')->willReturn([
            [
                $var
            ],
            [
                $var2
            ]
        ]);
        $imp = $impProphecy->reveal();

        $this->service->setImplementation($imp);

        $varDataAll = $this->service->getVariableDataFromDeliveryResult('#fakeUri');
        $this->assertEquals([
            $responseVariable,
            $outcomeVariable
        ], $varDataAll);
        $varDataEmpty = $this->service->getVariableDataFromDeliveryResult(
            '#fakeUri',
            [TraceVariable::class]
        );
        $this->assertEmpty($varDataEmpty);
        $varData = $this->service->getVariableDataFromDeliveryResult(
            '#fakeUri',
            [ResponseVariable::class]
        );
        $this->assertEquals([
            $responseVariable
        ], $varData);
    }
    /**
     *
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function testGetTestTaker()
    {
        $impProphecy = $this->prophesize(ResultManagement::class);

        $impProphecy->getTestTaker('#fakeUri')->willReturn('#testTaker');
        $imp = $impProphecy->reveal();

        $this->service->setImplementation($imp);

        $serviceManager = ServiceManager::getServiceManager();
        $this->service->setServiceLocator($serviceManager);

        $item = $this->service->getTestTaker('#fakeUri');
        $this->assertInstanceOf(\core_kernel_users_GenerisUser::class, $item);
        $this->assertEquals('#testTaker', $item->getIdentifier());
    }
    /**
     *
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function testDeleteResult()
    {
        $impProphecy = $this->prophesize(ResultManagement::class);
        $impProphecy->deleteResult('#foo')->willReturn(true);
        $imp = $impProphecy->reveal();

        $this->service->setImplementation($imp);

        $this->assertTrue($this->service->deleteResult('#foo'));
    }

    /**
     * ResultService refactoring is required.
     *
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function testGetReadableImplementationNoResultStorage()
    {
        // @TODO: Refactor ResultService class to be able to mock dependencies and fix test.
        $this->markTestSkipped();

        $this->expectException(common_exception_Error::class);

        $deliveryProphecy = $this->prophesize(core_kernel_classes_Resource::class);
        $delivery = $deliveryProphecy->reveal();

        $this->service->getReadableImplementation($delivery);
    }

    /**
     * ResultService refactoring is required.
     *
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function testGetReadableImplementationNoResultServer()
    {
        // @TODO: Refactor ResultService class to be able to mock dependencies and fix test.
        $this->markTestSkipped();

        $this->expectException(common_exception_Error::class);

        $resultProphecy = $this->prophesize(core_kernel_classes_Resource::class);
        $resultServer = $resultProphecy->reveal();

        $deliveryProphecy = $this->prophesize(core_kernel_classes_Resource::class);
        $deliveryProphecy
            ->getOnePropertyValue(
                new core_kernel_classes_Property(DeliveryContainerService::PROPERTY_RESULT_SERVER)
            )
            ->willReturn($resultServer);
        $delivery = $deliveryProphecy->reveal();

        $this->service->getReadableImplementation($delivery);
    }

    /**
     *
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function testGetReadableImplementationNoResultServerModel()
    {
        // @TODO: Refactor ResultService class to be able to mock dependencies and fix test.
        $this->markTestSkipped();

        $this->expectException(common_exception_Error::class);

        $resultProphecy = $this->prophesize(core_kernel_classes_Resource::class);
        $resultProphecy
            ->getPropertyValues(new core_kernel_classes_Property(ResultServerService::PROPERTY_HAS_MODEL))
            ->willReturn(['#fakeUri']);
        $resultServer = $resultProphecy->reveal();

        $deliveryProphecy = $this->prophesize(core_kernel_classes_Resource::class);

        $deliveryProphecy
            ->getOnePropertyValue(
                new core_kernel_classes_Property(DeliveryContainerService::PROPERTY_RESULT_SERVER)
            )
            ->willReturn($resultServer);

        $delivery = $deliveryProphecy->reveal();
        $this->service->getReadableImplementation($delivery);
    }

    /**
     *
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function testGetReadableImplementation()
    {
        $resultProphecy = $this->prophesize(core_kernel_classes_Resource::class);
        $resultServer = $resultProphecy->reveal();

        $deliveryProphecy = $this->prophesize(core_kernel_classes_Resource::class);
        $deliveryProphecy
            ->getOnePropertyValue(
                new core_kernel_classes_Property(DeliveryContainerService::PROPERTY_RESULT_SERVER)
            )
            ->willReturn($resultServer);
        $delivery = $deliveryProphecy->reveal();

        $this->assertInstanceOf(ResultManagement::class, $this->service->getReadableImplementation($delivery));
    }

    public function testGetVariables()
    {
        $variable1 = new \stdClass();
        $variable1->variable = new ResponseVariable();
        $variable1->value = 'foo';
        $variable1->callIdItem = '#itemResultVariable';

        $variable2 = new \stdClass();
        $variable2->variable = new ResponseVariable();
        $variable2->value = 'bar';
        $variable2->callIdTest = '#testResultVariable';

        $impProphecy = $this->prophesize(ResultManagement::class);
        $impProphecy->getRelatedItemCallIds('#fakeUri')->willReturn(['#itemsResults1' => '#itemResultVariable']);
        $impProphecy->getRelatedTestCallIds('#fakeUri')->willReturn(['#testResults1' => '#testResultVariable']);
        $impProphecy->getDeliveryVariables('#fakeUri')->willReturn([
            [$variable1],
            [$variable2]
        ]);
        $implementationMock = $impProphecy->reveal();

        $this->service->setImplementation($implementationMock);

        $var = ($this->service->getVariables('#fakeUri'));
        $this->assertContains([$variable1], $var);

        $var = $this->service->getVariables('#fakeUri', false);
        $this->assertArrayHasKey('#itemResultVariable', $var);
        $this->assertEquals([[$variable1]], $var['#itemResultVariable']);
    }

    /**
     * ResultService refactoring is required.
     *
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function testGetItemVariableDataFromDeliveryResult()
    {
        // @TODO: Refactor ResultService class to be able to mock dependencies and fix test.
        $this->markTestSkipped();

        $impProphecy = $this->prophesize(ResultManagement::class);
        $impProphecy->getRelatedItemCallIds('#fakeUri')->willReturn([
            '#itemsResults1' => '#itemResultVariable',
            '#itemsResults2' => '#itemResultVariable2',
            '#itemsResults3' => '#itemResultVariable3'
        ]);

        $item = new \stdClass();
        $item->item = '#item';
        $item->uri = '#uri';

        $var = new TraceVariable();
        $var->setEpoch(microtime());
        $var->setIdentifier('varIdentifier');
        $item->variable = $var;

        $item2 = new \stdClass();
        $item2->item = '#item2';
        $item2->uri = '#uri2';

        $var2 = new ResponseVariable();
        $var2->setEpoch(microtime());
        $var2->setIdentifier('varIdentifier2');
        // response correct
        $var2->setCorrectResponse(1);
        $item2->variable = $var2;

        $item3 = new \stdClass();
        $item3->item = '#item3';
        $item3->uri = '#uri3';

        $var3 = new ResponseVariable();
        $var3->setEpoch(microtime());
        $var3->setIdentifier('varIdentifier3');
        // response incorrect
        $var3->setCorrectResponse(0);
        $item3->variable = $var3;

        $impProphecy->getVariables('#itemResultVariable')->willReturn([
            [
                $item
            ]
        ]);
        $impProphecy->getVariables('#itemResultVariable2')->willReturn([
            [
                $item2
            ]
        ]);
        $impProphecy->getVariables('#itemResultVariable3')->willReturn([
            [
                $item3
            ]
        ]);

        $imp = $impProphecy->reveal();
        $this->service->setImplementation($imp);

        $itemVar = $this->service->getItemVariableDataFromDeliveryResult(
            '#fakeUri',
            ResultsService::VARIABLES_FILTER_LAST_SUBMITTED
        );

        // @todo fix (Failed asserting that an array has the key '#item')
        $this->assertArrayHasKey('#item', $itemVar);
        $this->assertArrayHasKey('itemModel', $itemVar['#item']);
        $this->assertEquals('unknown', $itemVar['#item']['itemModel']);

        $this->assertArrayHasKey('sortedVars', $itemVar['#item']);
        $this->assertArrayHasKey(TraceVariable::class, $itemVar['#item']['sortedVars']);
        $this->assertArrayHasKey('varIdentifier', $itemVar['#item']['sortedVars'][TraceVariable::class]);

        $this->assertArrayHasKey(
            'uri',
            $itemVar['#item']['sortedVars'][TraceVariable::class]['varIdentifier'][0]
        );
        $this->assertEquals(
            '#uri',
            $itemVar['#item']['sortedVars'][TraceVariable::class]['varIdentifier'][0]['uri']
        );

        $this->assertArrayHasKey(
            'isCorrect',
            $itemVar['#item']['sortedVars'][TraceVariable::class]['varIdentifier'][0]
        );
        $this->assertEquals(
            'unscored',
            $itemVar['#item']['sortedVars'][TraceVariable::class]['varIdentifier'][0]['isCorrect']
        );

        $this->assertArrayHasKey('var', $itemVar['#item']['sortedVars'][TraceVariable::class]['varIdentifier'][0]);
        $this->assertInstanceOf(
            TraceVariable::class,
            $itemVar['#item']['sortedVars'][TraceVariable::class]['varIdentifier'][0]['var']
        );
        $this->assertEquals($var, $itemVar['#item']['sortedVars'][TraceVariable::class]['varIdentifier'][0]['var']);

        $this->assertArrayHasKey('label', $itemVar['#item']);

        // item2
        $this->assertArrayHasKey('#item2', $itemVar);
        $this->assertArrayHasKey('itemModel', $itemVar['#item2']);
        $this->assertEquals('unknown', $itemVar['#item2']['itemModel']);

        $this->assertArrayHasKey('sortedVars', $itemVar['#item2']);
        $this->assertArrayHasKey(ResponseVariable::class, $itemVar['#item2']['sortedVars']);
        $this->assertArrayHasKey('varIdentifier2', $itemVar['#item2']['sortedVars'][ResponseVariable::class]);

        $this->assertArrayHasKey(
            'uri',
            $itemVar['#item2']['sortedVars'][ResponseVariable::class]['varIdentifier2'][0]
        );
        $this->assertEquals(
            '#uri2',
            $itemVar['#item2']['sortedVars'][ResponseVariable::class]['varIdentifier2'][0]['uri']
        );

        $this->assertArrayHasKey(
            'isCorrect',
            $itemVar['#item2']['sortedVars'][ResponseVariable::class]['varIdentifier2'][0]
        );
        $this->assertEquals(
            'correct',
            $itemVar['#item2']['sortedVars'][ResponseVariable::class]['varIdentifier2'][0]['isCorrect']
        );

        // item3
        $this->assertArrayHasKey('#item3', $itemVar);
        $this->assertArrayHasKey('itemModel', $itemVar['#item3']);
        $this->assertEquals(
            'unknown',
            $itemVar['#item3']['itemModel']
        );

        $this->assertArrayHasKey('sortedVars', $itemVar['#item3']);
        $this->assertArrayHasKey(ResponseVariable::class, $itemVar['#item3']['sortedVars']);
        $this->assertArrayHasKey('varIdentifier3', $itemVar['#item3']['sortedVars'][ResponseVariable::class]);

        $this->assertArrayHasKey('uri', $itemVar['#item3']['sortedVars'][ResponseVariable::class]['varIdentifier3'][0]);
        $this->assertEquals(
            '#uri3',
            $itemVar['#item3']['sortedVars'][ResponseVariable::class]['varIdentifier3'][0]['uri']
        );

        $this->assertArrayHasKey(
            'isCorrect',
            $itemVar['#item3']['sortedVars'][ResponseVariable::class]['varIdentifier3'][0]
        );
        $this->assertEquals(
            'incorrect',
            $itemVar['#item3']['sortedVars'][ResponseVariable::class]['varIdentifier3'][0]['isCorrect']
        );
    }

    /**
     * @depends testGetItemVariableDataFromDeliveryResult
     *
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function testGetItemVariableDataStatsFromDeliveryResult()
    {
        // @TODO: Refactor ResultService class to be able to mock dependencies and fix test.
        $this->markTestSkipped();

        $itemVar = $this->service->getItemVariableDataStatsFromDeliveryResult(
            '#fakeUri',
            ResultsService::VARIABLES_FILTER_LAST_SUBMITTED
        );

        $this->assertArrayHasKey('nbResponses', $itemVar);
        $this->assertEquals(2, $itemVar['nbResponses']);
        $this->assertArrayHasKey('nbCorrectResponses', $itemVar);
        $this->assertEquals(1, $itemVar['nbCorrectResponses']);
        $this->assertArrayHasKey('nbIncorrectResponses', $itemVar);
        $this->assertEquals(1, $itemVar['nbIncorrectResponses']);
        $this->assertArrayHasKey('nbUnscoredResponses', $itemVar);
        $this->assertEquals(0, $itemVar['nbUnscoredResponses']);

        $this->assertArrayHasKey('data', $itemVar);
    }

    public function testAllGetStructuredVariables()
    {
        // @TODO: Refactor ResultService class to be able to mock dependencies and fix test.
        $this->markTestSkipped();

        $serviceMock = $this->getMockBuilder(ResultsService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getItemFromItemResult'])
            ->getMock();

        $itemModel1Prophecy = $this->prophesize(core_kernel_classes_Resource::class);
        $itemModel1Prophecy->getLabel()->willReturn('MyItemModel');

        $relatedItem1Prophecy = $this->prophesize(core_kernel_classes_Resource::class);
        $relatedItem1Prophecy->getLabel()->willReturn('MyRelatedItem1');
        $relatedItem1Prophecy->getUri()->willReturn('MyRelatedItemUri1');
        $relatedItem1Prophecy
            ->getUniquePropertyValue(
                new core_kernel_classes_Property(taoItems_models_classes_ItemsService::PROPERTY_ITEM_MODEL)
            )
            ->willReturn($itemModel1Prophecy->reveal());

        $itemModel2Prophecy = $this->prophesize(core_kernel_classes_Resource::class);
        $itemModel2Prophecy->getLabel()->willReturn('MySecondItemModel');

        $relatedItem2Prophecy = $this->prophesize(core_kernel_classes_Resource::class);
        $relatedItem2Prophecy->getLabel()->willReturn('MyRelatedItem2');
        $relatedItem2Prophecy->getUri()->willReturn('MyRelatedItemUri2');
        $relatedItem2Prophecy
            ->getUniquePropertyValue(
                new core_kernel_classes_Property(taoItems_models_classes_ItemsService::PROPERTY_ITEM_MODEL)
            )
            ->willReturn($itemModel2Prophecy->reveal());

        $itemModel3Prophecy = $this->prophesize(core_kernel_classes_Resource::class);
        $itemModel3Prophecy->getLabel()->willReturn('MyThirdItemModel');

        $relatedItem3Prophecy = $this->prophesize(core_kernel_classes_Resource::class);
        $relatedItem3Prophecy->getLabel()->willReturn('MyRelatedItem3');
        $relatedItem3Prophecy->getUri()->willReturn('MyRelatedItemUri3');
        $relatedItem3Prophecy
            ->getUniquePropertyValue(
                new core_kernel_classes_Property(taoItems_models_classes_ItemsService::PROPERTY_ITEM_MODEL)
            )
            ->willReturn($itemModel3Prophecy->reveal());


        //Variables for Item 1
        $callId1 = 'callId1';
        $epoch1 = microtime();
        $variable1 = new \stdClass();
        $responseVariable1 = new ResponseVariable();
        $responseVariable1->setValue('myValue');
        $responseVariable1->setCorrectResponse(1);
        $responseVariable1->setBaseType('string');
        $responseVariable1->setCardinality('single');
        $responseVariable1->setEpoch($epoch1);
        $responseVariable1->setIdentifier('MyFirstResponseVariableIdentifier');
        $variable1->uri = 'uri11';
        $variable1->class = ResponseVariable::class;
        $variable1->deliveryResultIdentifier = '#fakeUri';
        $variable1->callIdItem = 'callId1';
        $variable1->callIdTest = '';
        $variable1->test = '#fakeTestUri';
        $variable1->item = 'MyRelatedItemUri1';
        $variable1->variable = $responseVariable1;

        $epoch12 = microtime();
        $variable12 = new \stdClass();
        $outcomeVariable1 = new OutcomeVariable();
        $outcomeVariable1->setValue('myOutcomeValue');
        $outcomeVariable1->setBaseType('string');
        $outcomeVariable1->setCardinality('multiple');
        $outcomeVariable1->setEpoch($epoch12);
        $outcomeVariable1->setIdentifier('MyFirstOutcomeVariableIdentifier');
        $outcomeVariable1->setNormalMaximum(10);
        $outcomeVariable1->setNormalMinimum(1);
        $variable12->uri = 'uri12';
        $variable12->class = OutcomeVariable::class;
        $variable12->deliveryResultIdentifier = '#fakeUri';
        $variable12->callIdItem = 'callId1';
        $variable12->callIdTest = '';
        $variable12->test = '#fakeTestUri';
        $variable12->item = 'MyRelatedItemUri1';
        $variable12->variable = $outcomeVariable1;

        $epoch13 = microtime();
        $variable13 = new \stdClass();
        $traceVariable1 = new TraceVariable();
        $traceVariable1->setValue(true);
        $traceVariable1->setTrace('my first trace');
        $traceVariable1->setBaseType('boolean');
        $traceVariable1->setCardinality('single');
        $traceVariable1->setEpoch($epoch13);
        $traceVariable1->setIdentifier('MyFirstTraceVariableIdentifier');
        $variable13->uri = 'uri13';
        $variable13->class = TraceVariable::class;
        $variable13->deliveryResultIdentifier = '#fakeUri';
        $variable13->callIdItem = 'callId1';
        $variable13->callIdTest = '';
        $variable13->test = '#fakeTestUri';
        $variable13->item = 'MyRelatedItemUri1';
        $variable13->variable = $traceVariable1;


        $variables1 = [
            'uri11' => [
                $variable1
            ],
            'uri12' => [
                $variable12
            ],
            'uri13' => [
                $variable13
            ]
        ];

        //Variables for Item 2
        $callId2 = 'callId2';
        $epoch2 = microtime();
        $variable2 = new \stdClass();
        $responseVariable2 = new ResponseVariable();
        $responseVariable2->setValue('myValue');
        $responseVariable2->setCorrectResponse(0);
        $responseVariable2->setBaseType('string');
        $responseVariable2->setCardinality('single');
        $responseVariable2->setEpoch($epoch2);
        $responseVariable2->setIdentifier('MySecondResponseVariableIdentifier');
        $variable2->uri = 'uri21';
        $variable2->class = ResponseVariable::class;
        $variable2->deliveryResultIdentifier = '#fakeUri';
        $variable2->callIdItem = 'callId2';
        $variable2->callIdTest = '';
        $variable2->test = '#fakeTestUri';
        $variable2->item = 'MyRelatedItemUri2';
        $variable2->variable = $responseVariable2;

        $epoch22 = microtime();
        $variable22 = new \stdClass();
        $outcomeVariable2 = new OutcomeVariable();
        $outcomeVariable2->setValue('myOutcomeValue');
        $outcomeVariable2->setBaseType('string');
        $outcomeVariable2->setCardinality('multiple');
        $outcomeVariable2->setEpoch($epoch22);
        $outcomeVariable2->setIdentifier('MySecondOutcomeVariableIdentifier');
        $outcomeVariable2->setNormalMaximum(10);
        $outcomeVariable2->setNormalMinimum(1);
        $variable22->uri = 'uri22';
        $variable22->class = OutcomeVariable::class;
        $variable22->deliveryResultIdentifier = '#fakeUri';
        $variable22->callIdItem = 'callId2';
        $variable22->callIdTest = '';
        $variable22->test = '#fakeTestUri';
        $variable22->item = 'MyRelatedItemUri2';
        $variable22->variable = $outcomeVariable2;

        $epoch23 = microtime();
        $variable23 = new \stdClass();
        $traceVariable2 = new TraceVariable();
        $traceVariable2->setValue(true);
        $traceVariable2->setTrace('my second trace');
        $traceVariable2->setBaseType('boolean');
        $traceVariable2->setCardinality('single');
        $traceVariable2->setEpoch($epoch23);
        $traceVariable2->setIdentifier('MySecondTraceVariableIdentifier');
        $variable23->uri = 'uri23';
        $variable23->class = TraceVariable::class;
        $variable23->deliveryResultIdentifier = '#fakeUri';
        $variable23->callIdItem = 'callId2';
        $variable23->callIdTest = '';
        $variable23->test = '#fakeTestUri';
        $variable23->item = 'MyRelatedItemUri2';
        $variable23->variable = $traceVariable2;



        //Variables for Item 3
        $callId3 = 'callId3';
        $epoch3 = microtime();
        $variable3 = new \stdClass();
        $responseVariable3 = new ResponseVariable();
        $responseVariable3->setValue('myValue');
        $responseVariable3->setCorrectResponse(1);
        $responseVariable3->setBaseType('string');
        $responseVariable3->setCardinality('single');
        $responseVariable3->setEpoch($epoch3);
        $responseVariable3->setIdentifier('MyThirdResponseVariableIdentifier');
        $variable3->uri = 'uri31';
        $variable3->class = ResponseVariable::class;
        $variable3->deliveryResultIdentifier = '#fakeUri';
        $variable3->callIdItem = 'callId3';
        $variable3->callIdTest = '';
        $variable3->test = '#fakeTestUri';
        $variable3->item = 'MyRelatedItemUri3';
        $variable3->variable = $responseVariable3;

        $epoch32 = microtime();
        $variable32 = new \stdClass();
        $outcomeVariable3 = new OutcomeVariable();
        $outcomeVariable3->setValue('myOutcomeValue');
        $outcomeVariable3->setBaseType('string');
        $outcomeVariable3->setCardinality('multiple');
        $outcomeVariable3->setEpoch($epoch32);
        $outcomeVariable3->setIdentifier('MyThirdOutcomeVariableIdentifier');
        $outcomeVariable3->setNormalMaximum(10);
        $outcomeVariable3->setNormalMinimum(1);
        $variable32->uri = 'uri32';
        $variable32->class = OutcomeVariable::class;
        $variable32->deliveryResultIdentifier = '#fakeUri';
        $variable32->callIdItem = 'callId3';
        $variable32->callIdTest = '';
        $variable32->test = '#fakeTestUri';
        $variable32->item = 'MyRelatedItemUri3';
        $variable32->variable = $outcomeVariable3;

        $epoch33 = microtime();
        $variable33 = new \stdClass();
        $traceVariable3 = new TraceVariable();
        $traceVariable3->setValue(true);
        $traceVariable3->setTrace('my third trace');
        $traceVariable3->setBaseType('boolean');
        $traceVariable3->setCardinality('single');
        $traceVariable3->setEpoch($epoch33);
        $traceVariable3->setIdentifier('MyThirdTraceVariableIdentifier');
        $variable33->uri = 'uri33';
        $variable33->class = TraceVariable::class;
        $variable33->deliveryResultIdentifier = '#fakeUri';
        $variable33->callIdItem = 'callId3';
        $variable33->callIdTest = '';
        $variable33->test = '#fakeTestUri';
        $variable33->item = 'MyRelatedItemUri3';
        $variable33->variable = $traceVariable3;

        $epoch24 = microtime();
        $variable24 = new \stdClass();
        $traceVariable22 = new TraceVariable();
        $traceVariable22->setValue(true);
        $traceVariable22->setTrace('my forth trace');
        $traceVariable22->setBaseType('boolean');
        $traceVariable22->setCardinality('single');
        $traceVariable22->setEpoch($epoch24);
        $traceVariable22->setIdentifier('TraceVariableIdentifier');
        $variable24->uri = 'uri24';
        $variable24->class = TraceVariable::class;
        $variable24->deliveryResultIdentifier = '#fakeUri';
        $variable24->callIdItem = 'callId2';
        $variable24->callIdTest = '';
        $variable24->test = '#fakeTestUri';
        $variable24->item = 'MyRelatedItemUri2';
        $variable24->variable = $traceVariable22;



        $variables2 = [
            'uri21' => [
                $variable2
            ],
            'uri22' => [
                $variable22
            ],
            'uri23' => [
                $variable23
            ],
            'uri24' => [
                $variable24
            ]
        ];

        $variables3 = [
            'uri31' => [
                $variable3
            ],
            'uri32' => [
                $variable32
            ],
            'uri33' => [
                $variable33
            ]
        ];


        $callIds = [$callId1, $callId2, $callId3];

        $relatedItem1 = $relatedItem1Prophecy->reveal();
        $relatedItem2 = $relatedItem2Prophecy->reveal();
        $relatedItem3 = $relatedItem3Prophecy->reveal();

        $serviceMock->expects($this->exactly(8))
            ->method('getItemFromItemResult')
            ->withConsecutive(
                [$callId1, [[$variable1]]],
                [$callId2, [[$variable2]]],
                [$callId3, [[$variable3]]],
                [$callId2, [[$variable24]]],
                [$callId1, [[$variable13]]],
                [$callId2, [[$variable23]]],
                [$callId3, [[$variable33]]],
                [$callId2, [[$variable24]]]
            )
            ->willReturnOnConsecutiveCalls(
                $relatedItem1,
                $relatedItem2,
                $relatedItem3,
                $relatedItem2,
                $relatedItem1,
                $relatedItem2,
                $relatedItem3,
                $relatedItem2
            );


        $impProphecy = $this->prophesize(ResultManagement::class);
        $impProphecy->getRelatedItemCallIds('DeliveryExecutionIdentifier')->willReturn($callIds);
        $impProphecy->getVariables($callId1)->willReturn($variables1);
        $impProphecy->getVariables($callId2)->willReturn($variables2);
        $impProphecy->getVariables($callId3)->willReturn($variables3);
        $serviceMock->setImplementation($impProphecy->reveal());

        // @todo fix method call not expected
        $wantedTypes = [
            ResponseVariable::class,
            OutcomeVariable::class,
            TraceVariable::class
        ];
        $allVariables = $serviceMock->getStructuredVariables(
            'DeliveryExecutionIdentifier',
            'all',
            $wantedTypes
        );

        $lastVariables = $serviceMock->getStructuredVariables(
            'DeliveryExecutionIdentifier',
            ResultsService::VARIABLES_FILTER_LAST_SUBMITTED,
            [TraceVariable::class]
        );

        $this->assertIsArray($allVariables);

        $this->assertEquals([$epoch1,$epoch2,$epoch3,$epoch24], array_keys($allVariables));

        $this->assertIsArray($allVariables[$epoch1]);
        $this->assertArrayHasKey('itemModel', $allVariables[$epoch1]);
        $this->assertEquals('MyItemModel', $allVariables[$epoch1]['itemModel']);
        $this->assertArrayHasKey('label', $allVariables[$epoch1]);
        $this->assertEquals('MyRelatedItem1', $allVariables[$epoch1]['label']);
        $this->assertArrayHasKey('uri', $allVariables[$epoch1]);
        $this->assertEquals('MyRelatedItemUri1', $allVariables[$epoch1]['uri']);

        $this->assertIsArray($allVariables[$epoch2]);
        $this->assertArrayHasKey('itemModel', $allVariables[$epoch2]);
        $this->assertEquals('MySecondItemModel', $allVariables[$epoch2]['itemModel']);
        $this->assertArrayHasKey('label', $allVariables[$epoch2]);
        $this->assertEquals('MyRelatedItem2', $allVariables[$epoch2]['label']);
        $this->assertArrayHasKey('uri', $allVariables[$epoch2]);
        $this->assertEquals('MyRelatedItemUri2', $allVariables[$epoch2]['uri']);

        $this->assertIsArray($allVariables[$epoch3]);
        $this->assertArrayHasKey('itemModel', $allVariables[$epoch3]);
        $this->assertEquals('MyThirdItemModel', $allVariables[$epoch3]['itemModel']);
        $this->assertArrayHasKey('label', $allVariables[$epoch3]);
        $this->assertEquals('MyRelatedItem3', $allVariables[$epoch3]['label']);
        $this->assertArrayHasKey('uri', $allVariables[$epoch3]);
        $this->assertEquals('MyRelatedItemUri3', $allVariables[$epoch3]['uri']);

        $this->assertArrayHasKey(ResponseVariable::class, $allVariables[$epoch1]);
        $this->assertIsArray($allVariables[$epoch1][ResponseVariable::class]);
        $this->assertArrayHasKey(
            'MyFirstResponseVariableIdentifier',
            $allVariables[$epoch1][ResponseVariable::class]
        );
        $this->assertIsArray($allVariables[$epoch1][ResponseVariable::class]['MyFirstResponseVariableIdentifier']);
        $this->assertArrayHasKey(
            'uri',
            $allVariables[$epoch1][ResponseVariable::class]['MyFirstResponseVariableIdentifier']
        );
        $this->assertEquals(
            'uri11',
            $allVariables[$epoch1][ResponseVariable::class]['MyFirstResponseVariableIdentifier']['uri']
        );

        $this->assertArrayHasKey(
            'var',
            $allVariables[$epoch1][ResponseVariable::class]['MyFirstResponseVariableIdentifier']
        );
        $this->assertEquals(
            $responseVariable1,
            $allVariables[$epoch1][ResponseVariable::class]['MyFirstResponseVariableIdentifier']['var']
        );

        $this->assertArrayHasKey(
            'isCorrect',
            $allVariables[$epoch1][ResponseVariable::class]['MyFirstResponseVariableIdentifier']
        );
        $this->assertEquals(
            'correct',
            $allVariables[$epoch1][ResponseVariable::class]['MyFirstResponseVariableIdentifier']['isCorrect']
        );

        $this->assertArrayHasKey(OutcomeVariable::class, $allVariables[$epoch1]);
        $this->assertIsArray($allVariables[$epoch1][OutcomeVariable::class]);
        $this->assertArrayHasKey(
            'MyFirstOutcomeVariableIdentifier',
            $allVariables[$epoch1][OutcomeVariable::class]
        );
        $this->assertIsArray($allVariables[$epoch1][OutcomeVariable::class]['MyFirstOutcomeVariableIdentifier']);
        $this->assertArrayHasKey(
            'uri',
            $allVariables[$epoch1][OutcomeVariable::class]['MyFirstOutcomeVariableIdentifier']
        );
        $this->assertEquals(
            'uri12',
            $allVariables[$epoch1][OutcomeVariable::class]['MyFirstOutcomeVariableIdentifier']['uri']
        );

        $this->assertArrayHasKey(
            'var',
            $allVariables[$epoch1][OutcomeVariable::class]['MyFirstOutcomeVariableIdentifier']
        );
        $this->assertEquals(
            $outcomeVariable1,
            $allVariables[$epoch1][OutcomeVariable::class]['MyFirstOutcomeVariableIdentifier']['var']
        );

        $this->assertArrayHasKey(
            'isCorrect',
            $allVariables[$epoch1][OutcomeVariable::class]['MyFirstOutcomeVariableIdentifier']
        );
        $this->assertEquals(
            'unscored',
            $allVariables[$epoch1][OutcomeVariable::class]['MyFirstOutcomeVariableIdentifier']['isCorrect']
        );

        $this->assertArrayHasKey(TraceVariable::class, $allVariables[$epoch1]);
        $this->assertIsArray($allVariables[$epoch1][TraceVariable::class]);
        $this->assertArrayHasKey(
            'MyFirstTraceVariableIdentifier',
            $allVariables[$epoch1][TraceVariable::class]
        );
        $this->assertIsArray(
            $allVariables[$epoch1][TraceVariable::class]['MyFirstTraceVariableIdentifier']
        );
        $this->assertArrayHasKey(
            'uri',
            $allVariables[$epoch1][TraceVariable::class]['MyFirstTraceVariableIdentifier']
        );
        $this->assertEquals(
            'uri13',
            $allVariables[$epoch1][TraceVariable::class]['MyFirstTraceVariableIdentifier']['uri']
        );

        $this->assertArrayHasKey(
            'var',
            $allVariables[$epoch1][TraceVariable::class]['MyFirstTraceVariableIdentifier']
        );
        $this->assertEquals(
            $traceVariable1,
            $allVariables[$epoch1][TraceVariable::class]['MyFirstTraceVariableIdentifier']['var']
        );

        $this->assertArrayHasKey(
            'isCorrect',
            $allVariables[$epoch1][TraceVariable::class]['MyFirstTraceVariableIdentifier']
        );
        $this->assertEquals(
            'unscored',
            $allVariables[$epoch1][TraceVariable::class]['MyFirstTraceVariableIdentifier']['isCorrect']
        );


        $this->assertArrayHasKey(ResponseVariable::class, $allVariables[$epoch2]);
        $this->assertIsArray($allVariables[$epoch2][ResponseVariable::class]);
        $this->assertArrayHasKey(
            'MySecondResponseVariableIdentifier',
            $allVariables[$epoch2][ResponseVariable::class]
        );
        $this->assertIsArray(
            $allVariables[$epoch2][ResponseVariable::class]['MySecondResponseVariableIdentifier']
        );
        $this->assertArrayHasKey(
            'uri',
            $allVariables[$epoch2][ResponseVariable::class]['MySecondResponseVariableIdentifier']
        );
        $this->assertEquals(
            'uri21',
            $allVariables[$epoch2][ResponseVariable::class]['MySecondResponseVariableIdentifier']['uri']
        );

        $this->assertArrayHasKey(
            'var',
            $allVariables[$epoch2][ResponseVariable::class]['MySecondResponseVariableIdentifier']
        );
        $this->assertEquals(
            $responseVariable2,
            $allVariables[$epoch2][ResponseVariable::class]['MySecondResponseVariableIdentifier']['var']
        );

        $this->assertArrayHasKey(
            'isCorrect',
            $allVariables[$epoch2][ResponseVariable::class]['MySecondResponseVariableIdentifier']
        );
        $this->assertEquals(
            'incorrect',
            $allVariables[$epoch2][ResponseVariable::class]['MySecondResponseVariableIdentifier']['isCorrect']
        );

        $this->assertArrayHasKey(OutcomeVariable::class, $allVariables[$epoch2]);
        $this->assertIsArray($allVariables[$epoch2][OutcomeVariable::class]);
        $this->assertArrayHasKey(
            'MySecondOutcomeVariableIdentifier',
            $allVariables[$epoch2][OutcomeVariable::class]
        );
        $this->assertIsArray(
            $allVariables[$epoch2][OutcomeVariable::class]['MySecondOutcomeVariableIdentifier']
        );
        $this->assertArrayHasKey(
            'uri',
            $allVariables[$epoch2][OutcomeVariable::class]['MySecondOutcomeVariableIdentifier']
        );
        $this->assertEquals(
            'uri22',
            $allVariables[$epoch2][OutcomeVariable::class]['MySecondOutcomeVariableIdentifier']['uri']
        );

        $this->assertArrayHasKey(
            'var',
            $allVariables[$epoch2][OutcomeVariable::class]['MySecondOutcomeVariableIdentifier']
        );
        $this->assertEquals(
            $outcomeVariable2,
            $allVariables[$epoch2][OutcomeVariable::class]['MySecondOutcomeVariableIdentifier']['var']
        );

        $this->assertArrayHasKey(
            'isCorrect',
            $allVariables[$epoch2][OutcomeVariable::class]['MySecondOutcomeVariableIdentifier']
        );
        $this->assertEquals(
            'unscored',
            $allVariables[$epoch2][OutcomeVariable::class]['MySecondOutcomeVariableIdentifier']['isCorrect']
        );

        $this->assertArrayHasKey(TraceVariable::class, $allVariables[$epoch2]);
        $this->assertIsArray($allVariables[$epoch2][TraceVariable::class]);
        $this->assertArrayHasKey(
            'MySecondTraceVariableIdentifier',
            $allVariables[$epoch2][TraceVariable::class]
        );
        $this->assertIsArray(
            $allVariables[$epoch2][TraceVariable::class]['MySecondTraceVariableIdentifier']
        );
        $this->assertArrayHasKey(
            'uri',
            $allVariables[$epoch2][TraceVariable::class]['MySecondTraceVariableIdentifier']
        );
        $this->assertEquals(
            'uri23',
            $allVariables[$epoch2][TraceVariable::class]['MySecondTraceVariableIdentifier']['uri']
        );

        $this->assertArrayHasKey(
            'var',
            $allVariables[$epoch2][TraceVariable::class]['MySecondTraceVariableIdentifier']
        );
        $this->assertEquals(
            $traceVariable2,
            $allVariables[$epoch2][TraceVariable::class]['MySecondTraceVariableIdentifier']['var']
        );

        $this->assertArrayHasKey(
            'isCorrect',
            $allVariables[$epoch2][TraceVariable::class]['MySecondTraceVariableIdentifier']
        );
        $this->assertEquals(
            'unscored',
            $allVariables[$epoch2][TraceVariable::class]['MySecondTraceVariableIdentifier']['isCorrect']
        );

        $this->assertArrayHasKey(ResponseVariable::class, $allVariables[$epoch3]);
        $this->assertIsArray($allVariables[$epoch3][ResponseVariable::class]);
        $this->assertArrayHasKey(
            'MyThirdResponseVariableIdentifier',
            $allVariables[$epoch3][ResponseVariable::class]
        );
        $this->assertIsArray(
            $allVariables[$epoch3][ResponseVariable::class]['MyThirdResponseVariableIdentifier']
        );
        $this->assertArrayHasKey(
            'uri',
            $allVariables[$epoch3][ResponseVariable::class]['MyThirdResponseVariableIdentifier']
        );
        $this->assertEquals(
            'uri31',
            $allVariables[$epoch3][ResponseVariable::class]['MyThirdResponseVariableIdentifier']['uri']
        );

        $this->assertArrayHasKey(
            'var',
            $allVariables[$epoch3][ResponseVariable::class]['MyThirdResponseVariableIdentifier']
        );
        $this->assertEquals(
            $responseVariable3,
            $allVariables[$epoch3][ResponseVariable::class]['MyThirdResponseVariableIdentifier']['var']
        );

        $this->assertArrayHasKey(
            'isCorrect',
            $allVariables[$epoch3][ResponseVariable::class]['MyThirdResponseVariableIdentifier']
        );
        $this->assertEquals(
            'correct',
            $allVariables[$epoch3][ResponseVariable::class]['MyThirdResponseVariableIdentifier']['isCorrect']
        );

        $this->assertArrayHasKey(OutcomeVariable::class, $allVariables[$epoch3]);
        $this->assertIsArray($allVariables[$epoch3][OutcomeVariable::class]);
        $this->assertArrayHasKey(
            'MyThirdOutcomeVariableIdentifier',
            $allVariables[$epoch3][OutcomeVariable::class]
        );
        $this->assertIsArray(
            $allVariables[$epoch3][OutcomeVariable::class]['MyThirdOutcomeVariableIdentifier']
        );
        $this->assertArrayHasKey(
            'uri',
            $allVariables[$epoch3][OutcomeVariable::class]['MyThirdOutcomeVariableIdentifier']
        );
        $this->assertEquals(
            'uri32',
            $allVariables[$epoch3][OutcomeVariable::class]['MyThirdOutcomeVariableIdentifier']['uri']
        );

        $this->assertArrayHasKey(
            'var',
            $allVariables[$epoch3][OutcomeVariable::class]['MyThirdOutcomeVariableIdentifier']
        );
        $this->assertEquals(
            $outcomeVariable3,
            $allVariables[$epoch3][OutcomeVariable::class]['MyThirdOutcomeVariableIdentifier']['var']
        );

        $this->assertArrayHasKey(
            'isCorrect',
            $allVariables[$epoch3][OutcomeVariable::class]['MyThirdOutcomeVariableIdentifier']
        );
        $this->assertEquals(
            'unscored',
            $allVariables[$epoch3][OutcomeVariable::class]['MyThirdOutcomeVariableIdentifier']['isCorrect']
        );

        $this->assertArrayHasKey(TraceVariable::class, $allVariables[$epoch3]);
        $this->assertIsArray($allVariables[$epoch3][TraceVariable::class]);
        $this->assertArrayHasKey(
            'MyThirdTraceVariableIdentifier',
            $allVariables[$epoch3][TraceVariable::class]
        );
        $this->assertIsArray(
            $allVariables[$epoch3][TraceVariable::class]['MyThirdTraceVariableIdentifier']
        );
        $this->assertArrayHasKey(
            'uri',
            $allVariables[$epoch3][TraceVariable::class]['MyThirdTraceVariableIdentifier']
        );
        $this->assertEquals(
            'uri33',
            $allVariables[$epoch3][TraceVariable::class]['MyThirdTraceVariableIdentifier']['uri']
        );

        $this->assertArrayHasKey(
            'var',
            $allVariables[$epoch3][TraceVariable::class]['MyThirdTraceVariableIdentifier']
        );
        $this->assertEquals(
            $traceVariable3,
            $allVariables[$epoch3][TraceVariable::class]['MyThirdTraceVariableIdentifier']['var']
        );

        $this->assertArrayHasKey(
            'isCorrect',
            $allVariables[$epoch3][TraceVariable::class]['MyThirdTraceVariableIdentifier']
        );
        $this->assertEquals(
            'unscored',
            $allVariables[$epoch3][TraceVariable::class]['MyThirdTraceVariableIdentifier']['isCorrect']
        );

        $this->assertArrayHasKey(TraceVariable::class, $allVariables[$epoch24]);
        $this->assertIsArray($allVariables[$epoch24][TraceVariable::class]);
        $this->assertArrayHasKey(
            'TraceVariableIdentifier',
            $allVariables[$epoch24][TraceVariable::class]
        );
        $this->assertIsArray(
            $allVariables[$epoch24][TraceVariable::class]['TraceVariableIdentifier']
        );
        $this->assertArrayHasKey(
            'uri',
            $allVariables[$epoch24][TraceVariable::class]['TraceVariableIdentifier']
        );
        $this->assertEquals(
            'uri24',
            $allVariables[$epoch24][TraceVariable::class]['TraceVariableIdentifier']['uri']
        );

        $this->assertArrayHasKey(
            'var',
            $allVariables[$epoch24][TraceVariable::class]['TraceVariableIdentifier']
        );
        $this->assertEquals(
            $traceVariable22,
            $allVariables[$epoch24][TraceVariable::class]['TraceVariableIdentifier']['var']
        );

        $this->assertArrayHasKey(
            'isCorrect',
            $allVariables[$epoch24][TraceVariable::class]['TraceVariableIdentifier']
        );
        $this->assertEquals(
            'unscored',
            $allVariables[$epoch24][TraceVariable::class]['TraceVariableIdentifier']['isCorrect']
        );

        $this->assertIsArray($lastVariables);
        $this->assertEquals([$epoch13, $epoch33, $epoch24], array_keys($lastVariables));

        $this->assertArrayHasKey(TraceVariable::class, $lastVariables[$epoch13]);
        $this->assertIsArray($lastVariables[$epoch13][TraceVariable::class]);
        $this->assertArrayHasKey(
            'MyFirstTraceVariableIdentifier',
            $lastVariables[$epoch13][TraceVariable::class]
        );
        $this->assertIsArray(
            $lastVariables[$epoch13][TraceVariable::class]['MyFirstTraceVariableIdentifier']
        );
        $this->assertArrayHasKey(
            'uri',
            $lastVariables[$epoch13][TraceVariable::class]['MyFirstTraceVariableIdentifier']
        );
        $this->assertEquals(
            'uri13',
            $lastVariables[$epoch13][TraceVariable::class]['MyFirstTraceVariableIdentifier']['uri']
        );

        $this->assertArrayHasKey(
            'var',
            $lastVariables[$epoch13][TraceVariable::class]['MyFirstTraceVariableIdentifier']
        );
        $this->assertEquals(
            $traceVariable1,
            $lastVariables[$epoch13][TraceVariable::class]['MyFirstTraceVariableIdentifier']['var']
        );

        $this->assertArrayHasKey(TraceVariable::class, $lastVariables[$epoch33]);
        $this->assertIsArray($lastVariables[$epoch33][TraceVariable::class]);
        $this->assertArrayHasKey(
            'MyThirdTraceVariableIdentifier',
            $lastVariables[$epoch33][TraceVariable::class]
        );
        $this->assertIsArray(
            $lastVariables[$epoch33][TraceVariable::class]['MyThirdTraceVariableIdentifier']
        );
        $this->assertArrayHasKey(
            'uri',
            $lastVariables[$epoch33][TraceVariable::class]['MyThirdTraceVariableIdentifier']
        );
        $this->assertEquals(
            'uri33',
            $lastVariables[$epoch33][TraceVariable::class]['MyThirdTraceVariableIdentifier']['uri']
        );

        $this->assertArrayHasKey(
            'var',
            $lastVariables[$epoch33][TraceVariable::class]['MyThirdTraceVariableIdentifier']
        );
        $this->assertEquals(
            $traceVariable3,
            $lastVariables[$epoch33][TraceVariable::class]['MyThirdTraceVariableIdentifier']['var']
        );

        $this->assertArrayHasKey(
            'isCorrect',
            $lastVariables[$epoch33][TraceVariable::class]['MyThirdTraceVariableIdentifier']
        );
        $this->assertEquals(
            'unscored',
            $lastVariables[$epoch33][TraceVariable::class]['MyThirdTraceVariableIdentifier']['isCorrect']
        );

        $this->assertArrayHasKey(TraceVariable::class, $lastVariables[$epoch24]);
        $this->assertIsArray($lastVariables[$epoch24][TraceVariable::class]);
        $this->assertArrayHasKey(
            'TraceVariableIdentifier',
            $lastVariables[$epoch24][TraceVariable::class]
        );
        $this->assertIsArray(
            $lastVariables[$epoch24][TraceVariable::class]['TraceVariableIdentifier']
        );
        $this->assertArrayHasKey(
            'uri',
            $lastVariables[$epoch24][TraceVariable::class]['TraceVariableIdentifier']
        );
        $this->assertEquals(
            'uri24',
            $lastVariables[$epoch24][TraceVariable::class]['TraceVariableIdentifier']['uri']
        );

        $this->assertArrayHasKey(
            'var',
            $lastVariables[$epoch24][TraceVariable::class]['TraceVariableIdentifier']
        );
        $this->assertEquals(
            $traceVariable22,
            $lastVariables[$epoch24][TraceVariable::class]['TraceVariableIdentifier']['var']
        );

        $this->assertArrayHasKey(
            'isCorrect',
            $lastVariables[$epoch24][TraceVariable::class]['TraceVariableIdentifier']
        );
        $this->assertEquals(
            'unscored',
            $lastVariables[$epoch24][TraceVariable::class]['TraceVariableIdentifier']['isCorrect']
        );
    }

    public function testCalculateResponseStatistics()
    {
        $itemVar1 = [
            ResponseVariable::class => [
                'variableIdentifier' => [
                    'isCorrect' => 'correct',
                ],
            ],
        ];
        $itemVar2 = [
            ResponseVariable::class => [
                'variableIdentifier' => [
                    'isCorrect' => 'incorrect',
                ],
            ],
        ];
        $itemVar3 = [
            ResponseVariable::class => [
                'variableIdentifier' => [
                    'isCorrect' => 'incorrect',
                ],
            ],
        ];
        $itemVar4 = [
            ResponseVariable::class => [
                'variableIdentifier' => [
                    'isCorrect' => 'unscored',
                ],
            ],
        ];
        $itemVar5 = [
            ResponseVariable::class => [
                'variableIdentifier' => [
                    'isCorrect' => 'incorrect',
                ],
            ],
        ];
        $itemVar6 = [
            'notAValidVariableType' => ['variableIdentifier' => ['isCorrect' => 'correct']]
        ];
        $itemVar7 = [
            ResponseVariable::class => [
                'variableIdentifier' => [
                    'isCorrect' => 'undefinedValue',
                ],
            ],
        ];
        $itemVar8 = [
            ResponseVariable::class => [
                'variableIdentifier' => [
                    'isCorrect' => 'correct',
                ],
            ],
        ];
        $variables = [
            'epoch1' => $itemVar1,
            'epoch2' => $itemVar2,
            'epoch3' => $itemVar3,
            'epoch4' => $itemVar4,
            'epoch5' => $itemVar5,
            'epoch6' => $itemVar6,
            'epoch7' => $itemVar7,
            'epoch8' => $itemVar8
        ];
        $responseStats = $this->service->calculateResponseStatistics($variables);


        $this->assertCount(4, $responseStats);
        $this->assertArrayHasKey('nbResponses', $responseStats);
        $this->assertEquals(7, $responseStats['nbResponses']);
        $this->assertArrayHasKey('nbCorrectResponses', $responseStats);
        $this->assertEquals(2, $responseStats['nbCorrectResponses']);
        $this->assertArrayHasKey('nbIncorrectResponses', $responseStats);
        $this->assertEquals(3, $responseStats['nbIncorrectResponses']);
        $this->assertArrayHasKey('nbUnscoredResponses', $responseStats);
        $this->assertEquals(1, $responseStats['nbUnscoredResponses']);
    }

    public function testGetResultsFromDelivery()
    {
        $prop = new \core_kernel_classes_Property('http://www.w3.org/2000/01/rdf-schema#label');
        $impProphecy = $this->prophesize(ResultManagement::class);

        $deliveryProphecy = $this->prophesize(core_kernel_classes_Resource::class);
        $deliveryProphecy->getUri()->willReturn('#fakeUri');
        $deliveryProphecy->__toString()->willReturn('#fakeUri');

        $delivery = $deliveryProphecy->reveal();
        $user = $this->prophesize(User::class);
        $user->getIdentifier()->willReturn('#fakeTestUri');
        $user->getPropertyValues($prop)->willReturn([]);

        $impProphecy->getRelatedTestCallIds("#fakeUri")->willReturn(["#fakeTestUri"]);
        $impProphecy->getTestTaker('#fakeUri1')->willReturn('#testTaker');
        $columns = [
            new \oat\taoOutcomeUi\model\table\ContextTypePropertyColumn('test_taker', $prop)
        ];
        $impProphecy->getResultByDelivery(['#fakeUri'], $columns)->willReturn([[
            'deliveryResultIdentifier' => '#fakeUri1',
            'testTakerIdentifier' => '123',
            'deliveryIdentifier' => '#fakeUri2',
        ]]);

        $imp = $impProphecy->reveal();
        $resultServerServiceMock = $this->prophesize(ResultServerService::class);
        $resultServerServiceMock->getResultStorage($delivery)->willReturn($imp);

        $userService = $this->prophesize(\tao_models_classes_UserService::class);
        $userService->getUserById('#testTaker')->willReturn($user->reveal());

        $serviceManager = $this->getServiceLocatorMock([
            ResultServerService::SERVICE_ID => $resultServerServiceMock->reveal(),
            \tao_models_classes_UserService::SERVICE_ID => $userService->reveal(),
        ]);
        $this->service->setServiceLocator($serviceManager);


        $varDataAll = $this->service->getResultsByDelivery($delivery, $columns, ['lastSubmitted']);
        $this->assertEquals('#fakeUri1', $varDataAll[0]);
    }
}
