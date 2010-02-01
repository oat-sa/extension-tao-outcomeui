<?php


error_reporting(E_ALL);

/**
 * this method intercept the request of the client and invoke the appropriate
 * This class is responsible of creating the table according TAO model and the
 * of the use
 * It interacts with a client side by AJAX request and provides a json result to
 * used by the client
 *
 * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
 * @package Result
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * RegCommon provides the common methods to acces generis API in more suitable
 * in Table Builder context.
 *
 * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
 */
require_once('RegCommon.php');


/**
 * this method intercept the request of the client and invoke the appropriate
 * This class is responsible of creating the table according TAO model and the
 * of the use
 * It interacts with a client side by AJAX request and provides a json result to
 * used by the client
 *
 * @access public
 * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
 * @package Result
 */
class TReg_VirtualTable extends RegCommon {
// --- ASSOCIATIONS ---


// --- ATTRIBUTES ---


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
     * Delete a row from the utr Table based on its uri
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  String $uriInstance
     * @return void
     */
    public function trDeleteRow($uriInstance) {
        unset($_SESSION['instances'][$uriInstance]);

    }
    /**
     * Delete a list  of rows from the utr Table based, it uses the trDeleteRow() method
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  void $uriInstance
     * @return void
     */

    public function trDeleteListRows($listRows) {
    //delete all the rows
        foreach($listRows as $uriInstance) {
            $this->trDeleteRow($uriInstance);
        }

    }

    /**
     * Delete the column, acording to its ID
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  String $columnId
     * @return void
     */
    public function YdeleteColumn($columnId) {
        $table = $_SESSION["utrModel"];
        unset($table[$columnId]);
        $_SESSION["utrModel"]=$table;

    }

    /**
     * this method provides the final table model with all the values in order
     * be used by client side to preview it on bases on utrModel, that containes
     * model of the columns model,
     * result we provide 3 tables :
     * //$tableF['rowsHTML']= $rowsHTML;//to facilitate the html table
     * //$tableF['utrModel']= $table;//the real model of the table, more
     *         //$tableF['rowsInfo']=$rowsInfo;
     *
     * @access private
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @return java_util_Collection
     */

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
            //for each instance in the list, get the value of the column
            $listInstances = $this->trGetInstances();
            foreach ( $listInstances as $instanceSourceUri=>$obj) {

            //get the bridged value, this method provides a brut value that can be performed by pther one
            //ex extract an attribute from xml dom

                $value=$p->trGetBridgePropertyValues($instanceSourceUri, $finalPath);

                //TEST IF IT IS AN URI
                $uriProperty = $value;
                $trProperty = new core_kernel_classes_Property($uriProperty);
                $value = $trProperty->getLabel();


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

    /**
     * get the initial list of instances, in the actual version, we get all the
     * of a class
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @return java_util_Collection
     */
    public function trGetInstances() {



        $tabUri= $_SESSION['instances'];
        return $tabUri;

    }
    /**
     * Get the list of classes of the initial instances
     * Result : $tclass['uriClass']=$uri; $tclass['label']=$labelclass;
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  String $uriInstance
     * @return java_util_Collection
     */
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

    /**
     * adds a column with all informations to the list of columns
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  Collection $columnDescription
     * @return void
     */
    public function YaddColumn($columnDescription) {
        $desc = $columnDescription;
        $columnList = $_SESSION['utrModel'];
        //timestamp
        $columnId = microtime(true);
        $columnId = $desc["columnName"];

        $columnList[$columnId]=$columnDescription;
        //save the intermediate table in session
        $_SESSION['utrModel']=$columnList;
    }

    /**
     * Gives more information about statistic of the column
     * number of rows, nomber of not null value in the column
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  Collection $table
     * @param  String $columnId
     * @return java_util_Collection
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
     * @return java_util_Collection
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
    /**
     * Save The UTR template
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  Collection $utrModel
     * @param  String $idModel
     * @return Collection
     */

    public function saveUtrModel($utrModel,$idModel) {

    //get the old list of model
        $oldUtrModels = file_get_contents("utrModel.mdl");
        $tabUtrModels = json_decode($oldUtrModels,true);
        //add in tab of models
        $tabUtrModels[$idModel]= $utrModel;

        //convert to json and Save
        $jsonUtrModels = json_encode($tabUtrModels);

        file_put_contents("utrModel.mdl", $jsonUtrModels);
        return 'Template Saved';

    }

    /**
     * Load a specific UTR Template
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  String $idModel
     * @return Collection
     */
    public function loadUtrModel($idModel) {
        $actualUtr = array ();

        $jsonUtrModels= file_get_contents("utrModel.mdl");
        $tabUtrModels = json_decode($jsonUtrModels,true);

        $actualUtr = $tabUtrModels[$idModel];
        //print_r($tabUtrModels);

        return $actualUtr;
    }

     /**
     * get the list of UTR Template
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @return Collection
     */
    public function getListOfUtrModel(){
        $actualUtr = array ();
        $jsonUtrModels= file_get_contents("utrModel.mdl");
        $tabUtrModels = json_decode($jsonUtrModels,true);
        return $tabUtrModels;

    }




    /**
     * this method intercept the request of the client (ajax) and invoke the
     * method
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @return void
     */
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

        //save utr model

        if ($_POST['op'] == 'saveUtr') {
            $idModel = $_POST['idModel'];
            echo $this->saveUtrModel($_SESSION["utrModel"], $idModel);


        }

        //load utr model
        if ($_POST['op'] == 'loadUtr') {
            $idModel = $_POST['idModel'];
            $_SESSION["utrModel"] = $this->loadUtrModel($idModel);

            $t=$this->generatePreview();
            echo json_encode($t);


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
        //delete a list of rows
        if ( $_POST['op'] == 'deleteListRows') {
        //get the list of rows as string
            $lr = $_POST['listRowsToDelete'];
            //create the tab
            $ListRows = explode('|',$lr);
            //delete the rows
            $this->trDeleteListRows($ListRows);

            $t = $this->generatePreview();
            echo json_encode($t);

        }

        //load utr models
        if ( $_POST['op'] == 'getUtrModels') {

            $t = $this->getListOfUtrModel();
            echo json_encode($t);

        }

    }

}

$p= new TReg_VirtualTable();
$p->dispatch();



?>