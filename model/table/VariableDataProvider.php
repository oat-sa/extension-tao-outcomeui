<?php
/*
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * Copyright (c) 2009-2012 (original work) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 */
namespace oat\taoOutcomeUi\model\table;

use \common_Logger;
use \common_cache_FileCache;
use \core_kernel_classes_Class;
use \core_kernel_classes_Resource;
use oat\taoOutcomeUi\helper\Datatypes;
use \tao_helpers_Date;
use \tao_helpers_Uri;
use \tao_models_classes_table_Column;
use \tao_models_classes_table_DataProvider;
use oat\taoOutcomeUi\model\ResultsService;
use oat\taoDelivery\model\execution\DeliveryExecution;

/**
 * Short description of class
 *
 * @access public
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package taoOutcomeUi
 */
class VariableDataProvider implements tao_models_classes_table_DataProvider
{

    /**
     * Short description of attribute cache
     *
     * @access public
     * @var array
     */
    public $cache = array();

    /**
     * Short description of attribute singleton
     *
     * @access public
     * @var VariableDataProvider
     */
    public static $singleton = null;
    
    // --- OPERATIONS ---
    
    /**
     * Short description of method prepare
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param array resources results
     * @param array columns variables
     * @return mixed
     */
    public function prepare($resources, $columns)
    {
        $resultsService = ResultsService::singleton();
        
        /**
         * @var DeliveryExecution $result
         */
        foreach ($resources as $result) {
            $itemresults = $resultsService->getVariables($result, false);
            
            foreach ($itemresults as $itemResultUri => $vars) {
                // cache the item information pertaining to a given itemResult (stable over time)
                if (common_cache_FileCache::singleton()->has('itemResultItemCache' . $itemResultUri)) {
                    $itemUri = common_cache_FileCache::singleton()->get('itemResultItemCache' . $itemResultUri);
                    $object = new core_kernel_classes_Resource($itemUri);
                } else {
                    $object = $resultsService->getItemFromItemResult($itemResultUri);
                    if (is_null($object)) {
                        $object = $resultsService->getVariableFromTest($itemResultUri);
                    }
                    if (! is_null($object)) {
                        common_cache_FileCache::singleton()->put($object->getUri(), 'itemResultItemCache' . $itemResultUri);
                    }
                }
                if (get_class($object) == "core_kernel_classes_Resource") {
                    $contextIdentifier = (string) $object->getUri();
                } else 
                    if (! is_null($object)) {
                        $contextIdentifier = (string) $object->__toString();
                    }
                foreach ($vars as $var) {
                    $var = $var[0];
                    // cache the variable data
                    /**
                     * @var \taoResultServer_models_classes_Variable $varData
                     */
                    $varData = $var->variable;
                    if (common_cache_FileCache::singleton()->has('variableDataCache' . $var->uri . '_' . $varData->getIdentifier())) {
                        $varData = common_cache_FileCache::singleton()->get('variableDataCache' . $var->uri . '_' . $varData->getIdentifier());
                    } else {
                        common_cache_FileCache::singleton()->put($varData, 'variableDataCache' . $var->uri . '_' . $varData->getIdentifier());
                    }
                    
                    if ($varData->getBaseType() === 'file') {
                        $decodedFile = Datatypes::decodeFile($varData->getValue());
                        $varData->setValue($decodedFile['name']);
                    }
                    $variableIdentifier = (string) $varData->getIdentifier();
                    foreach ($columns as $column) {
                        if ($variableIdentifier == $column->getIdentifier() and $contextIdentifier == $column->getContextIdentifier()) {
                            
                            $epoch = $varData->getEpoch();
                            $readableTime = "";
                            if ($epoch != "") {
                                $readableTime = "@" . tao_helpers_Date::displayeDate(tao_helpers_Date::getTimeStamp($epoch), tao_helpers_Date::FORMAT_VERBOSE);
                            }
                            $this->cache[get_class($varData)][$result][$column->getContextIdentifier() . $variableIdentifier][(string) $epoch] = array(
                                $varData->getValue(),
                                $readableTime);
                        }
                    }
                }
            }
        }
    }

    /**
     * (non-PHPdoc)
     * @see tao_models_classes_table_DataProvider::getValue()
     */
    public function getValue(core_kernel_classes_Resource $resource, tao_models_classes_table_Column $column)
    {
        $returnValue = array();
        
        if (! $column instanceof VariableColumn) {
            throw new \common_exception_InconsistentData('Unexpected colum type ' . get_class($column) . ' for ' . __CLASS__);
        }
        
        $vcUri = $column->getVariableType();
        
        if (isset($this->cache[$vcUri][$resource->getUri()][$column->getContextIdentifier() . $column->getIdentifier()])) {
            $returnValue = $this->cache[$vcUri][$resource->getUri()][$column->getContextIdentifier() . $column->getIdentifier()];
        } else {
            common_Logger::d('no data for resource: ' . $resource->getUri() . ' column: ' . $column->getIdentifier());
        }
        
        return $returnValue;
    }

}
