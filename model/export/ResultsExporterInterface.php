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

use Zend\ServiceManager\ServiceLocatorAwareInterface;

/**
 * ResultsExporterInterface
 *
 * @author Gyula Szucs <gyula@taotesting.com>
 */
interface ResultsExporterInterface extends ServiceLocatorAwareInterface
{
    /**
     * @return \core_kernel_classes_Resource
     */
    public function getResourceToExport();

    /**
     * @param array|string $columnsToExport An array of columns properties or a JSON string
     * @return ResultsExporterInterface
     */
    public function setColumnsToExport($columnsToExport);

    /**
     * @return \tao_models_classes_table_Column[]
     */
    public function getColumnsToExport();

    /**
     * @param string $variableToExport
     * @return ResultsExporterInterface
     */
    public function setVariableToExport($variableToExport);

    /**
     * @return string
     */
    public function getVariableToExport();

    /**
     * @param array $storageOptions
     * @see \oat\taoResultServer\models\classes\ResultManagement::getResultByDelivery()
     * @return ResultsExporterInterface
     */
    public function setStorageOptions(array $storageOptions);

    /**
     * Get data to be exported.
     *
     * @return array
     */
    public function getData();

    /**
     * @param null|string $destination Path of the DIRECTORY where the export file(s) should be saved.
     * @return string Name of the created file
     */
    public function export($destination = null);
}