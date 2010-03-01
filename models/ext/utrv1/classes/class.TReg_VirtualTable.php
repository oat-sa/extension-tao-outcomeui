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

    public function trad() {
        __("Build your table");
        __("Add colomn wizard");
        __("Remove rows");
        __("Template manager");
        __("Filter and search ");
        __("Add new filter");
        __("Delete filter");
        __("Delete filter");
        __("Apply filter");
        __("Cancel");
        __("Ok");
        __("Yes");
        __("No");
        __("Template saved");
        __("Back");
        __("Next");
        __("List of context classes");
        __("Root classes");
        __("List of properties");
        __("UTR Builder");
        __("Chose a property");
        __("Column name");
        __("Extraction method");
        __("Query");
        __("Exit");
        __("Info");
        __("Columns");
        __("Do you want to delete this column ?");
        __("Do you want to delete these rows ?");
        __("Error in loading table");
        __("Thank you for using UTR");
        __("Error, action failed !");
        __("Select a property");
        __("With UTR, you can");
        __("Build a flexible table to extract information");
        __("Build a complex table with no unlimited depth");
        __("You can dynamically Add, remove column");
        __("Calculate the percentage of columns and rows");
        __("Create save your own Template of tables");
        __("Get a direct chart diagram on your columns ans rows ");
        __("Welcome to UTR Builder");

    }
    //export CSV
    public function exportCSV($utrTable,$del) {
        //besed on rowsHTML already created we provide the csv
        $rowsHTML = $utrTable['rowsHTML'];
        $utrModel = $utrTable['utrModel'];

        $csvLines = array();

        $firstlineTab = array();
        foreach ( $utrModel as $columnId=>$columnDescription) {
            $firstlineTab[] = $columnDescription['columnName'];
        }

        $firstLine = implode($del, $firstlineTab);
        //save th first line, the manes of columns
        $csvLines[] = $firstLine;

        //Cretae the rows of the CSV

        foreach($rowsHTML as $row=>$rowContent) {
            $lineTab = array();
            //$row content is an associatif array wiith name of column:Value
            foreach ($rowContent as $columnId=>$value) {
                $lineTab[] = $value;
            }
            $line = implode($del,$lineTab);//the line is created
            $csvLines[] = $line;

        }
        $csvUtr = implode("\n",$csvLines);
        //send
        //header('content-type:text/csv');

        //file_put_contents('coco.csv', $csvUtr);

        return $csvUtr;

    }

    //get utr,filter get the new list of instances  and re Generate
    public function filterAndGenerateUtr ($lisOfFilterDescription,$utrModel,$listInstances) {
        //generate the first time to have all the values
        $utr = $this->generateUTR($utrModel, $listInstances);

        //get the utrModel
        $utrModelGenerated = $utr['utrModel'];

        // Apply the filter one after one, the order is important
        foreach($lisOfFilterDescription as $filterDescription) {
            //filter according

            $rows = $this->filterColumn($filterDescription, $utrModelGenerated);

            //regenerate the table based on the new list of instances
            $newRows = $rows['match'];

            // print_r($newRows);
            $utrGenerated = $this->generateUTR($utrModel, $newRows);
            $utrModelGenerated = $utrGenerated['utrModel'];

        }
        return $utrGenerated;
    }

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




    // --- OPERATIONS ---
    public function __construct() {
        $p = new  RegCommon();
        $p->regConnect();
        //print_r ($p->getCurrentModule());
    }

    /**
     * Delete a row from the utr Table based on its uri
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  String $uriInstance
     * @return void
     */
    public function trDeleteRow($uriInstance,$listInstances) {
        $newListOfRows = $listInstances;
        unset ( $newListOfRows[$uriInstance]);

        //return the the new list of rows
        return $newListOfRows;
        //unset($_SESSION['instances'][$uriInstance]);

    }
    /**
     * Delete a list  of rows from the utr Table based, it uses the trDeleteRow() method
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  void $uriInstance
     * @return void
     */

    public function trDeleteListRows($listRows,$listInstances) {
        //delete all the rows

        $newListOfRows = $listInstances;

        foreach($listRows as $uriInstance) {
            $newListOfRows= $this->trDeleteRow($uriInstance,$newListOfRows);
        }

        return $newListOfRows;
    }

    /**
     * Delete the column, acording to its ID
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  String $columnId
     * @return void
     */
    public function YdeleteColumn($columnId,$utrModel) {
        $newUtrModel =$utrModel;// $_SESSION["utrModel"];
        unset($newUtrModel[$columnId]);
        //return new table
        return $newUtrModel;

        //$_SESSION["utrModel"]=$table;
    }

    /**
     * this method provides the final table model with all the values in order
     * be used by client side to preview it on bases on utrModel, that containes
     * model of the columns model,
     * result we provide 3 tables :
     * //$tableF['rowsHTML']= $rowsHTML;//to facilitate the html table
     * //$tableF['utrModel']= $table;//the real model of the table, more
     * //$tableF['rowsInfo']=$rowsInfo;
     *
     * @access private
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @return java_util_Collection
     */

    public function generateUTR($utrModel,$listInstances) {
        // section 10-13-1--65--30cc15d0:1250bc77bd0:-8000:0000000000001023 begin
        $table = $utrModel;//$_SESSION['utrModel'];




        //get the list of instances
        //$listInstances = $this->trGetInstances();
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

            //error_reporting(0);

            foreach ( $listInstances as $instanceSourceUri=>$obj) {

                //get the bridged value, this method provides a brut value that can be performed by pther one
                //ex extract an attribute from xml dom

                $value=$p->trGetBridgePropertyValues($instanceSourceUri, $finalPath);

                //TEST IF IT IS AN URI
                $uriProperty = $value;
                $trProperty = new core_kernel_classes_Property($uriProperty);
                $value = $trProperty->getLabel();

                $rowsColumn[$instanceSourceUri] = $value;

                //Create the suitable array, this one is more simple to use with javascript to
                //generate the html code of table.
                $rowsHTML[$instanceSourceUri][$columnName] = $value;

            }//instances
            //put the rows in the column Model

            $table[$columnId]['rowsColumn']=$rowsColumn;
            //print_r  ($table);


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

        //persistance
        $_SESSION['lastUTR'] = $tableF;
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
     * @param  array $uriInstance
     * @return java_util_Collection
     */
    public function getClassesOfinstances($uriInstances) {
        $t=$uriInstances;//$this->getInstances();
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
    public function YaddColumn($columnDescription,$utrModel) {
        $desc = $columnDescription;
        $columnList = $utrModel;//$_SESSION['utrModel'];
        //timestamp
        $columnId = microtime(true);
        $columnId = $desc["columnName"];

        $columnList[$columnId]=$columnDescription;
        //return the utrTable

        //save the intermediate table in session
        //$_SESSION['utrModel']=$columnList;

        return $columnList;
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
    public function getListOfUtrModel() {
        $actualUtr = array ();
        $jsonUtrModels= file_get_contents("utrModel.mdl");
        $tabUtrModels = json_decode($jsonUtrModels,true);
        return $tabUtrModels;

    }

    /**
     * Generate an initial UTR base on intial instyances and list of properties
     *
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param array $listOfInstances
     * @param array $listOfProperties
     * @return Collection
     */
    public function createSimpleUtr($listOfInstances,$listOfProperties) {
        //Create the column description
        $columnDescription = array();
        $utrModel = array();
        $p= new TReg_VirtualTable();
        foreach($listOfProperties as $propUri=>$label) {
            //cretae the description
            $columnDescription['columnName'] = $label;
            $columnDescription['typeExtraction'] = 'Direct';
            $columnDescription['finalPath'] = $propUri;
            //add the column into the utrModel
            $utrModel=$p->YaddColumn($columnDescription, $utrModel);
        }
        // generate the UTR table
        $t=$p->generateUTR($utrModel,$listOfInstances);
        return $t;

    }
    /**
     * get the code of the current modul
     *
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     *
     * @return String
     */

    public function trGetCurrentExtention() {

        $p = new RegCommon();
        $currentExtension = $p->getCurrentModule();
        return $currentExtension;
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
            $p= new TReg_VirtualTable();

            echo $p->saveUtrModel($_SESSION["utrModel"], $idModel);

        }

        //load utr model
        if ($_POST['op'] == 'loadUtr') {
            $idModel = $_POST['idModel'];
            $p = new TReg_VirtualTable();

            $utrModel = $p->loadUtrModel($idModel);
            $listInstances = $p->trGetInstances();

            $_SESSION['utrModel'] = $utrModel;// for the persistance

            $t=$p->generateUTR($utrModel,$listInstances);


            echo json_encode($t);
        }

        //Add column
        if ($_POST['op']=='addColumn') {
            //get column description
            $columnName = $_POST['columnName'];
            $typeExtraction= $_POST['typeExtraction'];
            $finalPath = $_POST['finalPath'];

            //Create the column description
            $columnDescription['columnName'] = $columnName;
            $columnDescription['typeExtraction'] = $typeExtraction;
            $columnDescription['finalPath'] = $finalPath;


            $p= new TReg_VirtualTable();
            $utrModel = array();

            $utrModel = $_SESSION['utrModel'];


            $utrModel = $p->YaddColumn($columnDescription,$utrModel);

            //save the context of utrModel
            $_SESSION['utrModel'] = $utrModel;

            //get the instances and generate the preview
            $listInstances = $this->trGetInstances();

            $t=$p->generateUTR($utrModel,$listInstances);
            //echo (__("coco"));

            echo json_encode($t);
        }
        //
        //Delete column the utrModel
        if ($_POST['op']=='deleteColumn') {
            //get column description
            $columnId = $_POST["columnId"];

            $p= new TReg_VirtualTable();

            $utrModel = array();
            $utrModel = $_SESSION['utrModel'];

            //Delete the column from the utrModel table
            $utrModel = $p->YdeleteColumn($columnId,$utrModel);

            //save the context of utrModel
            $_SESSION['utrModel'] = $utrModel;
            //see the new columnList

            $listInstances = $p->trGetInstances();
            $t=$p->generateUTR($utrModel,$listInstances);

            echo json_encode($t);
        }
        //delete a list of rows
        if ( $_POST['op'] == 'deleteListRows') {
            //get the list of rows as string
            $lr = $_POST['listRowsToDelete'];

            $p= new TReg_VirtualTable();
            $listInstances = $p->trGetInstances();
            $utrModel = $_SESSION['utrModel'];
            //create the tab
            $ListRows = explode('|',$lr);
            //delete the rows
            $listInstances = $this->trDeleteListRows($ListRows,$listInstances);

            //persistance of the list of instances

            $_SESSION['instances'] =$listInstances;

            $t = $this->generateUTR($utrModel,$listInstances);
            echo json_encode($t);

        }

        //load utr models
        if ( $_POST['op'] == 'getUtrModels') {

            $t = $this->getListOfUtrModel();
            echo json_encode($t);

        }
        //Create a simple UTR based on a list of properties sended directely by Bertrand

        if ($_POST['op'] == 'loadInitialUtr') {


            //http://localhost/middleware/taov1.rdf#i1263288559029078400
            //http://localhost/middleware/taov1.rdf#i1264523889019415800

            /*$_SESSION['utrListOfProperties']['http://localhost/middleware/taov1.rdf#i1264523889019415800']="prop";
            $_SESSION['utrListOfProperties']['http://localhost/middleware/taov1.rdf#i1263288559029078400']="gender";
            $_SESSION['utrListOfProperties']['http://www.w3.org/2000/01/rdf-schema#label']="lABEL";*/

            if (isset($_SESSION['utrListOfProperties'])) {

                $p = new TReg_VirtualTable();
                $utrModel = array();
                //get the list of properties and the list of instancess
                $listOfProperties = $_SESSION['utrListOfProperties'];// an array $list[uriProperty] = label of property
                $listOfInstances = $p->trGetInstances();

                //generate an UTR model
                $utrModel = $p->createSimpleUtr($listOfInstances, $listOfProperties);

                $_SESSION['utrModel'] = $utrModel;// for the persistance
                //unset the session var
                $_SESSION['utrListOfProperties'] =array();

                echo json_encode($utrTable);
            }
        }

        //set filter
        if ($_POST['op']=='sendFilter') {
            $filter = $_POST['filter'];

            //extract filter elements
            // get the filters in tab
            $tabOfFilters = explode("|*$",$filter);

            $finalTabOFilters = array();
            foreach ($tabOfFilters as $postFilterDescription) {

                $tabFilter = explode("|||",$postFilterDescription);

                $filterDescription['columnID'] =trim($tabFilter[0]);
                $filterDescription['operator']=trim($tabFilter[1]);
                $filterDescription['value']=trim($tabFilter[2]);

                $finalTabOFilters[] = $filterDescription;

            }//foreach

            //print_r($filterDescription);

            //$_SESSION['filterDescription']= $filterDescription;

            $p = new TReg_VirtualTable();

            $utrModel = $_SESSION['utrModel'];//$p->loadUtrModel($idModel);
            $listInstances = $p->trGetInstances();

            //$t=$p->generateUTR($utrModel,$listInstances);

            $tf = $p->filterAndGenerateUtr($finalTabOFilters, $utrModel, $listInstances);
            echo json_encode($tf);

        }
        //export CSV

        if ($_POST["op"]=='exportCSV') {
            //filter and export
            $utrTable = $_SESSION['lastUTR'];
            $csv= $this->exportCSV($utrTable, ';');

            echo json_encode($csv);

        }

    }//dispatch

}

$p= new TReg_VirtualTable();
$p->dispatch();

?>