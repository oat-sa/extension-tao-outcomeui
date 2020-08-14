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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoOutcomeUi\model\export;

use oat\generis\model\GenerisRdf;
use oat\generis\model\OntologyAwareTrait;
use oat\generis\model\OntologyRdf;
use oat\generis\model\OntologyRdfs;
use oat\taoDelivery\model\fields\DeliveryFieldsService;
use oat\taoDeliveryRdf\model\DeliveryAssemblyService;
use oat\taoDeliveryRdf\model\DeliveryContainerService;
use oat\taoOutcomeUi\model\ResultsService;
use oat\taoOutcomeUi\model\table\ContextTypePropertyColumn;
use oat\taoOutcomeUi\model\table\DeliveryExecutionColumn;
use oat\taoOutcomeUi\model\table\DeliveryExecutionDataProvider;
use oat\taoTestTaker\models\TestTakerService;

/**
 * ColumnsProvider
 *
 * @author Gyula Szucs <gyula@taotesting.com>
 */
class ColumnsProvider
{
    use OntologyAwareTrait;

    private $delivery;
    private $resultsService;

    const LABEL_START_DELIVERY_EXECUTION = 'Start Delivery Execution';
    const LABEL_ID_DELIVERY_EXECUTION = 'Delivery Execution Id';

    /**
     * Test Taker properties to be exported.
     *
     * @var array
     */
    private $testTakerProperties = [
        OntologyRdfs::RDFS_LABEL,
        GenerisRdf::PROPERTY_USER_LOGIN,
        GenerisRdf::PROPERTY_USER_FIRSTNAME,
        GenerisRdf::PROPERTY_USER_LASTNAME,
        GenerisRdf::PROPERTY_USER_MAIL,
        GenerisRdf::PROPERTY_USER_UILG
    ];

    /**
     * Delivery properties to be exported.
     *
     * @var array
     */
    private $deliveryProperties = [
        OntologyRdfs::RDFS_LABEL,
        DeliveryFieldsService::PROPERTY_CUSTOM_LABEL,
        DeliveryContainerService::PROPERTY_MAX_EXEC,
        DeliveryAssemblyService::PROPERTY_START,
        DeliveryAssemblyService::PROPERTY_END,
        DeliveryAssemblyService::PROPERTY_DELIVERY_DISPLAY_ORDER_PROP,
        DeliveryContainerService::PROPERTY_ACCESS_SETTINGS
    ];

    /**
     * @param string|\core_kernel_classes_Resource $delivery
     * @param ResultsService                       $resultsService
     * @throws \common_exception_NotFound
     */
    public function __construct($delivery, ResultsService $resultsService)
    {
        $this->delivery = $this->getResource($delivery);

        if (!$this->delivery->exists()) {
            throw new \common_exception_NotFound('Results Exporter: delivery "' . $this->delivery->getUri() . '" does not exist.');
        }

        $this->resultsService = $resultsService;
    }

    /**
     * Get test taker columns to be exported.
     *
     * @return array
     */
    public function getTestTakerColumns()
    {
        $columns = [];
        // add tt ID
        $columns[] = [
            'type' => DeliveryExecutionColumn::class,
            'label' => __('Test Taker ID'),
            // for the BE to select test taker from the DeliveryExecution
            'contextId' => ContextTypePropertyColumn::CONTEXT_TYPE_TEST_TAKER,
            'variableIdentifier' => DeliveryExecutionDataProvider::PROP_USER_ID,
            // for the FE to show DE property within Test Takers data
            'prop' => 'delivery_execution',
            'contextType' => DeliveryExecutionDataProvider::PROP_USER_ID,
        ];

        // add custom properties, it contains the GROUP property as well
        $customProps = $this->getClass(TestTakerService::CLASS_URI_SUBJECT)->getProperties();

        $testTakerProps = array_merge($this->testTakerProperties, $customProps);

        foreach ($testTakerProps as $property) {
            $property = $this->getProperty($property);
            $col = new ContextTypePropertyColumn(ContextTypePropertyColumn::CONTEXT_TYPE_TEST_TAKER, $property);

            if ($property->getUri() === OntologyRdfs::RDFS_LABEL) {
                $col->label = __('Test Taker');
            }

            $columns[] = $col->toArray();
        }

        return $columns;
    }

    /**
     * Get delivery columns to be exported.
     *
     * @throws \RuntimeException
     * @return array
     * @throws \core_kernel_persistence_Exception
     */
    public function getDeliveryColumns()
    {
        $columns = [];

        // add custom properties, it contains the group property as well
        $customProps = $this->getClass($this->delivery->getOnePropertyValue($this->getProperty(OntologyRdf::RDF_TYPE)))->getProperties();

        $deliveryProps = array_merge($this->deliveryProperties, $customProps);

        foreach ($deliveryProps as $property) {
            $property = $this->getProperty($property);
            $loginCol = new ContextTypePropertyColumn(ContextTypePropertyColumn::CONTEXT_TYPE_DELIVERY, $property);

            if ($property->getUri() == OntologyRdfs::RDFS_LABEL) {
                $loginCol->label = __('Delivery');
            }

            $columns[] = $loginCol->toArray();
        }

        return $columns;
    }

    /**
     * Get delivery execution columns to be exported
     */
    public function getDeliveryExecutionColumns()
    {
        return [
            [
                'type' => DeliveryExecutionColumn::class,
                'label' => self::LABEL_ID_DELIVERY_EXECUTION,
                'contextId' => 'delivery_execution',
                'variableIdentifier' => DeliveryExecutionDataProvider::PROP_DELIVERY_EXECUTION_ID
            ],
            [
                'type' => DeliveryExecutionColumn::class,
                'label' => self::LABEL_START_DELIVERY_EXECUTION,
                'contextId' => 'delivery_execution',
                'variableIdentifier' => DeliveryExecutionDataProvider::PROP_STARTED_AT
            ],
            [
                'type' => DeliveryExecutionColumn::class,
                'label' => 'End Delivery Execution',
                'contextId' => 'delivery_execution',
                'variableIdentifier' => DeliveryExecutionDataProvider::PROP_FINISHED_AT,
            ],
        ];
    }

    /**
     * Returns all grade columns to be exported.
     *
     * @throws \RuntimeException
     * @return array
     */
    public function getGradeColumns()
    {
        return $this->resultsService->getVariableColumns($this->delivery, \taoResultServer_models_classes_OutcomeVariable::class);
    }

    /**
     * Returns all response columns to be exported.
     *
     * @throws \RuntimeException
     * @return array
     */
    public function getResponseColumns()
    {
        return $this->resultsService->getVariableColumns($this->delivery, \taoResultServer_models_classes_ResponseVariable::class);
    }

    /**
     * @return array
     * @throws \core_kernel_persistence_Exception
     */
    public function getAll()
    {
        return array_merge(
            $this->getTestTakerColumns(),
            $this->getDeliveryColumns(),
            $this->getGradeColumns(),
            $this->getResponseColumns(),
            $this->getDeliveryExecutionColumns()
        );
    }
}
