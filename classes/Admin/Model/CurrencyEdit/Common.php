<?php

/**
 * Редактирование валюты
 *
 */
class Admin_Model_CurrencyEdit_Common extends Admin_Model_Abstract {
    
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
	public function update () {

		if ($this->getFormValue('default')==1) {
			$this->updateField('currency','default',0,array(1=>1));
		}
	    parent::update();	    
		
 	}

	
}

?>
