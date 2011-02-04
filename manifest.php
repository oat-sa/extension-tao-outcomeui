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
			'version' => '2.0',
			'author' => 'CRP Henri Tudor',
			'dependances' => array('tao'),
			'models' => 'http://www.tao.lu/Ontologies/TAOResult.rdf',
			'install' => array( 
				'rdf' => dirname(__FILE__). '/models/ontology/taoresult.rdf'
			),
			'classLoaderPackages' => array( 
				dirname(__FILE__).'/actions/',
				dirname(__FILE__).'/helpers/'
			 )

				
			
		)
	);
?>