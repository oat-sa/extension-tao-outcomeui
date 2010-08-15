<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

 


$revType = urlencode('revFinal');//reviewer revFinal
$revIdCurrent=urlencode('rev2');
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
