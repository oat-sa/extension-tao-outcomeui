<?php

/*
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 *
 */
	return array(
		'name' => 'taoResults',
		'description' => 'TAO Results extension',
		'additional' => array(
			'version' => '2.3',
			'author' => 'CRP Henri Tudor',
			'extends' => 'tao',	
			'dependances' => array('taoTests'),
			'models' => array('http://www.tao.lu/Ontologies/TAOResult.rdf',
				'http://www.tao.lu/Ontologies/taoFuncACL.rdf'),
			'install' => array(
				'rdf' => array(
						array('ns' => 'http://www.tao.lu/Ontologies/TAOResult.rdf', 'file' => dirname(__FILE__). '/models/ontology/taoresult.rdf'),
						array('ns' => 'http://www.tao.lu/Ontologies/TAOResult.rdf', 'file' => dirname(__FILE__). '/models/ontology/taoresult_alt.rdf'),
				)
			),
			'classLoaderPackages' => array(
				dirname(__FILE__).'/actions/',
				dirname(__FILE__).'/helpers/'
			 )



		)
	);
?>