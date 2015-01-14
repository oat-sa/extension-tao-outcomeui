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
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoQtiTest\test;

use oat\tao\test\TaoPhpUnitTestRunner;
use \common_ext_ExtensionsManager;
use common_cache_FileCache;
use oat\taoOutcomeUi\model\ResultsService;
use Prophecy\Prophet;

/**
 * This test case focuses on testing ResultsService.
 *
 * @author Lionel Lecaque <lionel@taotesting.com>
 *
 */
class ResultsServiceTest extends TaoPhpUnitTestRunner
{
    
    protected $service;
    
    public function setUp()
    {
        TaoPhpUnitTestRunner::initTest();
        common_ext_ExtensionsManager::singleton()->getExtensionById('taoOutcomeUi');
        $this->service = ResultsService::singleton();
    }
    
    
    public function testGetRootClass()
    {
        $this->assertInstanceOf('core_kernel_classes_Class', $this->service->getRootClass());
        $this->assertEquals('http://www.tao.lu/Ontologies/TAOResult.rdf#DeliveryResult', $this->service->getRootClass()->getUri());
    }
    
    public function testGetImplementation()
    {
        $this->assertInstanceOf('oat\taoOutcomeRds\model\RdsResultStorage', $this->service->getImplementation());
    }
    
    public function testGetItemResultsFromDeliveryResult()
    {
        $prophet = new Prophet;

        $deliveryResultProphecy = $prophet->prophesize('core_kernel_classes_Resource');
        $deliveryResultProphecy->getUri()->willReturn('#fakeUri');
        
        $deliveryResult = $deliveryResultProphecy->reveal();
        $this->assertEmpty($this->service->getItemResultsFromDeliveryResult($deliveryResult));
        

    }
    
    public function testGetVariables()
    {
        $prophet = new Prophet;

        $deliveryResultProphecy = $prophet->prophesize('core_kernel_classes_Resource');
        $deliveryResultProphecy->getUri()->willReturn('#fakeUri');
        $deliveryResultProphecy->getUniquePropertyValue(
            new \core_kernel_classes_Property(PROPERTY_DELVIERYEXECUTION_STATUS)
            )->willReturn(new \core_kernel_classes_Resource(INSTANCE_DELIVERYEXEC_FINISHED));
        $deliveryResult = $deliveryResultProphecy->reveal();
        
        $this->assertFalse(common_cache_FileCache::singleton()->has('deliveryResultVariables:#fakeUri'));

        $this->assertEmpty($this->service->getVariables($deliveryResult));
        
        //remove cache
        $this->assertTrue(common_cache_FileCache::singleton()->has('deliveryResultVariables:#fakeUri'));
        
        $this->assertEmpty($this->service->getVariables($deliveryResult));
        
        $this->assertTrue(common_cache_FileCache::singleton()->has('deliveryResultVariables:#fakeUri'));
            

        
        common_cache_FileCache::singleton()->remove('deliveryResultVariables:#fakeUri');
    
    }
    
    public function testGetDelivery()
    {
        $prophet = new Prophet;
        
        $deliveryResultProphecy = $prophet->prophesize('core_kernel_classes_Resource');
        $deliveryResultProphecy->getUri()->willReturn('#fakeUri');
        $deliveryResult = $deliveryResultProphecy->reveal();
        
        $impProphecy = $prophet->prophesize('oat\taoOutcomeRds\model\RdsResultStorage');
        
        $impProphecy->getDelivery()->willReturn('#fakeDelivery');
        $imp = $impProphecy->reveal();
        
        //$this->service->setImplementation($imp);
        
        var_dump($this->service->getImplementation(),$this->service->getImplementation()->getDelivery($deliveryResult->getUri()));
        
    }
    
}