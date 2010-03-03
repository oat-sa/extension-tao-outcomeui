<?php

/**
 * UtrFilter provides the common methods to Filter and search on UTR based on a filter description
 * 
 * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
 */

require_once('class.TReg_VirtualTable.php');

class UtrFilter {

    /**
     * Filter the given table according to the filter array and regenerate a new filtered UTR table
     * This method uses filterColumn () method.
     * 
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  Collection $lisOfFilterDescription
     * @param  Collection $utrModel
     * @param  Collection $listInstances
     * @return Collection
     */

    public function filterAndGenerateUtr ($lisOfFilterDescription,$utrModel,$listInstances) {
        //generate the first time to have all the values
        $uVT = new TReg_VirtualTable();

        $utr = $uVT->generateUTR($utrModel, $listInstances);

        //get the utrModel
        $utrModelGenerated = $utr['utrModel'];

        // Apply the filter one after one, the order is important
        foreach($lisOfFilterDescription as $filterDescription) {
            //filter according

            $rows = $this->filterColumn($filterDescription, $utrModelGenerated);

            //regenerate the table based on the new list of instances
            $newRows = $rows['match'];

            // print_r($newRows);
            $utrGenerated = $uVT->generateUTR($utrModel, $newRows);
            $utrModelGenerated = $utrGenerated['utrModel'];

        }
        return $utrGenerated;
    }
    /**
     * Filter the given UTR table according to ONLY one filter on one column,
     * the result is a new filtred UTR.
     * This method is used by filterAndGenerateUtr() method
     * 
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  Collection $filterDescription
     * @param  Collection $table
     * @return Collection
     */

    public function filterColumn($filterDescription,$table) {
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

        return $result;

    }



}
?>
