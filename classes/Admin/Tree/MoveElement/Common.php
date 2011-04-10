<?php

class Admin_Tree_MoveElement_Common extends Admin_Tree_Abstract {
       
	/**
	 * Конструктор класса
	 *
	 */
    public function __construct() {
		
        parent::__construct();
        
	    try {
	    	$langs_object = Admin_Controller_Collection::getInstance()->langs;
        } catch (Admin_CoreException $e) {
	    	$langs_object = new Admin_Langs_Common();
	    }
	    $this->root_id = $langs_object->getRootId();
	    
	    if (!$_SERVER['HTTP_ACCEPT']=='application/html+ajax') {
	        $this->ajax_script = SITE_SUBDIR.'/ajax/tree_move_load?exclude_id='.$this->getId();
	    } else {
	        $this->ajax_script = SITE_SUBDIR.'/ajax/tree_move_load?exclude_id='.$this->getId();
	    }
	    $this->requested_node_id = $this->getItem('pid');
	    // поля для выбора
	    $this->select_fields = array('id','pid');
	    
	    $objectConfig = new Admin_Template_Config(SYSTEM_PATH.Admin_Core::PATH_TO_CONFIGS.Admin_Core::BACKEND_MAIN_CONFIG);
		$this->default_tree_rules = $objectConfig->getConfigSection('TREE_RULES');
	    // объект правил уровня
	    $this->tree_rules = $this->setObjectTreeRules(new Admin_Tree_Rules($this->default_tree_rules));
	    $this->setLevelCaption(array('alias'=>$this->default_tree_rules['caption_alias'],'sql'=>$this->default_tree_rules['caption_sql']));
	    
	}
	
	protected function setObjectTreeRules (ArrayAccess $objectTreeRules) {
		
		return $objectTreeRules;
		
	}
	
	public function getTemplateValue () {
		
	    if (!defined('JQUERY_LIBRARY_LOADED')) define('JQUERY_LIBRARY_LOADED', true);
	    return array (
	    	'main_tree'=>$this->initTree()
	    );
		
	}
	
}

?>
