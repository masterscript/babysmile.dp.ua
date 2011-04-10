<?php

class Admin_Tree_Common extends Admin_Tree_Abstract {
       
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
	    
	    $this->ajax_script = SITE_SUBDIR.'/ajax/tree_load?exclude_id=0';
//        $this->requested_node_id = @$_GET['id'];
	    $this->requested_node_id = Admin_Core::getItemId();
	    
	    // поля для выбора 
	    $this->select_fields = array('?_items.id','?_items.pid');
	    
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
	    	'main_tree'=>$this->initTree($this->root_id)
	    );
		
	}
	
}
