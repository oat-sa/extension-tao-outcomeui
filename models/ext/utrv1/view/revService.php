<?
require_once($_SERVER['DOCUMENT_ROOT']."/generis/common/inc.extension.php");
require_once($_SERVER['DOCUMENT_ROOT']."/taoResults/includes/common.php");
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

        <script type="text/javascript" src="javascript/revfactory.js"></script>

    </head>
    <div id="itemDescription" class="ui-widget-content ui-corner-all">
        <h1 class ="ui-widget-header ui-corner-all"> Responce of the test maker</h1>

        <textarea id="responceOfTestee" rows="5" cols="50"></textarea>

    </div>

    <div id="Rev1Zone" class ="ui-widget-content ui-corner-all">
        <h1 class ="ui-widget-header ui-corner-all"> Reviewer 1</h1>
        <table border="0">
            <thead>
                <tr>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Reviewer ID</td>
                    <td><input id="revId_1" type="text" name="" value="" /></td>
                </tr>
                <tr>
                    <td>Reviewer Endorsement:</td>
                    <td><input id="revEndorsement_1" type="text" name="" value="" /></td>
                </tr>
                <tr>
                    <td>Reviewer Comment:</td>
                    <td>
                        <textarea id="revComment_1" name="" rows="4" cols="20"></textarea>

                    </td>
                </tr>


            </tbody>
        </table>
        <input id="okRev1" type="submit" value="OK" />

    </div>

    <div id="Rev2Zone" class ="ui-widget-content ui-corner-all">
        <h1 class ="ui-widget-header ui-corner-all"> Reviewer 2</h1>
        <table border="0">
            <thead>
                <tr>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Reviewer ID</td>
                    <td><input id="revId_2" type="text" name="" value="" /></td>
                </tr>
                <tr>
                    <td>Reviewer Endorsement:</td>
                    <td><input id="revEndorsement_2" type="text" name="" value="" /></td>
                </tr>
                <tr>
                    <td>Reviewer Comment:</td>
                    <td>
                        <textarea id="revComment_2" name="" rows="4" cols="20"></textarea>

                    </td>
                </tr>

            </tbody>
        </table>
        <input id="okRev2" type="submit" value="OK" />

    </div>

    <div id="finalRevZone" class ="ui-widget-content ui-corner-all">

        <h1 class ="ui-widget-header ui-corner-all"> Final Reviewer </h1>
        <table border="0">
            <thead>
                <tr>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>

                <tr>
                    <td>Final Endorsement:</td>
                    <td><input id="revEndorsement_Final" type="text" name="" value="" /></td>
                </tr>
                <tr>
                    <td>Final Comment:</td>
                    <td>
                        <textarea id="revComment_Final" name="" rows="4" cols="20"></textarea>

                    </td>
                </tr>

            </tbody>
        </table>
        <input id="okRevFinal" type="submit" value="OK" />
    </div>

    <body>

    </body>
</html>
