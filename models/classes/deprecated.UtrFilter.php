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
 *               2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);\n *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 * 
 */
?>
<?php

error_reporting(E_ALL);

/**
 * TAO - taoResults\models\classes\class.UtrFilter.php
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
// section 10-13-1--65--32b0d5d:12d7b0b88e4:-8000:0000000000002A7F-includes begin
// section 10-13-1--65--32b0d5d:12d7b0b88e4:-8000:0000000000002A7F-includes end

/* user defined constants */
// section 10-13-1--65--32b0d5d:12d7b0b88e4:-8000:0000000000002A7F-constants begin
// section 10-13-1--65--32b0d5d:12d7b0b88e4:-8000:0000000000002A7F-constants end

/**
 * Short description of class taoResults_models_classes_UtrFilter
 *
 * @access public
 * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
 * @package taoResults
 * @subpackage models_classes
 */
class taoResults_models_classes_UtrFilter {
    // --- ASSOCIATIONS ---
    // generateAssociationEnd : 
    // --- ATTRIBUTES ---
    // --- OPERATIONS ---

    /**
     * Short description of method filterAndGenerateUtr
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  array lisOfFilterDescription
     * @param  array utrModel
     * @param  array listInstances
     * @return array
     */
    public function filterAndGenerateUtr($lisOfFilterDescription, $utrModel, $listInstances) {
        $returnValue = array();

        // section 10-13-1--65--32b0d5d:12d7b0b88e4:-8000:0000000000002A80 begin
        //generate the first time to have all the values
        $uVT = new TReg_VirtualTable();

        $utr = $uVT->generateUTR($utrModel, $listInstances);

        //get the utrModel
        $utrModelGenerated = $utr['utrModel'];

        // Apply the filter one after one, the order is important
        foreach ($lisOfFilterDescription as $filterDescription) {
            //filter according

            $rows = $this->filterColumn($filterDescription, $utrModelGenerated);

            //regenerate the table based on the new list of instances
            $newRows = $rows['match'];

            // print_r($newRows);
            $utrGenerated = $uVT->generateUTR($utrModel, $newRows);
            $returnValue = $utrGenerated['utrModel'];
        }
        // section 10-13-1--65--32b0d5d:12d7b0b88e4:-8000:0000000000002A80 end

        return (array) $returnValue;
    }

    /**
     * Short description of method filterColumn
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  array filterDescription
     * @param  array table
     * @return array
     */
    public function filterColumn($filterDescription, $table) {
        $returnValue = array();

        // section 10-13-1--65--32b0d5d:12d7b0b88e4:-8000:0000000000002A85 begin
                //get filter information
        //print_r($filterDescription);

        $result = array();

        $result['notMatch'] = array();
        $result['match'] = array();

        $filtredRows= array();

        //prepare filter options
        $columnID = $filterDescription['columnID'];
        $operator = $filterDescription['operator'];
        $valueCriteria = $filterDescription['value'];



        //get the column rows
        if (isset ($table[$columnID]['rowsColumn'])) {
            //initialize the array
            //$result['match'];
            $columnTable = $table[$columnID]['rowsColumn'];

            $result = array();
            //do a filter
            foreach($columnTable as $instance=>$valueRow) {
                //according to operator we do a filter
                $match= FALSE;
                switch ($operator) {
                    case '=':
                    //do something
                        if ($valueCriteria == $valueRow ) {
                            $match= TRUE;
                        }
                        break;
                    case '<':
                    //do
                        if ( $valueRow < $valueCriteria) {
                            $match= TRUE;
                        }
                        break;

                    case '>':
                    //do
                        if ( $valueRow > $valueCriteria ) {
                            $match= TRUE;
                        }
                        break;

                        case '>=':
                    //do
                        if ( $valueRow >= $valueCriteria ) {
                            $match= TRUE;
                        }
                        break;

                        case '<=':
                    //do
                        if ( $valueRow <= $valueCriteria ) {
                            $match= TRUE;
                        }
                        break;

                    case 'like':
                    //do

                    //$match = preg_match("#".$valueCriteria+"#",$valueRow);
                        $pos = strpos($valueRow,$valueCriteria);
                        if ( $pos !== false) {
                            $match = TRUE;

                        }

                        break;
                }//switch
                //if on match then we add this row in the result array

                if ($match) {
                    $result['match'][$instance]=1;

                }else {
                    $result['notMatch'][$instance]=2;
                }
            }//foreach

        }
        //we send an empty array if there is no match
        if (!isset($result['match'])) {
            $result['match']= array();
        }

        $returnValue = $result;

        // section 10-13-1--65--32b0d5d:12d7b0b88e4:-8000:0000000000002A85 end

        return (array) $returnValue;
    }

}

/* end of class taoResults_models_classes_UtrFilter */
?>