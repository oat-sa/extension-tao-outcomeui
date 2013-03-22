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
 * Copyright (c) 2002-2008 (original work) Public Research Centre Henri Tudor & University of Luxembourg (under the project TAO & TAO2);
 *               2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 * 
 */
?>
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
class taoResults_models_classes_UtrStatistic {
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
    public function getStatOnColomn($table, $columnId) {
        $returnValue = array();

        // section 10-13-1--65--32b0d5d:12d7b0b88e4:-8000:0000000000002AA2 begin
        $rowsOfColumn = $table[$columnId]['rowsColumn'];
        //Number of rows
        $totalRows = count($rowsOfColumn);
        $totalRowsNotNull = 0;
        foreach ($rowsOfColumn as $value) {
            if ($value != '') {
                $totalRowsNotNull++;
            }
        }

        $stat['totalRows'] = $totalRows;
        $stat['totalRowsNotNull'] = $totalRowsNotNull;

        $returnValue =  $stat;

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
    public function getStatOnRows($table, $rowId) {
        $returnValue = array();

        // section 10-13-1--65--32b0d5d:12d7b0b88e4:-8000:0000000000002AAF begin
            //Get the list of columns in the row, to claculate the number of columns
        //and the number of columns not null
        //I prefer used the rowHTML to perse rapidly the
        $columnsOfRow= $table[$rowId];
        $totalColumns = 0;
        $totalColumnsNotNull=0;
        foreach($columnsOfRow as $col) {
            $totalColumns++;
            if ($col!='') {
                $totalColumnsNotNull++;
            }
        }
        $stat['totalColumns'] = $totalColumns;
        $stat['totalColumnsNotNull']=$totalColumnsNotNull;
        
        $returnValue = $stat;
        // section 10-13-1--65--32b0d5d:12d7b0b88e4:-8000:0000000000002AAF end

        return (array) $returnValue;
    }

}

/* end of class taoResults_models_classes_UtrStatistic */
?>