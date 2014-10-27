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
 * Copyright (c) 2009-2012 (original work) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 *               
 * 
 */

namespace oat\taoOutcomeUi\controller;

use \Exception;
use \core_kernel_classes_Class;
use \core_kernel_classes_Property;
use \core_kernel_classes_Resource;
use \tao_actions_TaoModule;
use \tao_helpers_Display;
use \tao_helpers_Request;
use \tao_helpers_Uri;
use oat\taoOutcomeUi\helper\DeliveryResultGrid;
use oat\taoOutcomeUi\model\ResultsService;

/**
 * Results Controller provide actions performed from url resolution
 *
 * @author Joel Bout, Patrick Plichart, <info@taotesting.com>
 * @package taoOutcomeUi
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php

 */
class InspectResults extends tao_actions_TaoModule
{
    /**
     * @var array
     */
    private $resultGridOptions;

    /**
     * constructor: initialize the service and the default data
     *
     * @author Joel Bout <joel@taotesting.com>
     */
    public function __construct()
    {

        parent::__construct();

        //the service is initialized by default
        $this->service = ResultsService::singleton();
        $this->resultGridOptions = array(
            'columns' => array(
                RDFS_LABEL => array('weight' => 2),
                PROPERTY_RESULT_OF_DELIVERY => array('weight' => 2),
                PROPERTY_RESULT_OF_SUBJECT => array('weight' => 2)
            )
        );
        $this->defaultData();
    }

    private function getImplementation(){
        return new \oat\taoOutcomeRds\model\RdsResultStorage();
    }

    /**
     * override the getFilteredInstancesPropertiesValues method from TaoModule
     */
    public function getFilteredInstancesPropertiesValues()
    {

        if(!tao_helpers_Request::isAjax()){
            throw new Exception("wrong request mode");
        }


        // Get the target property
        if($this->hasRequestParameter('propertyUri')){
            $propertyUri = $this->getRequestParameter('propertyUri');
        } else {
            $propertyUri = RDFS_LABEL;
        }
        $property = new core_kernel_classes_Property($propertyUri);


        $propertyValuesFormated = array ();
        if($propertyUri == PROPERTY_RESULT_OF_DELIVERY){
            $deliveriesInArray = array();
            $deliveries = $this->getImplementation()->getAllDeliveryIds();
            foreach($deliveries as $delivery){
                if(!in_array($delivery['deliveryIdentifier'], $deliveriesInArray)){
                    $deliveryResource = new core_kernel_classes_Resource($delivery['deliveryIdentifier']);

                    $propertyValueFormated = array(
                        'data' 	=> $deliveryResource->getLabel(),
                        'type'	=> 'instance',
                        'attributes' => array(
                            'id' => tao_helpers_Uri::encode($delivery['deliveryIdentifier']),
                            'class' => 'node-instance'
                        )
                    );
                    $deliveriesInArray[] = $delivery['deliveryIdentifier'];
                    $propertyValuesFormated[] = $propertyValueFormated;
                }
            }

        }
        else if($propertyUri == PROPERTY_RESULT_OF_SUBJECT){
            $testTakersInArray = array();
            $testTakers = $this->getImplementation()->getAllTestTakerIds();
            foreach($testTakers as $testTaker){
                if(!in_array($testTaker['testTakerIdentifier'], $testTakersInArray)){
                    $deliveryResource = new core_kernel_classes_Resource($testTaker['testTakerIdentifier']);

                    $propertyValueFormated = array(
                        'data' 	=> $deliveryResource->getLabel(),
                        'type'	=> 'instance',
                        'attributes' => array(
                            'id' => tao_helpers_Uri::encode($testTaker['testTakerIdentifier']),
                            'class' => 'node-instance'
                        )
                    );
                    $testTakersInArray[] = $testTaker['testTakerIdentifier'];
                    $propertyValuesFormated[] = $propertyValueFormated;
                }
            }
        }

        $data = array(
            'data' 	=> $this->hasRequestParameter('rootNodeName') ? $this->getRequestParameter('rootNodeName') : tao_helpers_Display::textCutter($property->getLabel(), 16),
            'type'	=> 'class',
            'count' => count($propertyValuesFormated),
            'attributes' => array(
                'id' => tao_helpers_Uri::encode($property->getUri()),
                'class' => 'node-class'
            ),
            'children' => $propertyValuesFormated
        );

        echo json_encode($data);
    }



    /*
     *  index action
     *
     * @author Joel Bout <joel@taotesting.com>
     */

    public function index()
    {
        //Class to filter on
        $rootClass = new core_kernel_classes_Class(TAO_DELIVERY_RESULT);

        //Properties to filter on
        $properties = array();
        $properties[] = new core_kernel_classes_Property(PROPERTY_RESULT_OF_DELIVERY);
        $properties[] = new core_kernel_classes_Property(PROPERTY_RESULT_OF_SUBJECT);
        //$properties[] = new core_kernel_classes_Property(RDF_TYPE);
	
        //Monitoring grid
        $deliveryResultGrid = new DeliveryResultGrid(array(), $this->resultGridOptions);
        $grid = $deliveryResultGrid->getGrid();
        $model = $grid->getColumnsModel();

        //Filtering data
        $this->setData('clazz', $rootClass);
        $this->setData('properties', $properties);

        //Monitoring data
        $this->setData('model', json_encode($model));
        $this->setData('data', $deliveryResultGrid->toArray());

        $this->setView('resultList.tpl');
    }

    /**
     * retrieve Results action
     *
     * @author Joel Bout <joel@taotesting.com>
     *
     */

    public function getResults()
    {
        $returnValue = array();
        // Get filter parameter
        $filter = array();
        if($this->hasRequestParameter('filter')){
            $filter = $this->getFilterState('filter');
        }
        //get the processes uris
        $processesUri = $this->hasRequestParameter('processesUri') ? $this->getRequestParameter('processesUri') : null;

        $rootClass = new core_kernel_classes_Class(TAO_DELIVERY_RESULT);
        if (!is_null($filter)) {
            $results = $rootClass->searchInstances($filter, array('recursive' => true));
        } else if (!is_null($processesUri)) {
            foreach ($processesUri as $processUri) {
                $results[$processUri] = new core_kernel_classes_resource($processUri);
            }
        } else {
            $results = $rootClass->getInstances();
        }

        $data = array();
        $results = $this->getImplementation()->getResultByColumn(array_keys($filter), $filter);


        $testTakers = $this->getImplementation()->getAllTestTakerIds();
        foreach($results as $value){
            $deliveryResult = new core_kernel_classes_Resource($value['deliveryResultIdentifier']);
            $types = $deliveryResult->getTypes();
            $data[$value['deliveryResultIdentifier']] = array(
                RDFS_LABEL  => $deliveryResult->getLabel(),
                PROPERTY_RESULT_OF_DELIVERY => $value['deliveryIdentifier'],
                PROPERTY_RESULT_OF_SUBJECT  => $value['testTakerIdentifier'],
                RDF_TYPE    => array_shift($types)
            );
        }

        $resultsGrid = new DeliveryResultGrid($data, $this->resultGridOptions);
        $data = $resultsGrid->toArray();
        echo json_encode($data);
    }

    

    /**
     * get the main class
     *
     * @author Joel Bout <joel@taotesting.com>
     * @return core_kernel_classes_Classes
     */
    protected function getRootClass()
    {
        return new core_kernel_classes_Class(TAO_DELIVERY_RESULT);
    }
   
}

?>