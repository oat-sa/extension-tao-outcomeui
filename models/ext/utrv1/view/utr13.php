<?php
/*  
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 * 
 * Copyright (c) 2002-2008 (original work) Public Research Centre Henri Tudor & University of Luxembourg (under the project TAO & TAO2);
 *               2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);\n *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 * 
 */
?>
<html>
    <head>
        <link href="js/jquery/jqueryui/themes/base/jquery.ui.all.css" rel="stylesheet" type="text/css"/>
        <script src="js/jquery/jquery-1.4.2.min.js"></script>
        <script src="js/jquery/jqueryui/jquery-ui-1.8.custom.min.js"></script>

        <script>


            $(document).ready(function() {
                

            });
        </script>


    </head>
    <body>
        <!--        <div class="ui-widget-header ui-corner-top ui-state-default"> sdfds </div> -->

        <div id="utrContainer">
            <!-- the utrTableManagement is the first Tab in the UTR, according to the template of Sophie is the tab Table-->
            <div id="utrTableMangement" class="ext-home-container ui-state-highlight ui-corner-top" style="height:100%" ><!--The first Tab-->
                <div id="utrHeaderTableMangement" class="ui-widget-header  ui-state-default"><h1 style="margin:1px" id="utrNameOfTheTable">utrTableMangement header</h1></div>

                <div id="utrTableData" class="ui-widget-content ui-corner-top" style="float:left;width: 75%;height: 80%;margin: 1px;">
                    <div id="utrHeaderTableData" class="ui-widget-header ui-corner-top ui-state-defaultl" ><h1 style="margin:1px" style="margin:1px">Name of the table</h1></div>
                    <!-- the table it self-->
                    <div id="utrTableDataContent" class="ui-widget-content " style ="margin: 1px; height:80%;overflow: auto">
                        content of the table

                    </div>
                    <div id="utrTableDataFooter" class="ui-widget-content ui-corner-all" style="height:10%"><!--some buttons hide and export-->
                        des bouttons
                    </div>


                </div>
                <!-- same level of utrTableData at the right side variable + table manager-->
                <div id="utrRightAction" class="ui-widget-content ui-corner-all" style="float:left;width: 24%;height: 80%;">

                    <!-- in this div, one has tow mains DIVs variable + table manager,
                    and I add filter also according to the recomendation of Cédric-->





                    <div id="utrVariables" style="height:40%;margin-top: 0px">
                        <div id="utrHeaderVariables" class="ui-widget-header ui-corner-top ui-state-default"><h1 style="margin:1px">Variablesgg</h1></div>
                        <div id="utrVariableContent" class="ui-widget-content " style ="height:80%;margin: 0px;overflow: auto">  var </div>

                        
                    </div>

                    <div id ="utrViewManager" style="height:30%;margin-top: 1px">
                        <div id="utrHeaderViewManager" class="ui-widget-header ui-corner-top ui-state-default"><h1 style="margin:1px">Views</h1></div>
                        <div id="utrViewContent" class="ui-widget-content " style ="height:75%;margin: 0px;overflow: auto">  wiew </div>


                    </div>

                    <div id="utrFilter" style="height:25%;margin-top: 2px">
                        <div id="utrHeaderFilter" class="ui-widget-header ui-corner-top ui-state-default"><h1 style="margin:1px">Filter</h1></div>
                        <div id="utrFilterContent" class="ui-widget-content " style ="height:75%;margin: 0px;overflow: auto">  <br><br><br><br><br><br>fff<br><br><br><br><br><br>filter </div>

                        
                    </div>





                </div> <!--the right side-->

            </div><!-- First Tab-->





        </div>




    </body>
</html>