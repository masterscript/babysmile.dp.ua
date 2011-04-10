<?php

class Admin_Forms_Elements_richtext extends Admin_Forms_Elements_Abstract_richtext {
	
	public function __construct ($elementName = null,$elementLabel = null,$attributes = null) {
	
	    parent::__construct($elementName,$elementLabel,$attributes);
	    $this->setConfig(array('elements'=>$elementName));
	    
	}
	
}

?>
