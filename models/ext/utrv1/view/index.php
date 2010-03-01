<?session_start();?>
<!--
To change this template, choose Tools | Templates
and open the template in the editor.
-->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <title></title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <script type="text/javascript" src="javascript/jquery-1.3.2.min.js"></script>
        <script type="text/javascript" src="javascript/raphael.js"></script>
        <script type="text/javascript" src="javascript/pie.js"></script>

        <script type="text/javascript" src="locales/<?=$_SESSION['lang']?>/messages_po.js"></script>
        <script type="text/javascript" src="javascript/i18n.js"></script>

        <script type="text/javascript" src="javascript/utrfactory.js"></script>

        <link rel="stylesheet" type="text/css" href="cssfiles/default/basic.css">
        
    </head>
    <body>
        <div id="utrDiv" >
            <div id="divPathWizard" class="divstandard">
                <div id="menuPathWizard">
                    UTR BUILDER... chose the property ...
                    <input class="closePathBuilderClass" id="closePathBuilder" type="button" value=""> </input>
                </div>


                <div id="classesDiv">
                    <div id="contextClassHeader" class="boxHeader">

                        <input id="backClass" type="button" value="Back" name="backClass"/>
                        <h1>Classe</h1>

                    </div>
                    <div id="contextClasses" class="contextClassesStyle">

                        <h1> list of classes</h1>
                    </div>
                </div>
                <div id="propertiesDiv">
                    <div id="contextPropertiesHeader" class="boxHeader">
                        <h1>...</h1>
                    </div>

                    <div id="contextProperties" class="contextPropertiesStyle">
                        <h1>List of properties</h1>
                    </div>

                </div>

                <div id="divFooterPathWizard" style="clear:both">

                </div>
            </div>

            <div id="propertyBinding" class="centered">
                <table border="0" cellpadding="0">

                    <tbody>
                        <tr>
                            <td>Column Name:</td>
                            <td><input id="columnName" type="text" name="" value="" /></td>
                        </tr>
                        <tr>
                            <td>Extraction Method:</td>
                            <td><select id="typeExtraction" name="ExtractionMethodDrop">
                                    <option value="direct" >Direct </option>
                                    <option value="xpath" >XPath query </option>
                                    <option value="function" >Function </option>
                                </select></td>
                        </tr>
                        <tr>
                            <td>Query</td>
                            <td><input id="finalPath" type="text" name="finalPath" value="" size="30" /></td>
                        </tr>

                        <tr
                            <td>
                                <input id="addColumn" type="button" value="Add Column" /><input id="exitAddColumn" type="button" value="Exit" />

                            </td>

                        </tr>

                    </tbody>
                </table>


            </div>


            <div id="utrmenu">
                <input id="columnBuilder" type="submit" value="Column Builder Wizard..." /><input id="deleteListRows" type="submit" value="Delete Rows..." /><input id="manageUtr" type="submit" value="Template Manager..." /><input id="manageFilter" type="submit" value="Filter" /><input id="export" type="submit" value="Export" />
                
            </div>

            <table id="UTR" border="1">
                <thead id="utrHead">


                </thead>
                <tbody id="utrBody" >

                </tbody>
            </table>

            <div id="utrTemplateManager" class="">
                <div id="#utrTemplateTitle">
                    utr
                </div>

                <div id="utrTemplateModelList">


                </div>

                <div id="utrTempateMenu">
                    <input id="saveUtrBtn" type="submit" value="save Table" /><input id ="txtUtrName" type="text" name="txtUtrName" value="" /><br>
                    <input id="cancelUtrManager" type="submit" value="Cancel..." />
                </div>

            </div>
        </div>
        <div id="pieStat" class="pieStatClass">
            <input id="hidePieStat" type="button" value="Hide" />

        </div>

        <div id="filterUtr">
            <table border="1" width="1" cellspacing="1">
                <thead>
                    <tr>
                        <th>C</th>
                        <th>F</th>
                        <th>V</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><input id="searchProperty1" type="text" name="searchProperty1" value="" size="10" /></td>
                        <td><input id="searchOperator1" type="text" name="searchOperator1" value="" size="5" /></td>
                        <td><input id="searchValue1" type="text" name="searchValue1" value="" size="10" /></td>
                    </tr>
                    <tr>
                        <td><input id="searchProperty2" type="text" name="searchProperty2" value="" size="10" /></td>
                        <td><input id="searchOperator2" type="text" name="searchOperator2" value="" size="5" /></td>
                        <td><input id="searchValue2" type="text" name="searchValue2" value="" size="10" /></td>
                    </tr>
                    <tr>
                        <td><input id="searchProperty3" type="text" name="searchProperty3" value="" size="10" /></td>
                        <td><input id="searchOperator3" type="text" name="searchOperator3" value="" size="5" /></td>
                        <td><input id="searchValue3" type="text" name="searchValue3" value="" size="10" /></td>
                    </tr>
                    <tr>
                        <td><input id="searchProperty4" type="text" name="searchProperty4" value="" size="10" /></td>
                        <td><input id="searchOperator4" type="text" name="searchOperator4" value="" size="5" /></td>
                        <td><input id="searchValue4" type="text" name="searchValue4" value="" size="10" /></td>
                    </tr>
                    <tr>
                        <td><input id="searchProperty5" type="text" name="searchProperty4" value="" size="10" /></td>
                        <td><input id="searchOperator5" type="text" name="searchOperator4" value="" size="5" /></td>
                        <td><input id="searchValue5" type="text" name="searchValue4" value="" size="10" /></td>
                    </tr>



                    <tr>
                        <td><input id="sendFilter" type="submit" value="Send Filter" /></td>
                        <td><input id="cancelFilter" type="submit" value="Cancel" /></td>
                        <td></td>
                    </tr>



                </tbody>
            </table>


        </div>




    </body>
</html>
