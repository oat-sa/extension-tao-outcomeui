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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

declare(strict_types=1);

namespace oat\taoOutcomeUi\model\table;

use core_kernel_classes_Resource;

class TestCenterColumn extends \tao_models_classes_table_PropertyColumn
{
    private const TEST_CENTER_LABEL_PROPERTY = 'http://www.w3.org/2000/01/rdf-schema#label';
    private const ELIGIBLE_TEST_CENTER_RDF = 'http://www.tao.lu/Ontologies/TAOProctor.rdf#EligibileTestCenter';
    private const ELIGIBLE_DELIVERY_RDF = 'http://www.tao.lu/Ontologies/TAOProctor.rdf#EligibileDelivery';

    public function getTestCenterLabel($value, $delivery): string
    {
        $propertyValue = "";
        $valueResource = new core_kernel_classes_Resource($value);
        $testCenter = $valueResource->getOnePropertyValue(
            new \core_kernel_classes_Property(self::ELIGIBLE_TEST_CENTER_RDF)
        );
        $eligibleDelivery = $valueResource->getOnePropertyValue(
            new \core_kernel_classes_Property(self::ELIGIBLE_DELIVERY_RDF)
        );
        if ($testCenter !== null && $eligibleDelivery !== null && $delivery->getUri() === $eligibleDelivery->getUri()) {
            $propertyValue = (string)$testCenter->getOnePropertyValue(
                new \core_kernel_classes_Property(self::TEST_CENTER_LABEL_PROPERTY)
            );
        }

        return $propertyValue;
    }
}
