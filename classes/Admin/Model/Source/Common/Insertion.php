<?php

class Admin_Model_Source_Common_Insertion extends Admin_Model_Source_Common {
	   
	public function source_region_list () {
		
		return $this->showForSelect('regions',array('name'),false,'name ASC');
		
	}
	
	public function source_instype_list () {
		
		return $this->showForSelect('insertion_types',array('name'),false,'name ASC');
		
	}
	
}

?>