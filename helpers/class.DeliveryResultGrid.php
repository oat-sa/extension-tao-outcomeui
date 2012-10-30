<?php

error_reporting(E_ALL);

/**
 * TAO - taoResults/helpers/class.DeliveryResultGrid.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
 * @package wfEngine
 * @subpackage helpers_Monitoring
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include tao_helpers_grid_GridContainer
 *
 * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
 */
require_once('tao/helpers/grid/class.GridContainer.php');

/* user defined includes */

/* user defined constants */

/**
 * Short description of class taoResults_helpers_DeliveryResultGrid
 *
 * @access public
 * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
 * @package wfEngine
 * @subpackage helpers_Monitoring
 */
class taoResults_helpers_DeliveryResultGrid
    extends tao_helpers_grid_GridContainer
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute processExecutions
     *
     * @access protected
     * @var array
     */
    protected $processExecutions = array();

    // --- OPERATIONS ---

    /**
     * Short description of method initColumns
     *
     * @access protected
     * @author Somsack Sipasseuth, <somsack.sipasseuth@tudor.lu>
     * @return boolean
     */
    protected function initColumns()
    {
        $returnValue = (bool) false;

		$excludedProperties = (is_array($this->options) && isset($this->options['excludedProperties']))?$this->options['excludedProperties']:array();
		$columnNames = (is_array($this->options) && isset($this->options['columnNames']))?$this->options['columnNames']:array();
		
		
		$processProperties = array(
			RDFS_LABEL					=> __('Label'),
			PROPERTY_RESULT_OF_DELIVERY	=> __('Delivery'),
			PROPERTY_RESULT_OF_SUBJECT	=> __('Test taker')
		);
		
		foreach($processProperties as $processPropertyUri => $label){
			if(!isset($excludedProperties[$processPropertyUri])){
				$column = $this->grid->addColumn($processPropertyUri, $label);
			}
		}
		$this->grid->setColumnsAdapter(
			array_keys($processProperties),
			new tao_helpers_grid_Cell_ResourceLabelAdapter()
		);

        return (bool) $returnValue;
    }


} /* end of class taoResults_helpers_DeliveryResultGrid*/

?>