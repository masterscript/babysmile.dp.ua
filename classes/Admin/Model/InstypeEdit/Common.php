<?php

/**
 * Редактирование типов объявления
 *
 */
class Admin_Model_InstypeEdit_Common extends Admin_Model_Abstract {
    
	public function __construct() {
		
		parent::__construct();
		$this->setDefaultTable('insertion_types');
		if ($this->recordExists('insertion_types',array('id'=>@$_GET['instype_id']))) {
			$this->setId($_GET['instype_id']);
		} else {
			$this->setId(0);
		}
	
	}	
	
}

?>
