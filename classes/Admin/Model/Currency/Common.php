<?php

/**
 * Класс, реализующий добавление контента к странице
 *
 */
class Admin_Model_Currency_Common extends Admin_Model_Abstract {
    
	public function __construct() {
		
		parent::__construct();
		$this->setDefaultTable('currency');
		if ($this->recordExists('currency',array('id'=>@$_GET['currency_id']))) {
			$this->setId($_GET['currency_id']);
		} else {
			$this->setId(0);
		}
	
	}
	
	/**
	 * @see Admin_Model_Abstract::insert()
	 *
	 */
	public function insert () {

		if ($this->getFormValue('default')==1) {
			$this->updateField('currency','default',0,array(1=>1));
		}
	    parent::insert();	    
	    		
 	}

	
}

?>
