<?php

error_reporting(E_ALL);

/**
 * TAO - taoResults\models\classes\class.TReg_VirtualTable.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 01.06.2011, 17:15:04 with ArgoUML PHP module 
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
 * include taoResults_models_classes_RegCommon
 *
 * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
 */
require_once('taoResults/models/classes/class.RegCommon.php');

/**
 * include taoResults_models_classes_UtrFilter
 *
 * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
 */
require_once('taoResults/models/classes/class.UtrFilter.php');

/**
 * include taoResults_models_classes_UtrStatistic
 *
 * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
 */
require_once('taoResults/models/classes/class.UtrStatistic.php');

/* user defined includes */
// section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002AA5-includes begin
// section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002AA5-includes end

/* user defined constants */
// section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002AA5-constants begin
// section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002AA5-constants end

/**
 * Short description of class taoResults_models_classes_TReg_VirtualTable
 *
 * @access public
 * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
 * @package taoResults
 * @subpackage models_classes
 */
class taoResults_models_classes_TReg_VirtualTable
    /* multiple generalisations not supported by PHP: */
    /* extends taoResults_models_classes_RegCommon,
            taoResults_models_classes_RegCommon,
            taoResults_models_classes_RegCommon */
{
    // --- ASSOCIATIONS ---
    // generateAssociationEnd :     // generateAssociationEnd : 

    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * Short description of method __construct
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @return mixed
     */
    public function __construct()
    {
        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002AA6 begin
        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002AA6 end
    }

    /**
     * Short description of method trDeleteRow
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  string uriInstance
     * @param  array listInstances
     * @return array
     */
    public function trDeleteRow($uriInstance, $listInstances)
    {
        $returnValue = array();

        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002AA8 begin
        $newListOfRows = $listInstances;
        unset($newListOfRows[$uriInstance]);

        //return the the new list of rows
        $returnValue = $newListOfRows;

        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002AA8 end

        return (array) $returnValue;
    }

    /**
     * Short description of method trDeleteListRows
     *
     * @access private
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  array listRows
     * @param  array listInstances
     * @return array
     */
    private function trDeleteListRows($listRows, $listInstances)
    {
        $returnValue = array();

        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002AAC begin
        // TODO: change the name of the function
         $newListOfRows = $listInstances;

        foreach ($listRows as $uriInstance) {
            $newListOfRows = $this->trDeleteRow($uriInstance, $newListOfRows);
        }

        return $newListOfRows; //the new list of rows 
        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002AAC end

        return (array) $returnValue;
    }

    /**
     * Short description of method deleteColumn
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  string columnId
     * @param  array utrModel
     * @return array
     */
    public function deleteColumn($columnId, $utrModel)
    {
        $returnValue = array();

        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002AB0 begin
        $newUtrModel = $utrModel; // $_SESSION["utrModel"];
        unset($newUtrModel[$columnId]);
        //return new table
        $returnValue = $newUtrModel;
        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002AB0 end

        return (array) $returnValue;
    }

    /**
     * Short description of method generateUTR
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  array utrModel
     * @param  array listInstances
     * @return array
     */
    public function generateUTR($utrModel, $listInstances)
    {
        $returnValue = array();

        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002AB4 begin

        $table = $utrModel; //$_SESSION['utrModel'];
        //get the list of instances
        //$listInstances = $this->trGetInstances();
        //for each column we get the value according to instance and path
        $rowsColumn = array();
        $rowsHTML = array();
        $rowsTableHtml = array();

        $p = new RegCommon(); // in orderr to get the bridged values
        //for each column in the model
        foreach ($table as $columnId => &$columnDescription) {//the column description will be changed by adding the statistic info
            //get the path of the property
            $finalPath = $columnDescription['finalPath'];
            $columnName = $columnDescription['columnName'];

            //error_reporting(0);

            foreach ($listInstances as $instanceSourceUri => $obj) {

                //get the bridged value, this method provides a brut value that can be performed by pther one
                //ex extract an attribute from xml dom

                $value = $p->trGetBridgePropertyValues($instanceSourceUri, $finalPath);

                //TEST IF IT IS AN URI
                $uriProperty = $value;
                $trProperty = new core_kernel_classes_Property($uriProperty);
                $valueLabel = $trProperty->getLabel(); // to see
                if ($valueLabel == NULL) {
                    
                }

                $rowsColumn[$instanceSourceUri] = $value;

                //Create the suitable array, this one is more simple to use with javascript to
                //generate the html code of table.
                $rowsHTML[$instanceSourceUri][$columnName] = $value;
            }//instances
            //put the rows in the column Model

            $table[$columnId]['rowsColumn'] = $rowsColumn;
            //print_r  ($table);
            //Get the stat info of the actual column
            $uStat = new UtrStatistic();
            $stat = $uStat->getStatOnColomn($table, $columnId);

            $totalRows = $stat['totalRows'];
            $totalRowsNotNull = $stat['totalRowsNotNull'];
            $columnDescription['totalRows'] = $totalRows;
            $columnDescription['totalRowsNotNull'] = $totalRowsNotNull;
        }//columns
        //get rows statistc
        $rowsInfo = array();

        foreach ($rowsHTML as $uri => $obj) {
            $stat = $uStat->getStatOnRows($rowsHTML, $uri);
            $rowsInfo[$uri] = $stat;
        }

        //convert to suitable table structure
        $tableF['rowsHTML'] = $rowsHTML; //to facilitate the html table generation
        $tableF['utrModel'] = $table; //the real model of the table, more scientists
        $tableF['rowsInfo'] = $rowsInfo; // the statistique of the the row
        //generation html
        //persistance
        $_SESSION['lastUTR'] = $tableF;
        $returnValue = $tableF;


        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002AB4 end

        return (array) $returnValue;
    }

    /**
     * Short description of method trGetInstances
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @return array
     */
    public function trGetInstances()
    {
        $returnValue = array();

        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002AB8 begin

        $tabUri = $_SESSION['instances'];
        $returnValue = $tabUri;

        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002AB8 end

        return (array) $returnValue;
    }

    /**
     * Short description of method getClassesOfinstances
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  array uriInstances
     * @return array
     */
    public function getClassesOfinstances($uriInstances)
    {
        $returnValue = array();

        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002ABA begin
        $t = $uriInstances; //$this->geInstances();

        $rc = new RegCommon();
        $classes = array();
        foreach ($t as $uri => $obj) {
            $ins = $uri;
            //Get all classes of current instance
            $ci = $rc->trGetClassesOfInstance($ins);
            //merge the classes of the actual instance with the list of all classes
            //of instances
            $classes = array_merge($classes, $ci);
            //print_r($classes);
        }
        //Now classes containe all classes of the list of instances
        //redendance
        $tab = array();
        foreach ($classes as $uri => $v) {

            //get the label and provide the result Array
            $trclass = new core_kernel_classes_Class($uri);
            $labelclass = $trclass->getLabel();
            $tclass['uriClass'] = $uri;
            $tclass['label'] = $labelclass;
            $tab[$uri] = $tclass;
        }


        $returnValue = $tab;
        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002ABA end

        return (array) $returnValue;
    }

    /**
     * Short description of method addColumn
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  array columnDescription
     * @param  array utrModel
     * @return array
     */
    public function addColumn($columnDescription, $utrModel)
    {
        $returnValue = array();

        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002ABD begin
        $desc = $columnDescription;
        $columnList = $utrModel; //$_SESSION['utrModel'];
        //timestamp
        //$columnId = microtime(true);
        $columnId = str_replace(" ", "_", $desc["columnName"]);

        $columnList[$columnId] = $columnDescription;
        //return the utrTable
        //save the intermediate table in session
        //$_SESSION['utrModel']=$columnList;

        $returnValue = $columnList;
        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002ABD end

        return (array) $returnValue;
    }

    /**
     * Short description of method saveUtrModel
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  array utrModel
     * @param  string idModel
     * @return string
     */
    public function saveUtrModel($utrModel, $idModel)
    {
        $returnValue = (string) '';

        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002AC1 begin
        //get the old list of model
        //if file not exist so create it
        if (!file_exists('utrModel.mdl')) {
            file_put_contents("utrModel.mdl", "");
        }


        $oldUtrModels = file_get_contents("utrModel.mdl");

        $tabUtrModels = json_decode($oldUtrModels, true);
        //add in tab of models
        //get the module
        $module = $this->trGetCurrentExtention();

        $tabUtrModels[$module][$idModel] = $utrModel;

        //convert to json and Save
        $jsonUtrModels = json_encode($tabUtrModels);

        file_put_contents("utrModel.mdl", $jsonUtrModels);
        $returnValue = __('Template Saved');
        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002AC1 end

        return (string) $returnValue;
    }

    /**
     * Short description of method loadUtrModel
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  string idModel
     * @return string
     */
    public function loadUtrModel($idModel)
    {
        $returnValue = (string) '';

        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002AC5 begin
        $actualUtr = array();

        //if file not exist so create it
        if (!file_exists('utrModel.mdl')) {
            file_put_contents("utrModel.mdl", "");
        }

        $jsonUtrModels = file_get_contents("utrModel.mdl");
        $tabUtrModels = json_decode($jsonUtrModels, true);
        $module = $this->trGetCurrentExtention();
        $actualUtr = $tabUtrModels[$module][$idModel];
        //print_r($tabUtrModels);

        $returnValue = $actualUtr;
        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002AC5 end

        return (string) $returnValue;
    }

    /**
     * Short description of method getListOfUtrModel
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @return array
     */
    public function getListOfUtrModel()
    {
        $returnValue = array();

        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002AC8 begin
        $actualUtr = array();
        $jsonUtrModels = file_get_contents("utrModel.mdl");
        $module = $this->trGetCurrentExtention();
        $tabUtrModels = json_decode($jsonUtrModels, true);
        $returnValue = $tabUtrModels[$module];

        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002AC8 end

        return (array) $returnValue;
    }

    /**
     * Short description of method createSimpleUtr
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  array listOfInstances
     * @param  array listOfProperties
     * @return array
     */
    public function createSimpleUtr($listOfInstances, $listOfProperties)
    {
        $returnValue = array();

        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002ACB begin
        //Create the column description
        $columnDescription = array();
        $utrModel = array();
        $p = new TReg_VirtualTable();
        foreach ($listOfProperties as $propUri => $label) {
            //cretae the description
            $columnDescription['columnName'] = $label;
            $columnDescription['typeExtraction'] = 'Direct';
            $columnDescription['finalPath'] = $propUri;
            //add the column into the utrModel
            $utrModel = $p->addColumn($columnDescription, $utrModel);
        }
        $_SESSION['utrModel'] = $utrModel; // for the persistance
        // generate the UTR table
        $t = $p->generateUTR($utrModel, $listOfInstances);
        $returnValue = $t; //TODO: change the UML modele to add return value
        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002ACB end

        return (array) $returnValue;
    }

    /**
     * Short description of method trGetCurrentExtention
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @return string
     */
    public function trGetCurrentExtention()
    {
        $returnValue = (string) '';

        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002ACF begin
        $p = new RegCommon();
        $currentExtension = $p->getCurrentModule();
        return $currentExtension;
        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002ACF end

        return (string) $returnValue;
    }

    /**
     * Short description of method exportCSV
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  array utrTable
     * @param  char del
     * @return mixed
     */
    public function exportCSV($utrTable, $del)
    {
        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002AD1 begin
        //based on rowsHTML already created we provide the csv
        $rowsHTML = $utrTable['rowsHTML'];
        $utrModel = $utrTable['utrModel'];

        $csvLines = array();

        $firstlineTab = array();
        foreach ($utrModel as $columnId => $columnDescription) {
            $firstlineTab[] = $columnDescription['columnName'];
        }

        $firstLine = implode($del, $firstlineTab);
        //save th first line, the manes of columns
        $csvLines[] = $firstLine;

        //Cretae the rows of the CSV

        foreach ($rowsHTML as $row => $rowContent) {
            $lineTab = array();
            //$row content is an associatif array wiith name of column:Value
            foreach ($rowContent as $columnId => $value) {
                $lineTab[] = $value;
            }
            $line = implode($del, $lineTab); //the line is created
            $csvLines[] = $line;
        }
        $csvUtr = implode("\n", $csvLines);
        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002AD1 end
    }

    /**
     * Short description of method exportToExcel
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  array utrTable
     * @return mixed
     */
    public function exportToExcel($utrTable)
    {
        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002AD5 begin
        
        //prepare the column name
        //besed on rowsHTML already created we provide the csv
        $rowsHTML = $utrTable['rowsHTML'];
        $utrModel = $utrTable['utrModel'];

        $firstlineTab = array();
        foreach ($utrModel as $columnId => $columnDescription) {
            $firstlineTab[] = $columnDescription['columnName'];
        }
        //$firstlineTab is the header
        $excelLines = array();

        foreach ($rowsHTML as $row => $rowContent) {
            $lineTab = array();
            //$row content is an associatif array with name of column:Value
            foreach ($rowContent as $columnId => $value) {
                $lineTab[] = $value;
            }
            $excelLines[] = $lineTab;
        }

        //Create the rows of the CSV

        $memoryRules = array();
        $memoryInfo = array();
        $title = $firstlineTab;

//add by younes
        $path = realpath("../lib") . '/';
        define('EXCEL_ROOT_PATH', $path);
        require_once('../lib/class.Excel.php');
        $params = array(
            //'directory' => '',
            'name' => 'TAO Table',
            'formats' => array(
                'titres' => array(
                    'bold' => 1,
                    'size' => 14,
                    'bgColor' => 'red',
                    'align' => 'center',
                ),
                'datas' => array(
                    'textWrap' => 1,
                    'size' => 12,
                    'align' => 'left',
                    'bgColor' => 'white',
                    'border' => array(),
                ),
            ),
            'pages' => array(
                'Results' => array(
                    'title' => $title,
                    'titleFormat' => 'titres',
                    'dataFormat' => 'datas',
                    'width' => array(40, 40, 40, 40, 40, 40),
                    'higlight' => 1,
                    'empty' => '-',
                    'data' => $excelLines
                )
            )
        );
        $url = osq_models_classes_Excel::createExcel($params);
        // section 10-13-1--65-3b6a288d:12d79aedebf:-8000:0000000000002AD5 end
    }

} /* end of class taoResults_models_classes_TReg_VirtualTable */

?>