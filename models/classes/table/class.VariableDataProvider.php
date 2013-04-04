<?php
/*  
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
?>
<?php

error_reporting(E_ALL);

/**
 * tao - taoResults/models/classes/table/class.VariableDataProvider.php
 *
 * $Id$
 *
 * This file is part of tao.
 *
 * Automatically generated on 31.08.2012, 10:14:43 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package taoResults
 * @subpackage models_classes_table
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include tao_models_classes_table_DataProvider
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
 */
require_once('tao/models/classes/table/interface.DataProvider.php');

/* user defined includes */
// section 127-0-1-1--8febfab:13977a059a7:-8000:0000000000004006-includes begin
// section 127-0-1-1--8febfab:13977a059a7:-8000:0000000000004006-includes end

/* user defined constants */
// section 127-0-1-1--8febfab:13977a059a7:-8000:0000000000004006-constants begin
// section 127-0-1-1--8febfab:13977a059a7:-8000:0000000000004006-constants end

/**
 * Short description of class
 *
 * @access public
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package taoResults
 * @subpackage models_classes_table
 */
class taoResults_models_classes_table_VariableDataProvider
        implements tao_models_classes_table_DataProvider
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

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
     * @param  array resources
     * @param  array columns
     * @return mixed
     */
    public function prepare($resources, $columns)
    {
        // section 127-0-1-1--920ca93:1397ba721e9:-8000:0000000000000C5B begin
        $vClasses = array();
        foreach ($columns as $column) {
        	$vClasses[] = $column->getVariableClass();
        }
        $vClasses = array_unique($vClasses); 
        // nothing to do?
        if (count($vClasses) == 0) {
        	return;
        }
        if (count($vClasses) == 1) {
        	$varClass = array_pop($vClasses);
        } else {
        	$varClass = new core_kernel_classes_Class(TAO_RESULT_VARIABLE);
        }
        
		foreach($resources as $result){
			
			$vars = $varClass->searchInstances(array(
				PROPERTY_MEMBER_OF_RESULT => $result->getUri()
			), array ('recursive'=>true));
			
			$cellData = array();
			foreach ($vars as $var) {
				$props = $var->getPropertiesValues(array(
					RDF_TYPE,
					PROPERTY_VARIABLE_ORIGIN,
					PROPERTY_VARIABLE_IDENTIFIER,
					PROPERTY_VARIABLE_EPOCH,
					RDF_VALUE
				));
				
				$classActivityExecution = array_pop($props[PROPERTY_VARIABLE_ORIGIN]);
				$classActivity = $classActivityExecution->getUniquePropertyValue(new core_kernel_classes_Property(PROPERTY_ACTIVITY_EXECUTION_ACTIVITY));
				$vid = (string)array_pop($props[PROPERTY_VARIABLE_IDENTIFIER]);
				foreach ($columns as $column) {
					if ($classActivity->getUri() == $column->getClassActivity()->getUri()
						&& $vid == $column->getIdentifier()) {
							$value = (string)array_pop($props[RDF_VALUE]);
							foreach ($props[RDF_TYPE] as $type) {
							    $time = "";
							    if (is_array($props[PROPERTY_VARIABLE_EPOCH])) {$epoch = (string)array_pop($props[PROPERTY_VARIABLE_EPOCH]);}
							    if ($epoch != "") {$time = "@". date("F j, Y, g:i:s a",$epoch);}
							    $this->cache[$type->getUri()][$result->getUri()][$classActivity->getUri()][$vid][] =  array($value, $time);
							}
							continue;
					}
				}
			}
		}

        // section 127-0-1-1--920ca93:1397ba721e9:-8000:0000000000000C5B end
    }

    /**
     * Short description of method getValue
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  Resource resource
     * @param  Column column
     * @return string
     */
    public function getValue( core_kernel_classes_Resource $resource,  tao_models_classes_table_Column $column)
    {
        $returnValue = (string) '';

        // section 127-0-1-1--920ca93:1397ba721e9:-8000:0000000000000C5D begin
        $vcUri = $column->getVariableClass()->getUri();
        if (isset($this->cache[$vcUri][$resource->getUri()][$column->getClassActivity()->getUri()][$column->getIdentifier()])) {
        	$returnValue = $this->cache[$vcUri][$resource->getUri()][$column->getClassActivity()->getUri()][$column->getIdentifier()];
        } else {
        	common_Logger::i('no data for resource: '.$resource->getUri().' column: '.$column->getClassActivity()->getUri().'-'.$column->getIdentifier());
        }
        // section 127-0-1-1--920ca93:1397ba721e9:-8000:0000000000000C5D end

        return (array) $returnValue;
    }
    
    /**
     * Short description of method singleton
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return taoResults_models_classes_table_VariableDataProvider
     */
    public static function singleton()
    {
        $returnValue = null;

        // section 127-0-1-1--920ca93:1397ba721e9:-8000:0000000000000C69 begin
        if (is_null(self::$singleton)) {
        	self::$singleton = new self();
        }
        return self::$singleton;
        // section 127-0-1-1--920ca93:1397ba721e9:-8000:0000000000000C69 end

        return $returnValue;
    }

    /**
     * Short description of method __construct
     *
     * @access private
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return mixed
     */
    private function __construct()
    {
        // section 127-0-1-1--920ca93:1397ba721e9:-8000:0000000000000C6C begin
        // section 127-0-1-1--920ca93:1397ba721e9:-8000:0000000000000C6C end
    }

} /* end of class taoResults_models_classes_table_VariableDataProvider */

?>