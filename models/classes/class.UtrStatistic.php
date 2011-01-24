<?php

error_reporting(E_ALL);

/**
 * TAO - taoResults\models\classes\class.UtrStatistic.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 24.01.2011, 11:45:53 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
 * @package taoResults
 * @subpackage models_classes
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include taoResults_models_classes_TReg_VirtualTable
 *
 * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
 */
require_once('taoResults/models/classes/class.TReg_VirtualTable.php');

/* user defined includes */
// section 10-13-1--65--32b0d5d:12d7b0b88e4:-8000:0000000000002A96-includes begin
// section 10-13-1--65--32b0d5d:12d7b0b88e4:-8000:0000000000002A96-includes end

/* user defined constants */
// section 10-13-1--65--32b0d5d:12d7b0b88e4:-8000:0000000000002A96-constants begin
// section 10-13-1--65--32b0d5d:12d7b0b88e4:-8000:0000000000002A96-constants end

/**
 * Short description of class taoResults_models_classes_UtrStatistic
 *
 * @access public
 * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
 * @package taoResults
 * @subpackage models_classes
 */
class taoResults_models_classes_UtrStatistic
{
    // --- ASSOCIATIONS ---
    // generateAssociationEnd : 

    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Gives more information about statistic of the column
     * number of rows, nomber of not null value in the column
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  array table
     * @param  string columnId
     * @return array
     */
    public function getStatOnColomn($table, $columnId)
    {
        $returnValue = array();

        // section 10-13-1--65--32b0d5d:12d7b0b88e4:-8000:0000000000002AA2 begin
        // section 10-13-1--65--32b0d5d:12d7b0b88e4:-8000:0000000000002AA2 end

        return (array) $returnValue;
    }

    /**
     * Short description of method getStatOnRows
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  array table
     * @param  string rowId
     * @return array
     */
    public function getStatOnRows($table, $rowId)
    {
        $returnValue = array();

        // section 10-13-1--65--32b0d5d:12d7b0b88e4:-8000:0000000000002AAF begin
        // section 10-13-1--65--32b0d5d:12d7b0b88e4:-8000:0000000000002AAF end

        return (array) $returnValue;
    }

} /* end of class taoResults_models_classes_UtrStatistic */

?>