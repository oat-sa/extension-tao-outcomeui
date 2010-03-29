<?php
/**
 *
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 *
 */

include_once ROOT_PATH . '/tao/includes/constants.php';

$todefine = array(
	'RESULT_ONTOLOGY'		=> 'http://www.tao.lu/Ontologies/TAOResult.rdf',
	'ITEM_ONTOLOGY'			=> 'http://www.tao.lu/Ontologies/TAOItem.rdf',
	'GROUP_ONTOLOGY'		=> 'http://www.tao.lu/Ontologies/TAOGroup.rdf',
	'TEST_ONTOLOGY'			=> 'http://www.tao.lu/Ontologies/TAOTest.rdf',
	'SUBJECT_ONTOLOGY'		=> 'http://www.tao.lu/Ontologies/TAOSubject.rdf'
);
foreach($todefine as $constName => $constValue){
	if(!defined($constName)){
		define($constName, $constValue);
	}
}
unset($todefine);
?>