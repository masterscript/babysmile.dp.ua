<?php

/**
 * Добавление фотографии к спецпредложению.
 */
class Admin_Actions_Photo_Common extends Admin_Actions_Abstract {
	
	private $mode;
	
	public function __construct() {
		
	    // вызов конструктора родительского класса
		parent::__construct();
		
		// создание и обработка формы
		$this->objectForm = $this->createObjectForm($this->objectModel);
		
		$this->mode = @$_GET['mode']=='delete'?'delete':'init';
		
		// обработка действия
		$this->process();		
		
	}
	
	public function process() {
		
		switch ($this->objectForm->getFormState()) {
			case 'INIT':
				if ($this->mode=='delete') {
					$this->objectModel->delete();
					Admin_Core::sendLocation('edit/photo',$this->objectModel->getId());
				}
			break;
			case Admin_Core::getActionName():
				$this->objectModel->insert();
				Admin_Core::sendLocation();
			break;
		}
		
	}
	
	/**
	 * @see Admin_IController::getTemplateValue()
	 */
	public function getTemplateValue() {
	    
	    $template_vars = array('photos'=>$this->objectModel->show());
	    return array_merge($this->objectForm->getTemplateValue(),$template_vars);
	    
	}

}
