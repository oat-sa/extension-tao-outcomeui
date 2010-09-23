<?php
require_once 'esmet_config.php';

//get the XML log
$xmlDoc = simplexml_load_file('teplax.xml');
$xml = $xmlDoc->asXML();

$conv = new taoEventModelConverter($xml);
$taoEvents = $conv->importFromHAWAI();

//Now we have an XML log compliant with TAO Event Model

$ms = new matchnigScoringToolBox($taoEvents);
//*************Prepare the patterns
$pattern = array();
//patterns['varName'] = "(query)";

$patterns['next1'] = "(type= 'BUTTON') and (id = 'btn_next1')";
$patterns['next2'] = "(type= 'BUTTON') and (id = 'btn_next2')";
$patterns['next3'] = "(type= 'BUTTON') and (id = 'btn_next3')";
$patterns['next4'] = "(type= 'BUTTON') and (id = 'btn_next4')";

//the resetQuery corresponds to reset button
$resetQuery = "(type= 'BUTTON') and (id = 'btn_back')";


//You have to call this method 
$ls = $ms->finaleStateOfEvents_CheckBoxStyle($patterns, $resetQuery);
print_r($ls);


?>
