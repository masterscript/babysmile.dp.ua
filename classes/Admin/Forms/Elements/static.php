<?php

require_once ('PEAR/HTML/QuickForm/static.php');

class Admin_Forms_Elements_static extends HTML_QuickForm_static {
	
	public function __construct($elementName = null, $elementLabel = null, $text = null) {
		
		parent::__construct ( $elementName, $elementLabel, $text );
	
	}
	
	public function toHtml() {
		
		return "<b>{$this->_text}</b>";
		
	}
	
}

?>
