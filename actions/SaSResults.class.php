<?php
/**
 * SaSResults Controller provide process services on results
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
		parent::__construct();
    }
    
    	
    
	/**
	 * @see TaoModule::setView()
	 * @param string $identifier the view name
	 * @param boolean $useMetaExtensionView use a view from the parent extention
	 * @return mixed 
	 */
    public function setView($identifier, $useMetaExtensionView = false) {
		if(tao_helpers_Request::isAjax()){
			return parent::setView($identifier, $useMetaExtensionView);
		}
    	if($useMetaExtensionView){
			$this->setData('includedView', $identifier);
		}
		else{
			$this->setData('includedView', BASE_PATH . '/' . DIR_VIEWS . $GLOBALS['dir_theme'] . $identifier);
		}
		return parent::setView('sas.tpl', true);
    }
	
	/**
     * overrided to prevent exception: 
     * if no class is selected, the root class is returned 
     * @see TaoModule::getCurrentClass()
     * @return core_kernel_class_Class
     */
    protected function getCurrentClass() {
        if($this->hasRequestParameter('classUri')){
        	return parent::getCurrentClass();
        }
		return $this->getRootClass();
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