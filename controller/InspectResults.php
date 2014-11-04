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
use oat\taoOutcomeRds\model\RdsResultStorage;
use \tao_actions_TaoModule;
use \tao_helpers_Display;
use \tao_helpers_Request;
use \tao_helpers_Uri;
use oat\taoOutcomeUi\helper\DeliveryResultGrid;
use oat\taoOutcomeUi\helper\ResultLabel;
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
     * constructor: initialize the service and the default data
     *
     * @author Joel Bout <joel@taotesting.com>
     */
    public function __construct()
    {

        parent::__construct();

        //the service is initialized by default
        $this->service = ResultsService::singleton();
        $this->defaultData();
    }

    private function getImplementation()
    {
        return new RdsResultStorage();
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

        } else {
            if ($propertyUri == PROPERTY_RESULT_OF_SUBJECT) {
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



    /**
     *  index action
     *
     * @author Joel Bout <joel@taotesting.com>
     */
    public function index()
    {
        //Class to filter on
        $rootClass = $this->getRootClass();

        //Properties to filter on
        $properties = array(
            new core_kernel_classes_Property(RDFS_LABEL),
            new core_kernel_classes_Property(PROPERTY_RESULT_OF_DELIVERY),
            new core_kernel_classes_Property(PROPERTY_RESULT_OF_SUBJECT),
            new core_kernel_classes_Property(RDF_TYPE)
        );

        $model = array();
        $filterNodes = array(); 
        foreach($properties as $property){
            if($property->getUri() != RDF_TYPE && $property->getUri() != RDFS_LABEL){
                $filterNodes[] = array(
                    'id'      =>  md5($property->getUri()),
                    'label'   =>  $property->getLabel(),
                    'url'     =>  _url("getFilteredInstancesPropertiesValues"),
                    'options' =>  array(
                        'propertyUri'   => $property->getUri(),
                        'classUri'      => $rootClass->getUri(),
                        'filterItself'  => false
                    )
                );
            }
            $model[] = array(
                'id'       => $property->getUri(),
                'label'    => $property->getLabel(),
                'sortable' => true
            );
        }
            
        $this->setData('filterNodes', $filterNodes);
        $this->setData('model',$model);

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
        $page = $this->getRequestParameter('page');
        $limit = $this->getRequestParameter('rows');
        $order = $this->getRequestParameter('sortby');
        $sord = $this->getRequestParameter('sortorder');
        $start = $limit * $page - $limit;

        $gau = array(
            'order' 	=> $order,
            'orderdir'	=> strtoupper($sord),
            'offset'    => $start,
            'limit'		=> $limit,
            'recursive' => true
        );

        // Get filter parameter
        $filter = array();
        if($this->hasRequestParameter('filter')){
            $filter = $this->getFilterState('filter');
        }
        $columns = array_keys($filter);

        $data = array();
        $results = $this->getImplementation()->getResultByColumn($columns, $filter, $gau);
        $counti = $this->getImplementation()->countResultByFilter($columns, $filter);

        foreach($results as $res){
            
            $deliveryResult = new core_kernel_classes_Resource($res['deliveryResultIdentifier']);
            $delivery = new core_kernel_classes_Resource($res['deliveryIdentifier']);
            $testTaker = new core_kernel_classes_Resource($res['testTakerIdentifier']);
            $label = new ResultLabel($deliveryResult, $testTaker, $delivery);
            $types = $deliveryResult->getTypes();

            $data[] = array(
                 'id'                           => $deliveryResult->getUri(),
                 RDFS_LABEL                     => (string)$label,
                 PROPERTY_RESULT_OF_DELIVERY    => $delivery->getLabel(),
                 PROPERTY_RESULT_OF_SUBJECT     => $testTaker->getLabel(),
                 RDF_TYPE                       => array_shift($types)->getLabel()
             );
        }
        $this->returnJSON(array(
            'data' => $data,
            'page' => floor($start / $limit) + 1,
            'total' => ceil($counti / $limit),
            'records' => count($data)
        ));
    }

    

    /**
     * get the main class
     *
     * @author Joel Bout <joel@taotesting.com>
     * @return core_kernel_classes_Classes
     */
    protected function getRootClass()
    {
        return $this->service->getRootClass();
    }
   
}

?>
