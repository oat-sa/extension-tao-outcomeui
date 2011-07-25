<?php
/**
 * This controller provide the actions to manage the user settings
 * 
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * @package taoResults
 * @subpackage action
 *
 */
class taoResults_actions_Settings extends tao_actions_Settings {
	      
        /*
         * get list of classes to be hardified
         */
        protected function getOptimizableClasses(){
                
                $returnValue = array();
                
                $optionsCompile = array(
                        'recursive'             => true,
                        'append'                => true,
                        'createForeigns'        => false,
                        'referencesAllTypes'	=> true,
                        'rmSources'             => true
                );
                
                $optionsDecompile = array(
                        'recursive'             => true,
                        'removeForeigns'        => false				
                );
                
                $returnValue = array(
                        'http://www.tao.lu/Ontologies/TAOResult.rdf#Result' => array(
                                'compile' => $optionsCompile,
                                'decompile' => $optionsDecompile
                        )
                );
                
                return $returnValue;
                
        }
	
}
?>