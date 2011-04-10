<?php

/**
 * Просмотр фотографий объявления
 *
 */
class Admin_Info_Photos_Common extends Admin_Info_Abstract {
	
	public function __construct() {
		
	    // вызов конструктора родительского класса
		parent::__construct();
				
	}
		
	/**
	 * @see Admin_IController::getTemplateValue()
	 *
	 */
	public function getTemplateValue() {
	    
	    return array('items'=>$this->objectModel->show('insertion_photos',array('insertion_id'=>$this->objectModel->getId())));
	    
	}

}

?>
