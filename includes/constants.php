<?php
$todefine = array(
	'TAO_RESULT_CLASS' => 'http://www.tao.lu/Ontologies/TAOResult.rdf#Result',
	'GENERIS_BOOLEAN'		=> 'http://www.tao.lu/Ontologies/generis.rdf#Boolean'
);
foreach($todefine as $constName => $constValue){
	if(!defined($constName)){
		define($constName, $constValue);
	}
}
unset($todefine);
?>