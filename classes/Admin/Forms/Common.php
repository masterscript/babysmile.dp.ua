<?php

class Admin_Forms_Common extends Admin_Forms_Abstract {
	
	public function __construct($objectModel) {
		
	    parent::__construct($objectModel);
	    // инициализация обработчиков формы
		$this->objectMetaFormAction = $this->processForm();
		
		// запускаем обработку формы
		$this->form_state = $this->objectMetaFormAction->process($this->objectFormBuilder->getCommonValidators());		
	    
	    // построение формы
	    $this->form_html = $this->objectFormBuilder->build();
	    
	    // передаем связи для модели
	    $this->objectModel->setLink($this->objectFormBuilder->getTableLink());
	    
	    // передаем данные для модели
	    $this->objectModel->setValues($this->getDbFields());
	    
//	    Admin_Errors::prnt_array($_POST);
	    
    	// восстановление значений в полях для режима редактирования
	    if (empty($_POST)) {
	    	$_POST = $this->objectModel->getFieldsData($this->objectFormBuilder);
	    }
	    
	    // передаем список постобработчиков для модели
	    $this->objectModel->setPosthandlers($this->objectFormBuilder->getPosthandlers());
	    
	}
	
	/**
	 * Возвращает массив переменных для передачи в шаблон
	 *
	 * @return array
	 */
	public function getTemplateValue () {
		
	    return array(
	    	'form_html'=>$this->form_html,
	    	'errors'=>$this->objectMetaFormAction->getErrors()
	    );
		
	}
	
}

?>