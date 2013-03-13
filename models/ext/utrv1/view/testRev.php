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
<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

 


$revType = urlencode('reviewer');//reviewer revFinal
$revIdCurrent=urlencode('revY2');
$revTestId= urlencode('test1');
$revSubjectId= urlencode('younes');
$revItemId=urlencode('item1');
//revService.php?revType=revFinal&revIdCurrent=younes cococ&revTestId=http://localhost/middleware/tao4.rdf#i1261572267020194300&revSubjectId=http://localhost/middleware/tao4.rdf#i1274434222052333200&revItemId=http://localhost/middleware/tao4.rdf#i1274434065093789300y"

$link = 'revService.php?'.'revType='.$revType.'&revIdCurrent='.$revIdCurrent.'&revTestId='.$revTestId.'&revSubjectId='.$revSubjectId.'&revItemId='.$revItemId;

echo '<a href ="'.$link.'">rev</a>';
echo '<br>';
$linkReport = 'revReport.php?'.'revType='.$revType.'&revIdCurrent='.$revIdCurrent.'&revTestId='.$revTestId.'&revSubjectId='.$revSubjectId.'&revItemId='.$revItemId;

echo '<a href ="'.$linkReport.'">rev Report</a>';



?>
