<?php

abstract class Admin_Forms_Elements_Abstract_ajaxtree extends HTML_QuickForm_element {
	
	protected $init_js = 'tree_move_init.js';
	protected $tree_css_id = 'tree-move';
	protected $element_type = 'ajaxtree';
	protected $php_class = 'Admin_Tree_MoveElement';
    
    /**
     * Объект для работы с деревом элементов
     *
     * @var Admin_Tree_MoveElement_Common
     */
    private $objectTree;
    
	/**
     * Class constructor
     *
     */
    public function __construct ($elementName = null,$elementLabel = null,$attributes = null) {
        
        $this->objectTree = Admin_Controller_Factory::createObject($this->php_class,Admin_Core::getItemConfigName(),Admin_Core::getActionClassName());
        $this->HTML_QuickForm_element($elementName,$elementLabel,$attributes);
        $this->_type = $this->element_type;
        
    }
	
	/**
	 * @see HTML_Common::toHtml()
	 *
	 * @return string
	 */
	public function toHtml() {
	    
	    $html = '
	    	<script type="text/javascript" src="'.SITE_SUBDIR.'/js/'.$this->init_js.'"></script>
			<div id="'.$this->tree_css_id.'" class="tree">
            	<ul>
            		<li class="root">
            			<ul>
            				'.$this->objectTree->initTree().'
            			</ul>
            		</li>
            	</ul>
            </div>';

	    // create hidden field
        $hidden = new HTML_QuickForm_hidden('move_item_id');
        $hidden->updateAttributes('id="move_item_id" meta:validator="'.$this->getAttribute('meta:validator').'" meta:dynamic');
        
        return $html.$hidden->toHtml();
	    
	}

	
}

?>
