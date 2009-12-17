<?php

//session_start();


error_reporting(E_ALL);

/**
 * untitledModel - class.TReg_VirtualTable.php
 *
 * $Id$
 *
 * This file is part of untitledModel.
 *
 * Automatically generated on 19.11.2009, 11:13:13 with ArgoUML PHP module
 * (last revised $Date: 2008-04-19 08:22:08 +0200 (Sat, 19 Apr 2008) $)
 *
 * @author Younes Djaghloul
 * @version 0.1
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * The column model of Treg extension
 *
 * @author Younes Djaghloul
 * @version 0.1
 */
require_once('RegCommon.php');
//require_once('class.TReg_ColumnModel.php');

/* user defined includes */
// section 10-13-1--65-4655aef3:124e777cdc4:-8000:0000000000000FF2-includes begin
// section 10-13-1--65-4655aef3:124e777cdc4:-8000:0000000000000FF2-includes end

/* user defined constants */
// section 10-13-1--65-4655aef3:124e777cdc4:-8000:0000000000000FF2-constants begin
// section 10-13-1--65-4655aef3:124e777cdc4:-8000:0000000000000FF2-constants end

/**
 * Short description of class TReg_VirtualTable
 *
 * @access public
 * @author Younes Djaghloul
 * @version 0.1
 */
class TReg_VirtualTable extends RegCommon {
// --- ASSOCIATIONS ---
// generateAssociationEnd : 

// --- ATTRIBUTES ---

/**
 * Short description of attribute listOfColomn
 *
 * @access public
 * @var TReg_ColumnModel
 */
    public $listOfColomn = null;

    /**
     * Short description of attribute listOfInstance
     *
     * @access public
     * @var List
     */
    public $listOfInstance = null;

    // --- OPERATIONS ---
    public function __construct() {
        $p = new  RegCommon();
        $p->regConnect();
    }

    /**
     * Short description of method addColomn
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @param  TReg_ColumnModel colomn
     * @return void
     */
    public function addColumn( TReg_ColumnModel $colomn) {
    // section 10-13-1--65--30cc15d0:1250bc77bd0:-8000:0000000000001016 begin
    // section 10-13-1--65--30cc15d0:1250bc77bd0:-8000:0000000000001016 end
    }

    /**
     * Short description of method deleteColomn
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @param  void idColumn
     * @return void
     */
    public function deleteColomn( void $idColumn) {
    // section 10-13-1--65--30cc15d0:1250bc77bd0:-8000:0000000000001019 begin
    // section 10-13-1--65--30cc15d0:1250bc77bd0:-8000:0000000000001019 end
    }

    /**
     * Short description of method generatePreview
     *
     * @access public
     * @author firstname and lastname of author, <author@example.org>
     * @return void
     */

    //delete the colum, acording to its ID
    public function YdeleteColumn($columnId) {
        $table = $_SESSION["utrModel"];
        unset($table[$columnId]);
        $_SESSION["utrModel"]=$table;
        
       
    }

    //this method provides the final table model with all the values in order to be used by cleint side to preview it
    //on bases on utrModel, that containes the model of the columns model, 
    //result we provide 3 tables :
        //$tableF['rowsHTML']= $rowsHTML;//to facilitate the html table generation
        //$tableF['utrModel']= $table;//the real model of the table, more scientists
        //$tableF['rowsInfo']=$rowsInfo;
    
    public function generatePreview() {
    // section 10-13-1--65--30cc15d0:1250bc77bd0:-8000:0000000000001023 begin
        $table = $_SESSION['utrModel'];
        //get the list of instances
        $listInstances = $this->trGetInstances();
        //for each column we get the value according to instance and path
        $rowsColumn = array();
        $rowsHTML = array();
        $rowsTableHtml = array();

        $p = new  RegCommon(); // in orderr to get the bridged values
        //for each column in the model
        foreach ($table as $columnId=>&$columnDescription) {//the column description will be changed by adding the statistic info
        //get the path of the property
            $finalPath = $columnDescription['finalPath'];
            $columnName = $columnDescription['columnName'];
            //for each instance in the list, getthe value of the column
            $listInstances = $this->trGetInstances();
            foreach ( $listInstances as $instanceSourceUri=>$obj) {

            //get the bridged value, this method provides a brut value that can be performed by pther one
            //ex extract an attribute from xml dom

                $value=$p->trGetBridgePropertyValues($instanceSourceUri, $finalPath);
                $rowsColumn[$instanceSourceUri] = $value;

                //Create the suitable array, this one is more simple to use with javascripte to
                //generate the html code of table.
                $rowsHTML[$instanceSourceUri][$columnName] = $value;

            }//instances
            //put the rows in the column Model
            $table[$columnId]['rowsColumn']=$rowsColumn;

            //Get the stat info of the actual column
            $stat=$this->getStatOnColomn($table, $columnId);
            $totalRows = $stat['totalRows'];
            $totalRowsNotNull = $stat['totalRowsNotNull'];
            $columnDescription['totalRows'] = $totalRows;
            $columnDescription['totalRowsNotNull'] = $totalRowsNotNull;

        }//columns

        //get rows statistc
        $rowsInfo = array();

        foreach ($rowsHTML as $uri=>$obj) {
            $stat=$this->getStatOnRows($rowsHTML, $uri);
            $rowsInfo[$uri]=$stat;

        }

        //convert to suitable table structure
        $tableF['rowsHTML']= $rowsHTML;//to facilitate the html table generation
        $tableF['utrModel']= $table;//the real model of the table, more scientists
        $tableF['rowsInfo']=$rowsInfo;// the statistique of the the row
        //generation html
        return $tableF;

    // section 10-13-1--65--30cc15d0:1250bc77bd0:-8000:0000000000001023 end
    }

    //get the initial list of instances, in the actual version, we get all the instances of a class
    public function trGetInstances() {

//        $uriClass = studentUri;
//        $trClass = new core_kernel_classes_Class($uriClass);
//        $listInstances = $trClass->getInstances();
//        $tab = array();
//        foreach ($listInstances as $uri=>$obj) {
//            $tab[$uri]=$obj;
//        }
//        return $tab;

        $tabUri= $_SESSION['instances'];
        return $tabUri;

    }
    //get the list of classes of the initial instances
    //Result : $tclass['uriClass']=$uri; $tclass['label']=$labelclass;
    public function getClassesOfinstances($uriInstance) {
        $t=$uriInstance;//$this->getInstances();
        $rc = new RegCommon();
        $classes = array();
        foreach ($t as $uri=>$obj) {
            $ins = $uri;
            //Get all classes of the actual instance
            $ci = $rc->trGetClassesOfInstance($ins);
            //merge the classes of the actual instance with the list of all classes
            //of instances
            $classes = array_merge($classes,$ci);
        }
        //Now classes containes all classes of the list of nstances
        //redendance
        $tab = array();
        foreach ($classes as $uri=>$v) {

        //get the label and provide the result Array
            $trclass = new core_kernel_classes_Class($uri);
            $labelclass = $trclass->getLabel();
            $tclass['uriClass']=$uri;
            $tclass['label']=$labelclass;
            $tab[$uri]=$tclass;
        }
        return $tab;
    }

    //addColumn adds a column with all informations to the list of colomns
    public function YaddColumn($columnDescription) {
        $desc = $columnDescription;
        $columnList = $_SESSION['utrModel'];
        $columnList[$desc["columnName"]]=$columnDescription;
        //save the intermediate table in session
        $_SESSION['utrModel']=$columnList;
    }

    //Gives more information about statistic of the column
    //number of rows, nomber of not null value in the column

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

    //Gives more information about a row, the row id is the uri
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

    //this method intercept sthe request of client and invok the appropriate methode
    public function dispatch() {

        if ($_POST['op']=='listInstances') {
            $p = new TReg_VirtualTable();
            $t= $p->trGetInstances();
            echo json_encode($t);
        }
        //
        if ($_POST['op']=='getClassesOfInstances') {
            $p = new TReg_VirtualTable();
            $uriInstance = $p->trGetInstances();
            $t= $p->getClassesOfinstances($uriInstance);
            echo json_encode($t);
        }
//
        if ($_POST['op']=='getProperties') {

            $p = new TReg_VirtualTable();

            $t= $p->trGetProperties($_POST['uriClass']);
            echo json_encode($t);
        }
//
        if ($_POST['op']=='getRangeClasses') {
            $p= new TReg_VirtualTable();
            $t = $p->trGetRangeClasses($_POST['uriClass']);
            echo json_encode($t);
        }
        //delete the session

        if ($_POST['op'] == 'removeSession') {
            $_SESSION["utrModel"] = array();
        }
        //
        if ($_POST['op']=='addColumn') {
        //get column description
            $columnName = $_POST['columnName'];
            $typeExtraction= $_POST['typeExtraction'];
            $finalPath = $_POST['finalPath'];

            $columnDescription['columnName'] = $columnName;
            $columnDescription['typeExtraction'] = $typeExtraction;
            $columnDescription['finalPath'] = $finalPath;

            $p= new TReg_VirtualTable();
            $p->YaddColumn($columnDescription);

            //see the new columnList

            $t=$p->generatePreview();

            echo json_encode($t);
        }
//
        //add column the utrModel
        if ($_POST['op']=='deleteColumn') {
        //get column description
            $columnId = $_POST["columnId"];
            $p= new TReg_VirtualTable();
            $p->YdeleteColumn($columnId);

            //see the new columnList

            $t=$p->generatePreview();

            echo json_encode($t);
        }



    }

} 
$p= new TReg_VirtualTable();
$p->dispatch();
////$t=$p->getInstances();
////$c = $p->getClassesOfinstances($t);
////print_r($c);
//
//$tab["name"] = "younes";
//$tab["age"]=28;
//
//$a[]=$tab;
//$tab["name"] = "younes";
//$tab["age"]=28;
//$a[]=$tab;
//print_r($a);
//echo json_encode($a);




?>