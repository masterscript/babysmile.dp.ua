<?php

/**
 * Общий класс редактирования элемента
 *
 */
class Admin_Actions_Edit_Common extends Admin_Actions_Edit_Abstract {
	
	public function __construct() {
		
	    // вызов конструктора родительского класса
		parent::__construct();
		
		// создание и обработка формы
		$this->objectForm = $this->createObjectForm($this->objectModel);
		
		// обработка действия
		$this->process();		
		
	}
	
	public function process() {
		
	    $objectConfig = Admin_Core::getObjectGlobalConfig();
		switch ($this->objectForm->getFormState()) {
			case 'INIT':
			break;
			case Admin_Core::getActionName():
        		// если введен пароль пользователя
			    if ($this->objectModel->getFormValue('users::pass')!==false) {
			        $this->objectModel->setDbField('pass',md5($this->objectModel->getFormValue('users::pass')),'users');
			    }		        
				$this->objectForm->objectModel->update();
        		// удаление топового изображения
				if (isset($_POST['ch_delete_file'])) {
				    $upload_dir = $objectConfig->getConfigSection('FOLDERS');
				    $upload_dir = $_SERVER['DOCUMENT_ROOT'].$upload_dir['top_images'];
				    @unlink($upload_dir.$this->objectModel->getItem('id').'/'.$this->objectModel->getItem('filename','top_images'));
				    // удаляем запись
				    $this->objectModel->deleteRecord('top_images');
				}
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