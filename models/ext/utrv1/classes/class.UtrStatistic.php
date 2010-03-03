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
