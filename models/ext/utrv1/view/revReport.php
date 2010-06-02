<?
require_once($_SERVER['DOCUMENT_ROOT']."/generis/common/inc.extension.php");
require_once($_SERVER['DOCUMENT_ROOT']."/taoResults/includes/common.php");
//$_SESSION["revType"]= $_GET[revType];
//get the parameters of the workflow



$revType = urldecode($_GET['revType']);
$revIdCurrent=urldecode($_GET['revIdCurrent']);
$revTestId=urldecode($_GET['revTestId']);
$revSubjectId=urldecode($_GET['revSubjectId']);
$revItemId=urldecode($_GET['revItemId']);

//put in the session
$_SESSION['revType']= $revType;
$_SESSION['revIdCurrent']= $revIdCurrent;
$_SESSION['revTestId']= $revTestId;
$_SESSION['revSubjectId']= $revSubjectId;
$_SESSION['revItemId']= $revItemId;
?>
<!--
To change this template, choose Tools | Templates
and open the template in the editor.
-->

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>

    <head>
        <title></title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="stylesheet" type="text/css" href="cssfiles/default/basic.css">

        <link rel="stylesheet" type="text/css" media="screen" href="javascript/jqGrid/css/ui.jqgrid.css" />
        <link rel="stylesheet" type="text/css" media="screen" href="javascript/jquery/jqueryui/themes/redmond/jquery-ui-1.7.1.custom.css" />

        <script src="javascript/jquery-1.3.2.min.js"></script>

        <script src="javascript/jqGrid/js/jquery.jqGrid.min.js"></script>
        <script src="javascript/jquery/jqueryui/jquery-ui-1.8.custom.min.js"></script>

        <script type="text/javascript" src="locales/<?=$_SESSION['lang']?>/messages_po.js"></script>
        <script type="text/javascript" src="javascript/i18n.js"></script>

        <script type="text/javascript" src="javascript/revreport.js"></script>

    </head>
    <div id="container">

        <div id="itemDescription" class="ui-widget-content ui-corner-all">
            <h1 class ="ui-widget-header ui-corner-all"><?=__("Response of the test maker") ?> </h1>
            <table border="0">

                <tbody>
                    <tr>
                        <td><h1><?=__("Subject:") ?> </h1></td>
                        <td><h1 id="subjectId"> </h1></td>
                    </tr>
                    <tr>
                        <td> <h1><?=__("Test:") ?> </h1></td>
                        <td><h1 id="testId"></td>
                    </tr>
                    <tr>
                        <td><h1><?=__("Item:") ?> </h1></td>
                        <td><h1 id="itemId"></h1></td>
                    </tr>
                    <tr>
                        <td><h1><?=__("Response") ?> </h1></td>
                        <td><textarea id="responceOfTestee" rows="10" cols="100"></textarea></td>
                    </tr>
                </tbody>
            </table>

        </div>

        <div id="reviewersReport">

            <div id="Rev1Zone" class ="ui-widget-content ui-corner-all">
                <h1 class ="ui-widget-header ui-corner-all"> Reviewer 1</h1>
                <table border="1">

                    <tbody>
                        <tr>
                            <td>Reviewer ID</td>
                            <td id="revId_1"></td>
                        </tr>
                        <tr>
                            <td>Reviewer Endorsement:</td>
                            <td id="revEndorsement_1"></td>
                        </tr>
                        <tr>
                            <td>Reviewer Comment:</td>
                            <td id="revComment_1">
                            </td>
                        </tr>

                    </tbody>
                </table>

            </div>

            <div id="Rev1Zone" class ="ui-widget-content ui-corner-all">
                <h1 class ="ui-widget-header ui-corner-all"> Reviewer 2</h1>
                <table border="1">

                    <tbody>
                        <tr>
                            <td>Reviewer ID</td>
                            <td id="revId_2"></td>
                        </tr>
                        <tr>
                            <td>Reviewer Endorsement:</td>
                            <td id="revEndorsement_2"></td>
                        </tr>
                        <tr>
                            <td>Reviewer Comment:</td>
                            <td id="revComment_2">
                            </td>
                        </tr>

                    </tbody>
                </table>

            </div>

            <div id="Rev1Zone" class ="ui-widget-content ui-corner-all">
                <h1 class ="ui-widget-header ui-corner-all"> Reviewer 3</h1>
                <table border="1">

                    <tbody>
                        <tr>
                            <td>Reviewer ID</td>
                            <td id="revId_3"></td>
                        </tr>
                        <tr>
                            <td>Reviewer Endorsement:</td>
                            <td id="revEndorsement_3"></td>
                        </tr>
                        <tr>
                            <td>Reviewer Comment:</td>
                            <td id="revComment_3">
                            </td>
                        </tr>


                    </tbody>
                </table>

            </div>

            <div id="Rev1Zone" class ="ui-widget-content ui-corner-all">
                <h1 class ="ui-widget-header ui-corner-all"> Reviewer 4</h1>
                <table border="1">

                    <tbody>
                        <tr>
                            <td>Reviewer ID</td>
                            <td id="revId_4"></td>
                        </tr>
                        <tr>
                            <td>Reviewer Endorsement:</td>
                            <td id="revEndorsement_4"></td>
                        </tr>
                        <tr>
                            <td>Reviewer Comment:</td>
                            <td id="revComment_4">
                            </td>
                        </tr>


                    </tbody>
                </table>

            </div>


            <div id="finalRevZone" class ="ui-widget-content ui-corner-all">

                <h1 class ="ui-widget-header ui-corner-all"> Final Reviewer </h1>
                <table border="1">
                    <thead>
                        <tr>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>

                        <tr>
                            <td>Final Endorsement:</td>
                            <td id="revEndorsement_Final"></td>
                        </tr>
                        <tr>
                            <td>Final Comment:</td>
                            <td id="revComment_Final">
                               
                            </td>
                        </tr>

                    </tbody>
                </table>
              
            </div>

        </div>

    </div>
    <body>

    </body>
</html>
