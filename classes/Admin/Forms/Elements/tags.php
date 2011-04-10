<?php

class Admin_Forms_Elements_tags extends HTML_QuickForm_element {
	
	public function __construct ($elementName = null,$elementLabel = null,$attributes = null) {
	
	    parent::__construct($elementName,$elementLabel,$attributes);
	    
	}
	
	public function toHtml() {
		
		$tags = Admin_Core::getObjectDatabase()->selectCol('SELECT name FROM tags t JOIN tags_items ON tag_id = t.id WHERE item_id = ?d',
			Admin_Controller_Collection::getInstance()->model->getId());
			
		$tagstr = implode(', ',$tags);
		
		return '
			<link rel="stylesheet" href="/js/jquery-autocomplete/jquery.autocomplete.css" />
			<script type="text/javascript" src="/js/jquery-autocomplete/jquery.autocomplete.js"></script>
			<script type="text/javascript" src="/js/tags.js"></script>
			<div id="tags">	
				<textarea name="tags">'.$tagstr.'</textarea>
				Введи теги, разделяя их запятыми.
			</div>
		';
		
	}
	
}
