<?php

class Admin_Actions_Regions_Common extends Admin_Actions_Abstract {
	
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
					$this->objectModel->deleteRecord();
					Admin_Core::sendLocation('global/regions?id='.$_GET['id']);
				}
			break;
			case Admin_Core::getActionName():
				$this->objectModel->insert();
				Admin_Core::sendLocation('global/regions?id='.$_GET['id']);
			break;
		}
		
	}
	
	/**
	 * @see Admin_IController::getTemplateValue()
	 *
	 */
	public function getTemplateValue() {
	    
		$template_vars = array('items'=>$this->objectModel->show(false,array(),array('*'),array('name','ASC')));
	    return array_merge($this->objectForm->getTemplateValue(),$template_vars);
	    
	}

}

?>
