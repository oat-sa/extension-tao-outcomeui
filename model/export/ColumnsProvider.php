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

use oat\generis\model\OntologyAwareTrait;
use oat\taoDelivery\model\fields\DeliveryFieldsService;
use oat\taoOutcomeUi\model\ResultsService;
use oat\taoOutcomeUi\model\table\ContextTypePropertyColumn;

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

    /**
     * Test Taker properties to be exported.
     *
     * @var array
     */
    private $testTakerProperties = [
        RDFS_LABEL,
        PROPERTY_USER_LOGIN,
        PROPERTY_USER_FIRSTNAME,
        PROPERTY_USER_LASTNAME,
        PROPERTY_USER_MAIL,
        PROPERTY_USER_UILG
    ];

    /**
     * Delivery properties to be exported.
     *
     * @var array
     */
    private $deliveryProperties = [
        RDFS_LABEL,
        DeliveryFieldsService::PROPERTY_CUSTOM_LABEL,
        TAO_DELIVERY_MAXEXEC_PROP,
        TAO_DELIVERY_START_PROP,
        TAO_DELIVERY_END_PROP,
        DELIVERY_DISPLAY_ORDER_PROP,
        TAO_DELIVERY_ACCESS_SETTINGS_PROP
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
            throw new \common_exception_NotFound('Results Exporter: delivery "'. $this->delivery->getUri() .'" does not exist.');
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

        // add custom properties, it contains the GROUP property as well
        $customProps = $this->getClass(TAO_CLASS_SUBJECT)->getProperties();

        $testTakerProps = array_merge($this->testTakerProperties, $customProps);

        foreach ($testTakerProps as $property){
            $property = $this->getProperty($property);
            $col = new ContextTypePropertyColumn(ContextTypePropertyColumn::CONTEXT_TYPE_TEST_TAKER, $property);

            if ($property->getUri() == RDFS_LABEL) {
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
     */
    public function getDeliveryColumns()
    {
        $columns = [];

        // add custom properties, it contains the group property as well
        $customProps = $this->getClass($this->delivery->getOnePropertyValue($this->getProperty(RDF_TYPE)))->getProperties();

        $deliveryProps = array_merge($this->deliveryProperties, $customProps);

        foreach ($deliveryProps as $property){
            $property = $this->getProperty($property);
            $loginCol = new ContextTypePropertyColumn(ContextTypePropertyColumn::CONTEXT_TYPE_DELIVERY, $property);

            if ($property->getUri() == RDFS_LABEL) {
                $loginCol->label = __('Delivery');
            }

            $columns[] = $loginCol->toArray();
        }

        return $columns;
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
     */
    public function getAll()
    {
        return array_merge(
            $this->getTestTakerColumns(),
            $this->getDeliveryColumns(),
            $this->getGradeColumns(),
            $this->getResponseColumns()
        );
    }
}