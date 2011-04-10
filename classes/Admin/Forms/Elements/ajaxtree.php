<?php

require_once ('classes/Admin/Forms/Elements/Abstract/ajaxtree.php');

class Admin_Forms_Elements_ajaxtree extends Admin_Forms_Elements_Abstract_ajaxtree {
	
	public function __construct($elementName = null, $elementLabel = null,$attributes = null) {
	    
	    parent::__construct($elementName,$elementLabel,$attributes);
	    
	}
	
}

?>
