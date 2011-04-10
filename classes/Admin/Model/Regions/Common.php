<?php

class Admin_Model_Regions_Common extends Admin_Model_Abstract {
    
	public function __construct() {
		
		parent::__construct();
		$this->setDefaultTable('regions');
		if ($this->recordExists('regions',array('id'=>@$_GET['region_id']))) {
			$this->setId($_GET['region_id']);
		} else {
			$this->setId(0);
		}
	
	}	
	
}

?>
