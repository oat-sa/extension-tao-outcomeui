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
/**
 * RegCommon provides the common methods to acces generis API in more suitable
 * in Table Builder context.
 *
 * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
 * @package Result
 */
class UtrStatistic {

    /**
     * Gives more information about statistic of the column
     * number of rows, nomber of not null value in the column
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  Collection $table
     * @param  String $columnId
     * @return Collection
     */

    public function getStatOnColomn($table,$columnId) {
        $rowsOfColumn = $table[$columnId]['rowsColumn'];
        //Number of rows
        $totalRows = count($rowsOfColumn);
        $totalRowsNotNull = 0;
        foreach($rowsOfColumn as $value) {
            if ($value!='') {
                $totalRowsNotNull++;
            }
        }

        $stat['totalRows']= $totalRows;
        $stat['totalRowsNotNull']=$totalRowsNotNull;

        return $stat;
    }

    /**
     * Gives more information about a row, the row id is the uri
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  Collection $table
     * @param  String $rowId
     * @return Collection
     */
    public function getStatOnRows($table, $rowId) {
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
        
        return $stat;

    }

}
?>
