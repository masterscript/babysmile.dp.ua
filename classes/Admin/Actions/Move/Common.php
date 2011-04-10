<?php

/**
 * Общий класс перемещения элемента
 *
 */
class Admin_Actions_Move_Common extends Admin_Actions_Abstract {
	
	public function __construct() {
		
	    // вызов конструктора родительского класса
		parent::__construct();
		
		// создание и обработка формы
		$this->objectForm = $this->createObjectForm($this->objectModel);
		
		// обработка действия
		$this->process();		
		
	}
	
	public function process() {
		
		switch ($this->objectForm->getFormState()) {
			case 'INIT':
			break;
			case Admin_Core::getActionName():
			    $this->objectModel->move();
				Admin_Core::sendLocation('view',$this->objectModel->getId());
			break;
		}
		
	}
	
	/**
	 * @see Admin_IController::getTemplateValue()
	 *
	 */
	public function getTemplateValue() {
	    
	    return $this->objectForm->getTemplateValue();
	    
	}


}

?>