<?php

error_reporting(E_ALL);



if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * RegCommon provides the common methods to acces generis API in more suitable
 * in Table Builder context.
 *
 * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
 */
require_once('class.RegCommon.php');
require_once ('class.UtrStatistic.php');
require_once ('class.UtrFilter.php');

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
        __("Filter and search");
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

    public function __construct() {
        $p = new RegCommon();
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
    public function trDeleteRow($uriInstance, $listInstances) {
        $newListOfRows = $listInstances;
        unset($newListOfRows[$uriInstance]);

        //return the the new list of rows
        return $newListOfRows;
        //unset($_SESSION['instances'][$uriInstance]);
    }

    /**
     * Delete a list  of rows from the utr Table based, it uses the trDeleteRow() method
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  array $listRows
     * @param  array $uriInstance
     * @return array
     */
    public function trDeleteListRows($listRows, $listInstances) {
        //delete all the rows

        $newListOfRows = $listInstances;

        foreach ($listRows as $uriInstance) {
            $newListOfRows = $this->trDeleteRow($uriInstance, $newListOfRows);
        }

        return $newListOfRows; //the new list of rows
    }

    /**
     * Delete the column, acording to its ID
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  String $columnId
     * @param  Collection $utrModel
     * @return Collection
     */
    public function YdeleteColumn($columnId, $utrModel) {
        $newUtrModel = $utrModel; // $_SESSION["utrModel"];
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
     * @access public
     *
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  Collection $utrModel
     * @param  array $listInstances

     * @return Collection
     */
    public function generateUTR($utrModel, $listInstances) {
        // section 10-13-1--65--30cc15d0:1250bc77bd0:-8000:0000000000001023 begin
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
                if(common_Utils::isUri($uriProperty)){
                        $trProperty = new core_kernel_classes_Property($uriProperty);
                        $valueLabel = $trProperty->getLabel(); // to see
                        if(empty($valueLabel)){

                        }
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
        return $tableF;

        // section 10-13-1--65--30cc15d0:1250bc77bd0:-8000:0000000000001023 end
    }

    /**
     * get the initial list of instances, in the actual version, we get all the
     * of a class
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @return Collection
     */
    public function trGetInstances() {
        $tabUri = $_SESSION['instances'];
        return $tabUri;
    }

    /**
     * Get the list of classes of the initial instances
     * Result : $tclass['uriClass']=$uri; $tclass['label']=$labelclass;
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  array $uriInstances
     * @return Collection
     */
    public function getClassesOfinstances($uriInstances) {
        $t = $uriInstances; //$this->geInstances();


        $rc = new RegCommon();
        $classes = array();
        foreach ($t as $uri => $obj) {
            $ins = $uri;
            //Get all classes of the actual instance
            $ci = $rc->trGetClassesOfInstance($ins);
            //merge the classes of the actual instance with the list of all classes
            //of instances
            $classes = array_merge($classes, $ci);
            //print_r($classes);
        }
        //Now classes containes all classes of the list of nstances
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

        //echo '******';
        return $tab;
    }

    /**
     * adds a column with all informations to the list of columns
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  Collection $columnDescription
     * @param  Collection $utrModel
     * @return Collection
     */
    public function YaddColumn($columnDescription, $utrModel) {
        $desc = $columnDescription;
        $columnList = $utrModel; //$_SESSION['utrModel'];
        //timestamp
        //$columnId = microtime(true);
        $columnId = str_replace(" ", "_", $desc["columnName"]);

        $columnList[$columnId] = $columnDescription;
        //return the utrTable
        //save the intermediate table in session
        //$_SESSION['utrModel']=$columnList;

        return $columnList;
    }

    /**
     * Save The UTR template
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  Collection $utrModel
     * @param  String $idModel
     * @return String
     */
    public function saveUtrModel($utrModel, $idModel) {

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
        $actualUtr = array();
        $jsonUtrModels = file_get_contents("utrModel.mdl");
        $module = $this->trGetCurrentExtention();
        $tabUtrModels = json_decode($jsonUtrModels, true);
        return $tabUtrModels[$module];
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
    public function createSimpleUtr($listOfInstances, $listOfProperties) {
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
            $utrModel = $p->YaddColumn($columnDescription, $utrModel);
        }
        $_SESSION['utrModel'] = $utrModel; // for the persistance
        // generate the UTR table
        $t = $p->generateUTR($utrModel, $listOfInstances);
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
     * Export the UTR table into CSV format
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param array $utrTable
     * @param array $del
     * @return string
     */
    public function exportCSV($utrTable, $del) {
        //besed on rowsHTML already created we provide the csv
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
        //send
        //header('content-type:text/csv');
        //file_put_contents('coco.csv', $csvUtr);

        return $csvUtr;
    }

    /**
     * Export the UTR table into excel format
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param array $utrTable
     */
    public function exportToExcel($utrTable) {

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


        if (isset($_POST['op'])) {



            if ($_POST['op'] == 'listInstances') {
                $p = new TReg_VirtualTable();
                $t = $p->trGetInstances();
                echo json_encode($t);
            }
            //
            if ($_POST['op'] == 'getClassesOfInstances') {


                $p = new TReg_VirtualTable();
                $uriInstance = $p->trGetInstances();

                $t = $p->getClassesOfinstances($uriInstance);
                echo json_encode($t);
            }
            //
            if ($_POST['op'] == 'getProperties') {
                $p = new TReg_VirtualTable();

                $t = $p->trGetProperties($_POST['uriClass']);
                echo json_encode($t);
            }
            //
            if ($_POST['op'] == 'getContextClasses') {
                $p = new TReg_VirtualTable();
                $t = $p->getContextClasses($_POST['uriClass']);
                echo json_encode($t);
            }
            //delete the session
            if ($_POST['op'] == 'removeSession') {

                $_SESSION["utrModel"] = array();
            }

            //save utr model

            if ($_POST['op'] == 'saveUtr') {
                $idModel = $_POST['idModel'];
                $p = new TReg_VirtualTable();

                echo $p->saveUtrModel($_SESSION["utrModel"], $idModel);
            }

            //load utr model
            if ($_POST['op'] == 'loadUtr') {
                $idModel = $_POST['idModel'];
                $p = new TReg_VirtualTable();

                $utrModel = $p->loadUtrModel($idModel);
                $listInstances = $p->trGetInstances();

                $_SESSION['utrModel'] = $utrModel; // for the persistance

                $t = $p->generateUTR($utrModel, $listInstances);


                echo json_encode($t);
            }

            //Add column
            if ($_POST['op'] == 'addColumn') {
                //get column description
                $columnName = $_POST['columnName'];
                $typeExtraction = $_POST['typeExtraction'];
                $finalPath = $_POST['finalPath'];

                //Create the column description
                $columnDescription['columnName'] = $columnName;
                $columnDescription['typeExtraction'] = $typeExtraction;
                $columnDescription['finalPath'] = $finalPath;


                $p = new TReg_VirtualTable();
                $utrModel = array();

                $utrModel = $_SESSION['utrModel'];


                $utrModel = $p->YaddColumn($columnDescription, $utrModel);

                //save the context of utrModel
                $_SESSION['utrModel'] = $utrModel;

                //get the instances and generate the preview
                $listInstances = $this->trGetInstances();

                $t = $p->generateUTR($utrModel, $listInstances);
                //echo (__("coco"));

                echo json_encode($t);
            }
            //
            //Delete column the utrModel
            if ($_POST['op'] == 'deleteColumn') {
                //get column description
                $columnId = $_POST["columnId"];

                $p = new TReg_VirtualTable();

                $utrModel = array();
                $utrModel = $_SESSION['utrModel'];

                //Delete the column from the utrModel table
                $utrModel = $p->YdeleteColumn($columnId, $utrModel);

                //save the context of utrModel
                $_SESSION['utrModel'] = $utrModel;
                //see the new columnList

                $listInstances = $p->trGetInstances();
                $t = $p->generateUTR($utrModel, $listInstances);

                echo json_encode($t);
            }
            //delete a list of rows
            if ($_POST['op'] == 'deleteListRows') {
                //get the list of rows as string
                $lr = $_POST['listRowsToDelete'];

                $p = new TReg_VirtualTable();
                $listInstances = $p->trGetInstances();
                $utrModel = $_SESSION['utrModel'];
                //create the tab
                $ListRows = explode('|', $lr);
                //delete the rows
                $listInstances = $this->trDeleteListRows($ListRows, $listInstances);

                //persistance of the list of instances

                $_SESSION['instances'] = $listInstances;

                $t = $this->generateUTR($utrModel, $listInstances);
                echo json_encode($t);
            }

            //load utr models
            if ($_POST['op'] == 'getUtrModels') {

                $t = $this->getListOfUtrModel();
                echo json_encode($t);
            }
            //Create a simple UTR based on a list of properties sended directely by Bertrand

            if ($_POST['op'] == 'loadInitialUtr') {

                if (isset($_SESSION['utrListOfProperties'])) {

                    $p = new TReg_VirtualTable();
                    $utrModel = array();
                    //get the list of properties and the list of instancess
                    $listOfProperties = $_SESSION['utrListOfProperties']; // an array $list[uriProperty] = label of property

                    $listOfInstances = $p->trGetInstances();

                    //generate an UTR model
                    $utrTable = $p->createSimpleUtr($listOfInstances, $listOfProperties);

                    //unset the session var
                    $_SESSION['utrListOfProperties'] = array();

                    echo json_encode($utrTable);
                }
            }

            //set filter
            if ($_POST['op'] == 'sendFilter') {
                $filter = $_POST['filter'];

                //extract filter elements
                // get the filters in tab
                $tabOfFilters = explode("|*$", $filter);

                $finalTabOFilters = array();
                foreach ($tabOfFilters as $postFilterDescription) {

                    $tabFilter = explode("|||", $postFilterDescription);

                    $filterDescription['columnID'] = trim($tabFilter[0]);
                    $filterDescription['operator'] = trim($tabFilter[1]);
                    $filterDescription['value'] = trim($tabFilter[2]);

                    $finalTabOFilters[] = $filterDescription;
                }//foreach
                //print_r($filterDescription);
                //$_SESSION['filterDescription']= $filterDescription;

                $p = new TReg_VirtualTable();

                $utrModel = $_SESSION['utrModel']; //$p->loadUtrModel($idModel);
                $listInstances = $p->trGetInstances();

                //$t=$p->generateUTR($utrModel,$listInstances);
                $uFilter = new UtrFilter();
                $tf = $uFilter->filterAndGenerateUtr($finalTabOFilters, $utrModel, $listInstances);
                echo json_encode($tf);
            }


            //Load unfiltred UTR

            if ($_POST['op'] == 'loadUnfilteredUtr') {

                $p = new TReg_VirtualTable();
                $utrModel = $_SESSION['utrModel']; // for the persistance
                $listInstances = $p->trGetInstances();
                $t = $p->generateUTR($utrModel, $listInstances);
                echo json_encode($t);
            }
            

            
        }//if post [op]
        //export CSV

        if (isset($_GET['op'])) {

            if ($_GET['op'] == 'exoprtCSV') {

                //filter and export
                $utrTable = $_SESSION['lastUTR'];
                $csv = $this->exportCSV($utrTable, ';');
                header('Content-type:text/csv');
                header('Content-Disposition: attachement;filename="UTR.csv"');


                echo ($csv);
            }
            //export to excel
            if ($_GET['op'] == 'exportToExcel') {
                $utrTable = $_SESSION['lastUTR'];
                $this->exportToExcel($utrTable);




            }
        }
    }

//dispatch
}

$p = new TReg_VirtualTable();
//error_reporting(0);

$p->dispatch();
?>