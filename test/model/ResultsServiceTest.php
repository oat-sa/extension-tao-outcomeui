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
namespace oat\taoOutcomeUi\test\model;

use oat\tao\test\TaoPhpUnitTestRunner;
use \common_ext_ExtensionsManager;
use oat\taoOutcomeUi\model\ResultsService;
use Prophecy\Prophet;
use oat\taoDelivery\model\execution\DeliveryExecution;

/**
 * This test case focuses on testing ResultsService.
 *
 * @author Lionel Lecaque <lionel@taotesting.com>
 *        
 */
class ResultsServiceTest extends TaoPhpUnitTestRunner
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
    public function setUp()
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
        $this->assertInstanceOf('core_kernel_classes_Class', $this->service->getRootClass());
        $this->assertEquals('http://www.tao.lu/Ontologies/TAOResult.rdf#DeliveryResult', $this->service->getRootClass()
            ->getUri());
    }

    /**
     * @expectedException \common_exception_Error
     *
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function testGetImplementation()
    {
        $this->service->getImplementation();
    }
    /**
     *
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function testSetImplementation()
    {
        $prophet = new Prophet();
        $impProphecy = $prophet->prophesize('oat\taoOutcomeRds\model\RdsResultStorage');

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
        $prophet = new Prophet();

        $impProphecy = $prophet->prophesize('oat\taoOutcomeRds\model\RdsResultStorage');

        $impProphecy->getRelatedItemCallIds('#fakeUri')->willReturn('#fakeDelivery');
        $imp = $impProphecy->reveal();
        $this->service->setImplementation($imp);

        $this->assertEquals('#fakeDelivery', $this->service->getItemResultsFromDeliveryResult('#fakeUri'));
    }
    /**
     *
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function testGetDelivery()
    {
        $prophet = new Prophet();

        $impProphecy = $prophet->prophesize('oat\taoOutcomeRds\model\RdsResultStorage');
        $impProphecy->getDelivery('#fakeUri')->willReturn('#fakeDelivery');
        $imp = $impProphecy->reveal();

        $this->service->setImplementation($imp);

        $delivery = $this->service->getDelivery('#fakeUri');
        $this->assertInstanceOf('core_kernel_classes_Resource', $delivery);
        $this->assertEquals('#fakeDelivery', $delivery->getUri());
    }
    /**
     *
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function testGetVariablesFromObjectResult()
    {
        $prophet = new Prophet();
        $impProphecy = $prophet->prophesize('oat\taoOutcomeRds\model\RdsResultStorage');

        $variable = new \stdClass();
        $variable->variable = new \taoResultServer_models_classes_ResponseVariable();
        $variable->value = '#bar';
        $impProphecy->getVariables('#foo')->willReturn(array(array($variable)));
        $imp = $impProphecy->reveal();

        $this->service->setImplementation($imp);
        $this->assertContains(array($variable),$this->service->getVariablesFromObjectResult('#foo'));
    }
    /**
     *
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function testGetVariableCandidateResponse()
    {
        $prophet = new Prophet();
        $impProphecy = $prophet->prophesize('oat\taoOutcomeRds\model\RdsResultStorage');

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
        $prophet = new Prophet();
        $impProphecy = $prophet->prophesize('oat\taoOutcomeRds\model\RdsResultStorage');

        $impProphecy->getVariableProperty('#foo', 'baseType')->willReturn(true);
        $imp = $impProphecy->reveal();

        $this->service->setImplementation($imp);
        $this->assertTrue($this->service->getVariableBaseType('#foo'));
    }
    /**
     *
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function testGetItemFromItemResult()
    {
        $prophet = new Prophet();
        $impProphecy = $prophet->prophesize('oat\taoOutcomeRds\model\RdsResultStorage');

        $item = new \stdClass();
        $item->item = '#item';
        $impProphecy->getVariables('#foo')->willReturn(array(
            array(
                $item
            )
        ));
        $imp = $impProphecy->reveal();

        $this->service->setImplementation($imp);

        $resultItem = $this->service->getItemFromItemResult('#foo');
        $this->assertInstanceOf('core_kernel_classes_Resource', $resultItem);
        $this->assertEquals('#item', $resultItem->getUri());
    }
    /**
     *
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function testGetVariableDataFromDeliveryResult()
    {
        $prophet = new Prophet();
        $impProphecy = $prophet->prophesize('oat\taoOutcomeRds\model\RdsResultStorage');

        $first = microtime();

        $var = new \stdClass();
        $var->callIdTest = 'callId';
        $responseVariable = new \taoResultServer_models_classes_ResponseVariable();
        $responseVariable->setIdentifier('myID');
        $second = microtime();
        $responseVariable->setEpoch($second);
        $var->variable = $responseVariable;

        $var2 = new \stdClass();
        $var2->callIdTest = 'callId';
        $outcomeVariable = new \taoResultServer_models_classes_OutcomeVariable();
        $outcomeVariable->setIdentifier('mySecondID');
        $outcomeVariable->setEpoch($first);
        $var2->variable = $outcomeVariable;
        $impProphecy->getRelatedTestCallIds("#fakeUri")->willReturn(array("#fakeTestUri"));
        $impProphecy->getVariables('#fakeTestUri')->willReturn(array(
            array(
                $var
            ),
            array(
                $var2
            )
        ));
        $imp = $impProphecy->reveal();

        $this->service->setImplementation($imp);

        $varDataAll = $this->service->getVariableDataFromDeliveryResult('#fakeUri');
        $this->assertEquals(array(
            $outcomeVariable,
            $responseVariable
        ), $varDataAll);
        $varDataEmpty = $this->service->getVariableDataFromDeliveryResult('#fakeUri', array(\taoResultServer_models_classes_TraceVariable::class));
        $this->assertEmpty($varDataEmpty);
        $varData = $this->service->getVariableDataFromDeliveryResult('#fakeUri', array(\taoResultServer_models_classes_ResponseVariable::class));
        $this->assertEquals(array(
            $responseVariable
        ), $varData);
    }
    /**
     *
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function testGetTestTaker()
    {
        $prophet = new Prophet();
        $impProphecy = $prophet->prophesize('oat\taoOutcomeRds\model\RdsResultStorage');

        $impProphecy->getTestTaker('#fakeUri')->willReturn('#testTaker');
        $imp = $impProphecy->reveal();

        $this->service->setImplementation($imp);

        $item = $this->service->getTestTaker('#fakeUri');
        $this->assertInstanceOf('core_kernel_classes_Resource', $item);
        $this->assertEquals('#testTaker', $item->getUri());
    }
    /**
     *
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function testDeleteResult()
    {
        $prophet = new Prophet();
        $impProphecy = $prophet->prophesize('oat\taoOutcomeRds\model\RdsResultStorage');

        $impProphecy->deleteResult('#foo')->willReturn(true);
        $imp = $impProphecy->reveal();
        $this->service->setImplementation($imp);

        $this->assertTrue($this->service->deleteResult('#foo'));
    }

    /**
     *
     * @author Lionel Lecaque, lionel@taotesting.com
     *         @expectedException \common_exception_Error
     *         @expectedExceptionMessage This delivery has no Result Server
     */
    public function testGetReadableImplementationNoResultStorage()
    {
        $prophet = new Prophet();

        $deliveryProphecy = $prophet->prophesize('core_kernel_classes_Resource');
        $delivery = $deliveryProphecy->reveal();

        $this->service->getReadableImplementation($delivery);
    }

    /**
     *
     * @author Lionel Lecaque, lionel@taotesting.com
     *         @expectedException \common_exception_Error
     *         @expectedExceptionMessage This delivery has no readable Result Server
     */
    public function testGetReadableImplementationNoResultServer()
    {
        $prophet = new Prophet();

        $resultProphecy = $prophet->prophesize('core_kernel_classes_Resource');

        $resultServer = $resultProphecy->reveal();

        $deliveryProphecy = $prophet->prophesize('core_kernel_classes_Resource');

        $deliveryProphecy->getOnePropertyValue(new \core_kernel_classes_Property(TAO_DELIVERY_RESULTSERVER_PROP))->willReturn($resultServer);

        $delivery = $deliveryProphecy->reveal();
        $this->service->getReadableImplementation($delivery);
    }

    /**
     *
     * @author Lionel Lecaque, lionel@taotesting.com
     *         @expectedException \common_exception_Error
     *         @expectedExceptionMessage This delivery has no readable Result Server
     */
    public function testGetReadableImplementationNoResultServerModel()
    {
        $prophet = new Prophet();

        $resultProphecy = $prophet->prophesize('core_kernel_classes_Resource');
        $resultProphecy->getPropertyValues(new \core_kernel_classes_Property(TAO_RESULTSERVER_MODEL_PROP))->willReturn(array(
            '#fakeUri'
        ));
        $resultServer = $resultProphecy->reveal();

        $deliveryProphecy = $prophet->prophesize('core_kernel_classes_Resource');

        $deliveryProphecy->getOnePropertyValue(new \core_kernel_classes_Property(TAO_DELIVERY_RESULTSERVER_PROP))->willReturn($resultServer);

        $delivery = $deliveryProphecy->reveal();
        $this->service->getReadableImplementation($delivery);
    }

    /**
     *
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function testGetReadableImplementation()
    {
        $prophet = new Prophet();

        $resultProphecy = $prophet->prophesize('core_kernel_classes_Resource');
        $resultProphecy->getPropertyValues(new \core_kernel_classes_Property(TAO_RESULTSERVER_MODEL_PROP))->willReturn(array(
            'http://www.tao.lu/Ontologies/taoOutcomeRds.rdf#RdsResultStorageModel'
        ));
        $resultServer = $resultProphecy->reveal();

        $deliveryProphecy = $prophet->prophesize('core_kernel_classes_Resource');

        $deliveryProphecy->getOnePropertyValue(new \core_kernel_classes_Property(TAO_DELIVERY_RESULTSERVER_PROP))->willReturn($resultServer);

        $delivery = $deliveryProphecy->reveal();

        $this->assertInstanceOf('oat\taoOutcomeRds\model\RdsResultStorage', $this->service->getReadableImplementation($delivery));
    }

    public function testGetVariables()
    {
        $prophet = new Prophet();

        $impProphecy = $prophet->prophesize('oat\taoOutcomeRds\model\RdsResultStorage');
        $impProphecy->getRelatedItemCallIds('#fakeUri')->willReturn(array(
            '#itemsResults1' => '#itemResultVariable'
        ));

        $impProphecy->getRelatedTestCallIds('#fakeUri')->willReturn(array(
                '#testResults1' => '#testResultVariable'
            ));

        $variable1 = new \stdClass();
        $variable1->variable = new \taoResultServer_models_classes_ResponseVariable();
        $variable1->value = 'foo';

        $variable2 = new \stdClass();
        $variable2->variable = new \taoResultServer_models_classes_ResponseVariable();
        $variable2->value = 'bar';
        $impProphecy->getVariables('#itemResultVariable')->willReturn(array(array($variable1)));

        $impProphecy->getVariables('#testResultVariable')->willReturn(array(array($variable2)));

        $imp = $impProphecy->reveal();

        $this->service->setImplementation($imp);

        $var = ($this->service->getVariables('#fakeUri'));
        $this->assertContains(array($variable1), $var);

        $var = $this->service->getVariables('#fakeUri', false);
        $this->assertArrayHasKey('#itemResultVariable', $var);
        $this->assertEquals(array(array($variable1)), $var['#itemResultVariable']);

    }

    /**
     *
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function testGetItemVariableDataFromDeliveryResult()
    {
        $prophet = new Prophet();

        $impProphecy = $prophet->prophesize('oat\taoOutcomeRds\model\RdsResultStorage');
        $impProphecy->getRelatedItemCallIds('#fakeUri')->willReturn(array(
            '#itemsResults1' => '#itemResultVariable',
            '#itemsResults2' => '#itemResultVariable2',
            '#itemsResults3' => '#itemResultVariable3'
        ));

        $item = new \stdClass();
        $item->item = '#item';
        $item->uri = '#uri';

        $var = new \taoResultServer_models_classes_TraceVariable();
        $var->setEpoch(microtime());
        $var->setIdentifier('varIdentifier');
        $item->variable = $var;

        $item2 = new \stdClass();
        $item2->item = '#item2';
        $item2->uri = '#uri2';

        $var2 = new \taoResultServer_models_classes_ResponseVariable();
        $var2->setEpoch(microtime());
        $var2->setIdentifier('varIdentifier2');
        // response correct
        $var2->setCorrectResponse(1);
        $item2->variable = $var2;

        $item3 = new \stdClass();
        $item3->item = '#item3';
        $item3->uri = '#uri3';

        $var3 = new \taoResultServer_models_classes_ResponseVariable();
        $var3->setEpoch(microtime());
        $var3->setIdentifier('varIdentifier3');
        // response incorrect
        $var3->setCorrectResponse(0);
        $item3->variable = $var3;

        $impProphecy->getVariables('#itemResultVariable')->willReturn(array(
            array(
                $item
            )
        ));
        $impProphecy->getVariables('#itemResultVariable2')->willReturn(array(
            array(
                $item2
            )
        ));
        $impProphecy->getVariables('#itemResultVariable3')->willReturn(array(
            array(
                $item3
            )
        )
        );

        $imp = $impProphecy->reveal();
        $this->service->setImplementation($imp);

        $itemVar = $this->service->getItemVariableDataFromDeliveryResult('#fakeUri', 'lastSubmitted');

        $this->assertArrayHasKey('#item', $itemVar);
        $this->assertArrayHasKey('itemModel', $itemVar['#item']);
        $this->assertEquals('unknown', $itemVar['#item']['itemModel']);

        $this->assertArrayHasKey('sortedVars', $itemVar['#item']);
        $this->assertArrayHasKey('taoResultServer_models_classes_TraceVariable', $itemVar['#item']['sortedVars']);
        $this->assertArrayHasKey('varIdentifier', $itemVar['#item']['sortedVars']['taoResultServer_models_classes_TraceVariable']);

        $this->assertArrayHasKey('uri', $itemVar['#item']['sortedVars']['taoResultServer_models_classes_TraceVariable']['varIdentifier'][0]);
        $this->assertEquals('#uri', $itemVar['#item']['sortedVars']['taoResultServer_models_classes_TraceVariable']['varIdentifier'][0]['uri']);

        $this->assertArrayHasKey('isCorrect', $itemVar['#item']['sortedVars']['taoResultServer_models_classes_TraceVariable']['varIdentifier'][0]);
        $this->assertEquals('unscored', $itemVar['#item']['sortedVars']['taoResultServer_models_classes_TraceVariable']['varIdentifier'][0]['isCorrect']);

        $this->assertArrayHasKey('var', $itemVar['#item']['sortedVars']['taoResultServer_models_classes_TraceVariable']['varIdentifier'][0]);
        $this->assertInstanceOf('taoResultServer_models_classes_TraceVariable', $itemVar['#item']['sortedVars']['taoResultServer_models_classes_TraceVariable']['varIdentifier'][0]['var']);
        $this->assertEquals($var, $itemVar['#item']['sortedVars']['taoResultServer_models_classes_TraceVariable']['varIdentifier'][0]['var']);

        $this->assertArrayHasKey('label', $itemVar['#item']);

        // item2
        $this->assertArrayHasKey('#item2', $itemVar);
        $this->assertArrayHasKey('itemModel', $itemVar['#item2']);
        $this->assertEquals('unknown', $itemVar['#item2']['itemModel']);

        $this->assertArrayHasKey('sortedVars', $itemVar['#item2']);
        $this->assertArrayHasKey('taoResultServer_models_classes_ResponseVariable', $itemVar['#item2']['sortedVars']);
        $this->assertArrayHasKey('varIdentifier2', $itemVar['#item2']['sortedVars']['taoResultServer_models_classes_ResponseVariable']);

        $this->assertArrayHasKey('uri', $itemVar['#item2']['sortedVars']['taoResultServer_models_classes_ResponseVariable']['varIdentifier2'][0]);
        $this->assertEquals('#uri2', $itemVar['#item2']['sortedVars']['taoResultServer_models_classes_ResponseVariable']['varIdentifier2'][0]['uri']);

        $this->assertArrayHasKey('isCorrect', $itemVar['#item2']['sortedVars']['taoResultServer_models_classes_ResponseVariable']['varIdentifier2'][0]);
        $this->assertEquals('correct', $itemVar['#item2']['sortedVars']['taoResultServer_models_classes_ResponseVariable']['varIdentifier2'][0]['isCorrect']);

        // item3
        $this->assertArrayHasKey('#item3', $itemVar);
        $this->assertArrayHasKey('itemModel', $itemVar['#item3']);
        $this->assertEquals('unknown', $itemVar['#item3']['itemModel']);

        $this->assertArrayHasKey('sortedVars', $itemVar['#item3']);
        $this->assertArrayHasKey('taoResultServer_models_classes_ResponseVariable', $itemVar['#item3']['sortedVars']);
        $this->assertArrayHasKey('varIdentifier3', $itemVar['#item3']['sortedVars']['taoResultServer_models_classes_ResponseVariable']);

        $this->assertArrayHasKey('uri', $itemVar['#item3']['sortedVars']['taoResultServer_models_classes_ResponseVariable']['varIdentifier3'][0]);
        $this->assertEquals('#uri3', $itemVar['#item3']['sortedVars']['taoResultServer_models_classes_ResponseVariable']['varIdentifier3'][0]['uri']);

        $this->assertArrayHasKey('isCorrect', $itemVar['#item3']['sortedVars']['taoResultServer_models_classes_ResponseVariable']['varIdentifier3'][0]);
        $this->assertEquals('incorrect', $itemVar['#item3']['sortedVars']['taoResultServer_models_classes_ResponseVariable']['varIdentifier3'][0]['isCorrect']);
    }

    /**
     * @depends testGetItemVariableDataFromDeliveryResult
     *
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function testGetItemVariableDataStatsFromDeliveryResult()
    {
        $itemVar = $this->service->getItemVariableDataStatsFromDeliveryResult('#fakeUri', 'lastSubmitted');

        $this->assertArrayHasKey('nbResponses', $itemVar);
        $this->assertEquals(2, $itemVar['nbResponses']);
        $this->assertArrayHasKey('nbCorrectResponses', $itemVar);
        $this->assertEquals(1, $itemVar['nbCorrectResponses']);
        $this->assertArrayHasKey('nbIncorrectResponses', $itemVar);
        $this->assertEquals(1, $itemVar['nbIncorrectResponses']);
        $this->assertArrayHasKey('nbUnscoredResponses', $itemVar);
        $this->assertEquals(0,$itemVar['nbUnscoredResponses']);

        $this->assertArrayHasKey('data',$itemVar);
    }

    public function testAllGetStructuredVariables()
    {

        $serviceMock = $this->getMockBuilder('oat\taoOutcomeUi\model\ResultsService')
            ->disableOriginalConstructor()
            ->setMethods(array('getItemFromItemResult'))
            ->getMock();

        $prophet = new Prophet();

        $itemModel1Prophecy = $prophet->prophesize('\core_kernel_classes_Resource');
        $itemModel1Prophecy->getLabel()->willReturn('MyItemModel');

        $relatedItem1Prophecy = $prophet->prophesize('\core_kernel_classes_Resource');
        $relatedItem1Prophecy->getLabel()->willReturn('MyRelatedItem1');
        $relatedItem1Prophecy->getUri()->willReturn('MyRelatedItemUri1');
        $relatedItem1Prophecy->getUniquePropertyValue(new \core_kernel_classes_Property(TAO_ITEM_MODEL_PROPERTY))->willReturn($itemModel1Prophecy->reveal());

        $itemModel2Prophecy = $prophet->prophesize('\core_kernel_classes_Resource');
        $itemModel2Prophecy->getLabel()->willReturn('MySecondItemModel');

        $relatedItem2Prophecy = $prophet->prophesize('\core_kernel_classes_Resource');
        $relatedItem2Prophecy->getLabel()->willReturn('MyRelatedItem2');
        $relatedItem2Prophecy->getUri()->willReturn('MyRelatedItemUri2');
        $relatedItem2Prophecy->getUniquePropertyValue(new \core_kernel_classes_Property(TAO_ITEM_MODEL_PROPERTY))->willReturn($itemModel2Prophecy->reveal());

        $itemModel3Prophecy = $prophet->prophesize('\core_kernel_classes_Resource');
        $itemModel3Prophecy->getLabel()->willReturn('MyThirdItemModel');

        $relatedItem3Prophecy = $prophet->prophesize('\core_kernel_classes_Resource');
        $relatedItem3Prophecy->getLabel()->willReturn('MyRelatedItem3');
        $relatedItem3Prophecy->getUri()->willReturn('MyRelatedItemUri3');
        $relatedItem3Prophecy->getUniquePropertyValue(new \core_kernel_classes_Property(TAO_ITEM_MODEL_PROPERTY))->willReturn($itemModel3Prophecy->reveal());


        //Variables for Item 1
        $callId1 = 'callId1';
        $epoch1 = microtime();
        $variable1 = new \stdClass();
        $responseVariable1 = new \taoResultServer_models_classes_ResponseVariable();
        $responseVariable1->setValue('myValue');
        $responseVariable1->setCorrectResponse(1);
        $responseVariable1->setBaseType('string');
        $responseVariable1->setCardinality('single');
        $responseVariable1->setEpoch($epoch1);
        $responseVariable1->setIdentifier('MyFirstResponseVariableIdentifier');
        $variable1->uri = 'uri11';
        $variable1->class = 'taoResultServer_models_classes_ResponseVariable';
        $variable1->deliveryResultIdentifier = '#fakeUri';
        $variable1->callIdItem = 'callId1';
        $variable1->callIdTest = '';
        $variable1->test = '#fakeTestUri';
        $variable1->item = 'MyRelatedItemUri1';
        $variable1->variable = $responseVariable1;

        $epoch12 = microtime();
        $variable12 = new \stdClass();
        $outcomeVariable1 = new \taoResultServer_models_classes_OutcomeVariable();
        $outcomeVariable1->setValue('myOutcomeValue');
        $outcomeVariable1->setBaseType('string');
        $outcomeVariable1->setCardinality('multiple');
        $outcomeVariable1->setEpoch($epoch12);
        $outcomeVariable1->setIdentifier('MyFirstOutcomeVariableIdentifier');
        $outcomeVariable1->setNormalMaximum(10);
        $outcomeVariable1->setNormalMinimum(1);
        $variable12->uri = 'uri12';
        $variable12->class = 'taoResultServer_models_classes_OutcomeVariable';
        $variable12->deliveryResultIdentifier = '#fakeUri';
        $variable12->callIdItem = 'callId1';
        $variable12->callIdTest = '';
        $variable12->test = '#fakeTestUri';
        $variable12->item = 'MyRelatedItemUri1';
        $variable12->variable = $outcomeVariable1;

        $epoch13 = microtime();
        $variable13 = new \stdClass();
        $traceVariable1 = new \taoResultServer_models_classes_TraceVariable();
        $traceVariable1->setValue(true);
        $traceVariable1->setTrace('my first trace');
        $traceVariable1->setBaseType('boolean');
        $traceVariable1->setCardinality('single');
        $traceVariable1->setEpoch($epoch13);
        $traceVariable1->setIdentifier('MyFirstTraceVariableIdentifier');
        $variable13->uri = 'uri13';
        $variable13->class = 'taoResultServer_models_classes_TraceVariable';
        $variable13->deliveryResultIdentifier = '#fakeUri';
        $variable13->callIdItem = 'callId1';
        $variable13->callIdTest = '';
        $variable13->test = '#fakeTestUri';
        $variable13->item = 'MyRelatedItemUri1';
        $variable13->variable = $traceVariable1;


        $variables1 = array(
            'uri11' => array(
                $variable1
            ),
            'uri12' => array(
                $variable12
            ),
            'uri13' => array(
                $variable13
            )
        );

        //Variables for Item 2
        $callId2 = 'callId2';
        $epoch2 = microtime();
        $variable2 = new \stdClass();
        $responseVariable2 = new \taoResultServer_models_classes_ResponseVariable();
        $responseVariable2->setValue('myValue');
        $responseVariable2->setCorrectResponse(0);
        $responseVariable2->setBaseType('string');
        $responseVariable2->setCardinality('single');
        $responseVariable2->setEpoch($epoch2);
        $responseVariable2->setIdentifier('MySecondResponseVariableIdentifier');
        $variable2->uri = 'uri21';
        $variable2->class = 'taoResultServer_models_classes_ResponseVariable';
        $variable2->deliveryResultIdentifier = '#fakeUri';
        $variable2->callIdItem = 'callId2';
        $variable2->callIdTest = '';
        $variable2->test = '#fakeTestUri';
        $variable2->item = 'MyRelatedItemUri2';
        $variable2->variable = $responseVariable2;

        $epoch22 = microtime();
        $variable22 = new \stdClass();
        $outcomeVariable2 = new \taoResultServer_models_classes_OutcomeVariable();
        $outcomeVariable2->setValue('myOutcomeValue');
        $outcomeVariable2->setBaseType('string');
        $outcomeVariable2->setCardinality('multiple');
        $outcomeVariable2->setEpoch($epoch22);
        $outcomeVariable2->setIdentifier('MySecondOutcomeVariableIdentifier');
        $outcomeVariable2->setNormalMaximum(10);
        $outcomeVariable2->setNormalMinimum(1);
        $variable22->uri = 'uri22';
        $variable22->class = 'taoResultServer_models_classes_OutcomeVariable';
        $variable22->deliveryResultIdentifier = '#fakeUri';
        $variable22->callIdItem = 'callId2';
        $variable22->callIdTest = '';
        $variable22->test = '#fakeTestUri';
        $variable22->item = 'MyRelatedItemUri2';
        $variable22->variable = $outcomeVariable2;

        $epoch23 = microtime();
        $variable23 = new \stdClass();
        $traceVariable2 = new \taoResultServer_models_classes_TraceVariable();
        $traceVariable2->setValue(true);
        $traceVariable2->setTrace('my second trace');
        $traceVariable2->setBaseType('boolean');
        $traceVariable2->setCardinality('single');
        $traceVariable2->setEpoch($epoch23);
        $traceVariable2->setIdentifier('MySecondTraceVariableIdentifier');
        $variable23->uri = 'uri23';
        $variable23->class = 'taoResultServer_models_classes_TraceVariable';
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
        $responseVariable3 = new \taoResultServer_models_classes_ResponseVariable();
        $responseVariable3->setValue('myValue');
        $responseVariable3->setCorrectResponse(1);
        $responseVariable3->setBaseType('string');
        $responseVariable3->setCardinality('single');
        $responseVariable3->setEpoch($epoch3);
        $responseVariable3->setIdentifier('MyThirdResponseVariableIdentifier');
        $variable3->uri = 'uri31';
        $variable3->class = 'taoResultServer_models_classes_ResponseVariable';
        $variable3->deliveryResultIdentifier = '#fakeUri';
        $variable3->callIdItem = 'callId3';
        $variable3->callIdTest = '';
        $variable3->test = '#fakeTestUri';
        $variable3->item = 'MyRelatedItemUri3';
        $variable3->variable = $responseVariable3;

        $epoch32 = microtime();
        $variable32 = new \stdClass();
        $outcomeVariable3 = new \taoResultServer_models_classes_OutcomeVariable();
        $outcomeVariable3->setValue('myOutcomeValue');
        $outcomeVariable3->setBaseType('string');
        $outcomeVariable3->setCardinality('multiple');
        $outcomeVariable3->setEpoch($epoch32);
        $outcomeVariable3->setIdentifier('MyThirdOutcomeVariableIdentifier');
        $outcomeVariable3->setNormalMaximum(10);
        $outcomeVariable3->setNormalMinimum(1);
        $variable32->uri = 'uri32';
        $variable32->class = 'taoResultServer_models_classes_OutcomeVariable';
        $variable32->deliveryResultIdentifier = '#fakeUri';
        $variable32->callIdItem = 'callId3';
        $variable32->callIdTest = '';
        $variable32->test = '#fakeTestUri';
        $variable32->item = 'MyRelatedItemUri3';
        $variable32->variable = $outcomeVariable3;

        $epoch33 = microtime();
        $variable33 = new \stdClass();
        $traceVariable3 = new \taoResultServer_models_classes_TraceVariable();
        $traceVariable3->setValue(true);
        $traceVariable3->setTrace('my third trace');
        $traceVariable3->setBaseType('boolean');
        $traceVariable3->setCardinality('single');
        $traceVariable3->setEpoch($epoch33);
        $traceVariable3->setIdentifier('MyThirdTraceVariableIdentifier');
        $variable33->uri = 'uri33';
        $variable33->class = 'taoResultServer_models_classes_TraceVariable';
        $variable33->deliveryResultIdentifier = '#fakeUri';
        $variable33->callIdItem = 'callId3';
        $variable33->callIdTest = '';
        $variable33->test = '#fakeTestUri';
        $variable33->item = 'MyRelatedItemUri3';
        $variable33->variable = $traceVariable3;

        $epoch24 = microtime();
        $variable24 = new \stdClass();
        $traceVariable22 = new \taoResultServer_models_classes_TraceVariable();
        $traceVariable22->setValue(true);
        $traceVariable22->setTrace('my forth trace');
        $traceVariable22->setBaseType('boolean');
        $traceVariable22->setCardinality('single');
        $traceVariable22->setEpoch($epoch24);
        $traceVariable22->setIdentifier('TraceVariableIdentifier');
        $variable24->uri = 'uri24';
        $variable24->class = 'taoResultServer_models_classes_TraceVariable';
        $variable24->deliveryResultIdentifier = '#fakeUri';
        $variable24->callIdItem = 'callId2';
        $variable24->callIdTest = '';
        $variable24->test = '#fakeTestUri';
        $variable24->item = 'MyRelatedItemUri2';
        $variable24->variable = $traceVariable22;



        $variables2 = array(
            'uri21' => array(
                $variable2
            ),
            'uri22' => array(
                $variable22
            ),
            'uri23' => array(
                $variable23
            ),
            'uri24' => array(
                $variable24
            )
        );

        $variables3 = array(
            'uri31' => array(
                $variable3
            ),
            'uri32' => array(
                $variable32
            ),
            'uri33' => array(
                $variable33
            )
        );


        $callIds = array($callId1, $callId2, $callId3);

        $relatedItem1 = $relatedItem1Prophecy->reveal();
        $relatedItem2 = $relatedItem2Prophecy->reveal();
        $relatedItem3 = $relatedItem3Prophecy->reveal();

        $serviceMock->expects($this->exactly(8))
            ->method('getItemFromItemResult')
            ->withConsecutive(
                [$callId1, array(array($variable1))],
                [$callId2, array(array($variable2))],
                [$callId3, array(array($variable3))],
                [$callId2, array(array($variable24))],
                [$callId1, array(array($variable13))],
                [$callId2, array(array($variable23))],
                [$callId3, array(array($variable33))],
                [$callId2, array(array($variable24))]
            )
            ->willReturnOnConsecutiveCalls($relatedItem1, $relatedItem2, $relatedItem3, $relatedItem2,$relatedItem1, $relatedItem2, $relatedItem3, $relatedItem2);


        $impProphecy = $prophet->prophesize('oat\taoOutcomeRds\model\RdsResultStorage');
        $impProphecy->getRelatedItemCallIds('DeliveryExecutionIdentifier')->willReturn($callIds);
        $impProphecy->getVariables($callId1)->willReturn($variables1);
        $impProphecy->getVariables($callId2)->willReturn($variables2);
        $impProphecy->getVariables($callId3)->willReturn($variables3);
        $serviceMock->setImplementation($impProphecy->reveal());

        $allVariables = $serviceMock->getStructuredVariables('DeliveryExecutionIdentifier', 'all', array(\taoResultServer_models_classes_ResponseVariable::class,\taoResultServer_models_classes_OutcomeVariable::class, \taoResultServer_models_classes_TraceVariable::class));

        $lastVariables = $serviceMock->getStructuredVariables('DeliveryExecutionIdentifier', 'lastSubmitted', array(\taoResultServer_models_classes_TraceVariable::class));

        $this->assertInternalType('array', $allVariables);

        $this->assertEquals(array($epoch1,$epoch2,$epoch3,$epoch24), array_keys($allVariables));

        $this->assertInternalType('array', $allVariables[$epoch1]);
        $this->assertArrayHasKey('itemModel', $allVariables[$epoch1]);
        $this->assertEquals('MyItemModel', $allVariables[$epoch1]['itemModel']);
        $this->assertArrayHasKey('label', $allVariables[$epoch1]);
        $this->assertEquals('MyRelatedItem1', $allVariables[$epoch1]['label']);
        $this->assertArrayHasKey('uri', $allVariables[$epoch1]);
        $this->assertEquals('MyRelatedItemUri1', $allVariables[$epoch1]['uri']);

        $this->assertInternalType('array', $allVariables[$epoch2]);
        $this->assertArrayHasKey('itemModel', $allVariables[$epoch2]);
        $this->assertEquals('MySecondItemModel', $allVariables[$epoch2]['itemModel']);
        $this->assertArrayHasKey('label', $allVariables[$epoch2]);
        $this->assertEquals('MyRelatedItem2', $allVariables[$epoch2]['label']);
        $this->assertArrayHasKey('uri', $allVariables[$epoch2]);
        $this->assertEquals('MyRelatedItemUri2', $allVariables[$epoch2]['uri']);

        $this->assertInternalType('array', $allVariables[$epoch3]);
        $this->assertArrayHasKey('itemModel', $allVariables[$epoch3]);
        $this->assertEquals('MyThirdItemModel', $allVariables[$epoch3]['itemModel']);
        $this->assertArrayHasKey('label', $allVariables[$epoch3]);
        $this->assertEquals('MyRelatedItem3', $allVariables[$epoch3]['label']);
        $this->assertArrayHasKey('uri', $allVariables[$epoch3]);
        $this->assertEquals('MyRelatedItemUri3', $allVariables[$epoch3]['uri']);

        $this->assertArrayHasKey('taoResultServer_models_classes_ResponseVariable', $allVariables[$epoch1]);
        $this->assertInternalType('array', $allVariables[$epoch1]['taoResultServer_models_classes_ResponseVariable']);
        $this->assertArrayHasKey('MyFirstResponseVariableIdentifier', $allVariables[$epoch1]['taoResultServer_models_classes_ResponseVariable']);
        $this->assertInternalType('array', $allVariables[$epoch1]['taoResultServer_models_classes_ResponseVariable']['MyFirstResponseVariableIdentifier']);
        $this->assertArrayHasKey('uri', $allVariables[$epoch1]['taoResultServer_models_classes_ResponseVariable']['MyFirstResponseVariableIdentifier']);
        $this->assertEquals('uri11', $allVariables[$epoch1]['taoResultServer_models_classes_ResponseVariable']['MyFirstResponseVariableIdentifier']['uri']);

        $this->assertArrayHasKey('var', $allVariables[$epoch1]['taoResultServer_models_classes_ResponseVariable']['MyFirstResponseVariableIdentifier']);
        $this->assertEquals($responseVariable1, $allVariables[$epoch1]['taoResultServer_models_classes_ResponseVariable']['MyFirstResponseVariableIdentifier']['var']);

        $this->assertArrayHasKey('isCorrect', $allVariables[$epoch1]['taoResultServer_models_classes_ResponseVariable']['MyFirstResponseVariableIdentifier']);
        $this->assertEquals('correct', $allVariables[$epoch1]['taoResultServer_models_classes_ResponseVariable']['MyFirstResponseVariableIdentifier']['isCorrect']);

        $this->assertArrayHasKey('taoResultServer_models_classes_OutcomeVariable', $allVariables[$epoch1]);
        $this->assertInternalType('array', $allVariables[$epoch1]['taoResultServer_models_classes_OutcomeVariable']);
        $this->assertArrayHasKey('MyFirstOutcomeVariableIdentifier', $allVariables[$epoch1]['taoResultServer_models_classes_OutcomeVariable']);
        $this->assertInternalType('array', $allVariables[$epoch1]['taoResultServer_models_classes_OutcomeVariable']['MyFirstOutcomeVariableIdentifier']);
        $this->assertArrayHasKey('uri', $allVariables[$epoch1]['taoResultServer_models_classes_OutcomeVariable']['MyFirstOutcomeVariableIdentifier']);
        $this->assertEquals('uri12', $allVariables[$epoch1]['taoResultServer_models_classes_OutcomeVariable']['MyFirstOutcomeVariableIdentifier']['uri']);

        $this->assertArrayHasKey('var', $allVariables[$epoch1]['taoResultServer_models_classes_OutcomeVariable']['MyFirstOutcomeVariableIdentifier']);
        $this->assertEquals($outcomeVariable1, $allVariables[$epoch1]['taoResultServer_models_classes_OutcomeVariable']['MyFirstOutcomeVariableIdentifier']['var']);

        $this->assertArrayHasKey('isCorrect', $allVariables[$epoch1]['taoResultServer_models_classes_OutcomeVariable']['MyFirstOutcomeVariableIdentifier']);
        $this->assertEquals('unscored', $allVariables[$epoch1]['taoResultServer_models_classes_OutcomeVariable']['MyFirstOutcomeVariableIdentifier']['isCorrect']);

        $this->assertArrayHasKey('taoResultServer_models_classes_TraceVariable', $allVariables[$epoch1]);
        $this->assertInternalType('array', $allVariables[$epoch1]['taoResultServer_models_classes_TraceVariable']);
        $this->assertArrayHasKey('MyFirstTraceVariableIdentifier', $allVariables[$epoch1]['taoResultServer_models_classes_TraceVariable']);
        $this->assertInternalType('array', $allVariables[$epoch1]['taoResultServer_models_classes_TraceVariable']['MyFirstTraceVariableIdentifier']);
        $this->assertArrayHasKey('uri', $allVariables[$epoch1]['taoResultServer_models_classes_TraceVariable']['MyFirstTraceVariableIdentifier']);
        $this->assertEquals('uri13', $allVariables[$epoch1]['taoResultServer_models_classes_TraceVariable']['MyFirstTraceVariableIdentifier']['uri']);

        $this->assertArrayHasKey('var', $allVariables[$epoch1]['taoResultServer_models_classes_TraceVariable']['MyFirstTraceVariableIdentifier']);
        $this->assertEquals($traceVariable1, $allVariables[$epoch1]['taoResultServer_models_classes_TraceVariable']['MyFirstTraceVariableIdentifier']['var']);

        $this->assertArrayHasKey('isCorrect', $allVariables[$epoch1]['taoResultServer_models_classes_TraceVariable']['MyFirstTraceVariableIdentifier']);
        $this->assertEquals('unscored', $allVariables[$epoch1]['taoResultServer_models_classes_TraceVariable']['MyFirstTraceVariableIdentifier']['isCorrect']);


        $this->assertArrayHasKey('taoResultServer_models_classes_ResponseVariable', $allVariables[$epoch2]);
        $this->assertInternalType('array', $allVariables[$epoch2]['taoResultServer_models_classes_ResponseVariable']);
        $this->assertArrayHasKey('MySecondResponseVariableIdentifier', $allVariables[$epoch2]['taoResultServer_models_classes_ResponseVariable']);
        $this->assertInternalType('array', $allVariables[$epoch2]['taoResultServer_models_classes_ResponseVariable']['MySecondResponseVariableIdentifier']);
        $this->assertArrayHasKey('uri', $allVariables[$epoch2]['taoResultServer_models_classes_ResponseVariable']['MySecondResponseVariableIdentifier']);
        $this->assertEquals('uri21', $allVariables[$epoch2]['taoResultServer_models_classes_ResponseVariable']['MySecondResponseVariableIdentifier']['uri']);

        $this->assertArrayHasKey('var', $allVariables[$epoch2]['taoResultServer_models_classes_ResponseVariable']['MySecondResponseVariableIdentifier']);
        $this->assertEquals($responseVariable2, $allVariables[$epoch2]['taoResultServer_models_classes_ResponseVariable']['MySecondResponseVariableIdentifier']['var']);

        $this->assertArrayHasKey('isCorrect', $allVariables[$epoch2]['taoResultServer_models_classes_ResponseVariable']['MySecondResponseVariableIdentifier']);
        $this->assertEquals('incorrect', $allVariables[$epoch2]['taoResultServer_models_classes_ResponseVariable']['MySecondResponseVariableIdentifier']['isCorrect']);

        $this->assertArrayHasKey('taoResultServer_models_classes_OutcomeVariable', $allVariables[$epoch2]);
        $this->assertInternalType('array', $allVariables[$epoch2]['taoResultServer_models_classes_OutcomeVariable']);
        $this->assertArrayHasKey('MySecondOutcomeVariableIdentifier', $allVariables[$epoch2]['taoResultServer_models_classes_OutcomeVariable']);
        $this->assertInternalType('array', $allVariables[$epoch2]['taoResultServer_models_classes_OutcomeVariable']['MySecondOutcomeVariableIdentifier']);
        $this->assertArrayHasKey('uri', $allVariables[$epoch2]['taoResultServer_models_classes_OutcomeVariable']['MySecondOutcomeVariableIdentifier']);
        $this->assertEquals('uri22', $allVariables[$epoch2]['taoResultServer_models_classes_OutcomeVariable']['MySecondOutcomeVariableIdentifier']['uri']);

        $this->assertArrayHasKey('var', $allVariables[$epoch2]['taoResultServer_models_classes_OutcomeVariable']['MySecondOutcomeVariableIdentifier']);
        $this->assertEquals($outcomeVariable2, $allVariables[$epoch2]['taoResultServer_models_classes_OutcomeVariable']['MySecondOutcomeVariableIdentifier']['var']);

        $this->assertArrayHasKey('isCorrect', $allVariables[$epoch2]['taoResultServer_models_classes_OutcomeVariable']['MySecondOutcomeVariableIdentifier']);
        $this->assertEquals('unscored', $allVariables[$epoch2]['taoResultServer_models_classes_OutcomeVariable']['MySecondOutcomeVariableIdentifier']['isCorrect']);

        $this->assertArrayHasKey('taoResultServer_models_classes_TraceVariable', $allVariables[$epoch2]);
        $this->assertInternalType('array', $allVariables[$epoch2]['taoResultServer_models_classes_TraceVariable']);
        $this->assertArrayHasKey('MySecondTraceVariableIdentifier', $allVariables[$epoch2]['taoResultServer_models_classes_TraceVariable']);
        $this->assertInternalType('array', $allVariables[$epoch2]['taoResultServer_models_classes_TraceVariable']['MySecondTraceVariableIdentifier']);
        $this->assertArrayHasKey('uri', $allVariables[$epoch2]['taoResultServer_models_classes_TraceVariable']['MySecondTraceVariableIdentifier']);
        $this->assertEquals('uri23', $allVariables[$epoch2]['taoResultServer_models_classes_TraceVariable']['MySecondTraceVariableIdentifier']['uri']);

        $this->assertArrayHasKey('var', $allVariables[$epoch2]['taoResultServer_models_classes_TraceVariable']['MySecondTraceVariableIdentifier']);
        $this->assertEquals($traceVariable2, $allVariables[$epoch2]['taoResultServer_models_classes_TraceVariable']['MySecondTraceVariableIdentifier']['var']);

        $this->assertArrayHasKey('isCorrect', $allVariables[$epoch2]['taoResultServer_models_classes_TraceVariable']['MySecondTraceVariableIdentifier']);
        $this->assertEquals('unscored', $allVariables[$epoch2]['taoResultServer_models_classes_TraceVariable']['MySecondTraceVariableIdentifier']['isCorrect']);

        $this->assertArrayHasKey('taoResultServer_models_classes_ResponseVariable', $allVariables[$epoch3]);
        $this->assertInternalType('array', $allVariables[$epoch3]['taoResultServer_models_classes_ResponseVariable']);
        $this->assertArrayHasKey('MyThirdResponseVariableIdentifier', $allVariables[$epoch3]['taoResultServer_models_classes_ResponseVariable']);
        $this->assertInternalType('array', $allVariables[$epoch3]['taoResultServer_models_classes_ResponseVariable']['MyThirdResponseVariableIdentifier']);
        $this->assertArrayHasKey('uri', $allVariables[$epoch3]['taoResultServer_models_classes_ResponseVariable']['MyThirdResponseVariableIdentifier']);
        $this->assertEquals('uri31', $allVariables[$epoch3]['taoResultServer_models_classes_ResponseVariable']['MyThirdResponseVariableIdentifier']['uri']);

        $this->assertArrayHasKey('var', $allVariables[$epoch3]['taoResultServer_models_classes_ResponseVariable']['MyThirdResponseVariableIdentifier']);
        $this->assertEquals($responseVariable3, $allVariables[$epoch3]['taoResultServer_models_classes_ResponseVariable']['MyThirdResponseVariableIdentifier']['var']);

        $this->assertArrayHasKey('isCorrect', $allVariables[$epoch3]['taoResultServer_models_classes_ResponseVariable']['MyThirdResponseVariableIdentifier']);
        $this->assertEquals('correct', $allVariables[$epoch3]['taoResultServer_models_classes_ResponseVariable']['MyThirdResponseVariableIdentifier']['isCorrect']);

        $this->assertArrayHasKey('taoResultServer_models_classes_OutcomeVariable', $allVariables[$epoch3]);
        $this->assertInternalType('array', $allVariables[$epoch3]['taoResultServer_models_classes_OutcomeVariable']);
        $this->assertArrayHasKey('MyThirdOutcomeVariableIdentifier', $allVariables[$epoch3]['taoResultServer_models_classes_OutcomeVariable']);
        $this->assertInternalType('array', $allVariables[$epoch3]['taoResultServer_models_classes_OutcomeVariable']['MyThirdOutcomeVariableIdentifier']);
        $this->assertArrayHasKey('uri', $allVariables[$epoch3]['taoResultServer_models_classes_OutcomeVariable']['MyThirdOutcomeVariableIdentifier']);
        $this->assertEquals('uri32', $allVariables[$epoch3]['taoResultServer_models_classes_OutcomeVariable']['MyThirdOutcomeVariableIdentifier']['uri']);

        $this->assertArrayHasKey('var', $allVariables[$epoch3]['taoResultServer_models_classes_OutcomeVariable']['MyThirdOutcomeVariableIdentifier']);
        $this->assertEquals($outcomeVariable3, $allVariables[$epoch3]['taoResultServer_models_classes_OutcomeVariable']['MyThirdOutcomeVariableIdentifier']['var']);

        $this->assertArrayHasKey('isCorrect', $allVariables[$epoch3]['taoResultServer_models_classes_OutcomeVariable']['MyThirdOutcomeVariableIdentifier']);
        $this->assertEquals('unscored', $allVariables[$epoch3]['taoResultServer_models_classes_OutcomeVariable']['MyThirdOutcomeVariableIdentifier']['isCorrect']);

        $this->assertArrayHasKey('taoResultServer_models_classes_TraceVariable', $allVariables[$epoch3]);
        $this->assertInternalType('array', $allVariables[$epoch3]['taoResultServer_models_classes_TraceVariable']);
        $this->assertArrayHasKey('MyThirdTraceVariableIdentifier', $allVariables[$epoch3]['taoResultServer_models_classes_TraceVariable']);
        $this->assertInternalType('array', $allVariables[$epoch3]['taoResultServer_models_classes_TraceVariable']['MyThirdTraceVariableIdentifier']);
        $this->assertArrayHasKey('uri', $allVariables[$epoch3]['taoResultServer_models_classes_TraceVariable']['MyThirdTraceVariableIdentifier']);
        $this->assertEquals('uri33', $allVariables[$epoch3]['taoResultServer_models_classes_TraceVariable']['MyThirdTraceVariableIdentifier']['uri']);

        $this->assertArrayHasKey('var', $allVariables[$epoch3]['taoResultServer_models_classes_TraceVariable']['MyThirdTraceVariableIdentifier']);
        $this->assertEquals($traceVariable3, $allVariables[$epoch3]['taoResultServer_models_classes_TraceVariable']['MyThirdTraceVariableIdentifier']['var']);

        $this->assertArrayHasKey('isCorrect', $allVariables[$epoch3]['taoResultServer_models_classes_TraceVariable']['MyThirdTraceVariableIdentifier']);
        $this->assertEquals('unscored', $allVariables[$epoch3]['taoResultServer_models_classes_TraceVariable']['MyThirdTraceVariableIdentifier']['isCorrect']);

        $this->assertArrayHasKey('taoResultServer_models_classes_TraceVariable', $allVariables[$epoch24]);
        $this->assertInternalType('array', $allVariables[$epoch24]['taoResultServer_models_classes_TraceVariable']);
        $this->assertArrayHasKey('TraceVariableIdentifier', $allVariables[$epoch24]['taoResultServer_models_classes_TraceVariable']);
        $this->assertInternalType('array', $allVariables[$epoch24]['taoResultServer_models_classes_TraceVariable']['TraceVariableIdentifier']);
        $this->assertArrayHasKey('uri', $allVariables[$epoch24]['taoResultServer_models_classes_TraceVariable']['TraceVariableIdentifier']);
        $this->assertEquals('uri24', $allVariables[$epoch24]['taoResultServer_models_classes_TraceVariable']['TraceVariableIdentifier']['uri']);

        $this->assertArrayHasKey('var', $allVariables[$epoch24]['taoResultServer_models_classes_TraceVariable']['TraceVariableIdentifier']);
        $this->assertEquals($traceVariable22, $allVariables[$epoch24]['taoResultServer_models_classes_TraceVariable']['TraceVariableIdentifier']['var']);

        $this->assertArrayHasKey('isCorrect', $allVariables[$epoch24]['taoResultServer_models_classes_TraceVariable']['TraceVariableIdentifier']);
        $this->assertEquals('unscored', $allVariables[$epoch24]['taoResultServer_models_classes_TraceVariable']['TraceVariableIdentifier']['isCorrect']);

        $this->assertInternalType('array', $lastVariables);
        $this->assertEquals(array($epoch13, $epoch33, $epoch24), array_keys($lastVariables));

        $this->assertArrayHasKey('taoResultServer_models_classes_TraceVariable', $lastVariables[$epoch13]);
        $this->assertInternalType('array', $lastVariables[$epoch13]['taoResultServer_models_classes_TraceVariable']);
        $this->assertArrayHasKey('MyFirstTraceVariableIdentifier', $lastVariables[$epoch13]['taoResultServer_models_classes_TraceVariable']);
        $this->assertInternalType('array', $lastVariables[$epoch13]['taoResultServer_models_classes_TraceVariable']['MyFirstTraceVariableIdentifier']);
        $this->assertArrayHasKey('uri', $lastVariables[$epoch13]['taoResultServer_models_classes_TraceVariable']['MyFirstTraceVariableIdentifier']);
        $this->assertEquals('uri13', $lastVariables[$epoch13]['taoResultServer_models_classes_TraceVariable']['MyFirstTraceVariableIdentifier']['uri']);

        $this->assertArrayHasKey('var', $lastVariables[$epoch13]['taoResultServer_models_classes_TraceVariable']['MyFirstTraceVariableIdentifier']);
        $this->assertEquals($traceVariable1, $lastVariables[$epoch13]['taoResultServer_models_classes_TraceVariable']['MyFirstTraceVariableIdentifier']['var']);

        $this->assertArrayHasKey('taoResultServer_models_classes_TraceVariable', $lastVariables[$epoch33]);
        $this->assertInternalType('array', $lastVariables[$epoch33]['taoResultServer_models_classes_TraceVariable']);
        $this->assertArrayHasKey('MyThirdTraceVariableIdentifier', $lastVariables[$epoch33]['taoResultServer_models_classes_TraceVariable']);
        $this->assertInternalType('array', $lastVariables[$epoch33]['taoResultServer_models_classes_TraceVariable']['MyThirdTraceVariableIdentifier']);
        $this->assertArrayHasKey('uri', $lastVariables[$epoch33]['taoResultServer_models_classes_TraceVariable']['MyThirdTraceVariableIdentifier']);
        $this->assertEquals('uri33', $lastVariables[$epoch33]['taoResultServer_models_classes_TraceVariable']['MyThirdTraceVariableIdentifier']['uri']);

        $this->assertArrayHasKey('var', $lastVariables[$epoch33]['taoResultServer_models_classes_TraceVariable']['MyThirdTraceVariableIdentifier']);
        $this->assertEquals($traceVariable3, $lastVariables[$epoch33]['taoResultServer_models_classes_TraceVariable']['MyThirdTraceVariableIdentifier']['var']);

        $this->assertArrayHasKey('isCorrect', $lastVariables[$epoch33]['taoResultServer_models_classes_TraceVariable']['MyThirdTraceVariableIdentifier']);
        $this->assertEquals('unscored', $lastVariables[$epoch33]['taoResultServer_models_classes_TraceVariable']['MyThirdTraceVariableIdentifier']['isCorrect']);

        $this->assertArrayHasKey('taoResultServer_models_classes_TraceVariable', $lastVariables[$epoch24]);
        $this->assertInternalType('array', $lastVariables[$epoch24]['taoResultServer_models_classes_TraceVariable']);
        $this->assertArrayHasKey('TraceVariableIdentifier', $lastVariables[$epoch24]['taoResultServer_models_classes_TraceVariable']);
        $this->assertInternalType('array', $lastVariables[$epoch24]['taoResultServer_models_classes_TraceVariable']['TraceVariableIdentifier']);
        $this->assertArrayHasKey('uri', $lastVariables[$epoch24]['taoResultServer_models_classes_TraceVariable']['TraceVariableIdentifier']);
        $this->assertEquals('uri24', $lastVariables[$epoch24]['taoResultServer_models_classes_TraceVariable']['TraceVariableIdentifier']['uri']);

        $this->assertArrayHasKey('var', $lastVariables[$epoch24]['taoResultServer_models_classes_TraceVariable']['TraceVariableIdentifier']);
        $this->assertEquals($traceVariable22, $lastVariables[$epoch24]['taoResultServer_models_classes_TraceVariable']['TraceVariableIdentifier']['var']);

        $this->assertArrayHasKey('isCorrect', $lastVariables[$epoch24]['taoResultServer_models_classes_TraceVariable']['TraceVariableIdentifier']);
        $this->assertEquals('unscored', $lastVariables[$epoch24]['taoResultServer_models_classes_TraceVariable']['TraceVariableIdentifier']['isCorrect']);

    }

    public function testCalculateResponseStatistics()
    {
        $itemVar1 = array(
            'taoResultServer_models_classes_ResponseVariable' => array('variableIdentifier' => array('isCorrect' => 'correct'))
        );
        $itemVar2 = array(
            'taoResultServer_models_classes_ResponseVariable' => array('variableIdentifier' => array('isCorrect' => 'incorrect'))
        );
        $itemVar3 = array(
            'taoResultServer_models_classes_ResponseVariable' => array('variableIdentifier' => array('isCorrect' => 'incorrect'))
        );
        $itemVar4 = array(
            'taoResultServer_models_classes_ResponseVariable' => array('variableIdentifier' => array('isCorrect' => 'unscored'))
        );
        $itemVar5 = array(
            'taoResultServer_models_classes_ResponseVariable' => array('variableIdentifier' => array('isCorrect' => 'incorrect'))
        );
        $itemVar6 = array(
            'notAValidVariableType' => array('variableIdentifier' => array('isCorrect' => 'correct'))
        );
        $itemVar7 = array(
            'taoResultServer_models_classes_ResponseVariable' => array('variableIdentifier' => array('isCorrect' => 'undefinedValue'))
        );
        $itemVar8 = array(
            'taoResultServer_models_classes_ResponseVariable' => array('variableIdentifier' => array('isCorrect' => 'correct'))
        );
        $variables = array(
            'epoch1' => $itemVar1,
            'epoch2' => $itemVar2,
            'epoch3' => $itemVar3,
            'epoch4' => $itemVar4,
            'epoch5' => $itemVar5,
            'epoch6' => $itemVar6,
            'epoch7' => $itemVar7,
            'epoch8' => $itemVar8
        );
        $responseStats = $this->service->calculateResponseStatistics($variables);


        $this->assertCount(4, $responseStats);
        $this->assertArrayHasKey('nbResponses', $responseStats);
        $this->assertEquals(7, $responseStats['nbResponses']);
        $this->assertArrayHasKey('nbCorrectResponses', $responseStats);
        $this->assertEquals(2, $responseStats['nbCorrectResponses']);
        $this->assertArrayHasKey('nbIncorrectResponses', $responseStats);
        $this->assertEquals(3, $responseStats['nbIncorrectResponses']);
        $this->assertArrayHasKey('nbUnscoredResponses', $responseStats);
        $this->assertEquals(1,$responseStats['nbUnscoredResponses']);
    }
    
}