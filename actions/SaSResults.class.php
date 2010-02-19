<?php
/**
 * SaSResults Controller provide process services
 * 
 * @author Bertrand Chevrier, <taosupport@tudor.lu>
 * @package taoResults
 * @subpackage actions
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 */
class SaSResults extends Results {

    
    
    /**
     * @see Results::__construct()
     */
    public function __construct() {
        $this->setSessionAttribute('currentExtension', 'taoResults');
		tao_helpers_form_GenerisFormFactory::setMode(tao_helpers_form_GenerisFormFactory::MODE_STANDALONE);
		parent::__construct();
    }
    	
		
	/**
     * @see TaoModule::setView()
     */
    public function setView($identifier, $useMetaExtensionView = false) {
		if($useMetaExtensionView){
			$this->setData('includedView', $identifier);
		}
		else{
			$this->setData('includedView', BASE_PATH . '/' . DIR_VIEWS . $GLOBALS['dir_theme'] . $identifier);
		}
		parent::setView('sas.tpl', true);
    }
	
	/**
	 * 
	 * @return 
	 */
	public function createTable(){
		$clazz = $this->getCurrentClass();
		$_SESSION['instances'] = array();
		foreach($clazz->getInstances(true) as $instance){
			$_SESSION['instances'][$instance->uriResource] = $instance->uriResource;
		}
		$this->setView("create_table.tpl");
	}
}
?>