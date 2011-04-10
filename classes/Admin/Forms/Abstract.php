<?php

/**
 * 
 * Абстрактный класс для работы с формами
 *
 */
abstract class Admin_Forms_Abstract implements Admin_IController, Admin_IForm {
    
	/**
	 * Объект модели
	 *
	 * @var Admin_Model_Common
	 */
	public $objectModel;
	
	/**
	 * Объект HTML_MetaFormAction
	 *
	 * @var HTML_MetaFormAction
	 */
	protected $objectMetaFormAction;
	
	/**
	 * Объект построителя форм
	 *
	 * @var Admin_Forms_Builder
	 */
	protected $objectFormBuilder;
	
	/**
	 * Состояние формы
	 *
	 * @var string
	 */
	protected $form_state;
	
	/**
	 * HTML код формы
	 *
	 * @var string
	 */
	protected $form_html;
	
	/**
	 * Конструктор класса
	 *
	 */
	public function __construct ($objectModel) {
	    
		// получаем объект для работы с базой данных
	    $this->objectDb = Admin_Core::getObjectDatabase();
	    // получаем необходимый объект модели
		$this->objectModel = $objectModel;
		
		$placeholders = array();
		if ($this->objectModel->getDefaultTable()=='items') {
			$placeholders = array('ELEMENT'=>$this->objectModel->getItem('name'));
		}
		
		// получаем объект построителя форм
		$this->objectFormBuilder = new Admin_Forms_Builder($this->objectModel,$placeholders);
	    
	}
	
	/**
	 * Возвращает объект HTML_MetaFormAction
	 *
	 * @return HTML_MetaFormAction
	 */
	public function processForm () {
        
	    $objectCollection = Admin_Controller_Collection::getInstance();
	    $objectCollection->addObject(new Admin_Forms_Action(Admin_Forms_Action::init()));	    
		return $objectCollection->forms_action;
		
	}
	
	/**
	 * Возвращает массив полей для базы данных
	 *
	 * @return array
	 */
	public function getDbFields () {

		$db_fields = $this->objectMetaFormAction->getDbFields();
		
		// обработка параметра ifempty
		foreach ($db_fields as $table_name=>$fields) {
			
			foreach ($fields as $field_name=>$value) {
				
				if (!empty($value))
					continue;
					
				$ifempty = $this->objectFormBuilder->getFieldParam($table_name,$field_name,'ifempty');
				
				switch ($ifempty) {
					case 'NULL':
						$db_fields[$table_name][$field_name] = null;
						break;
					case 'eliminate':
						unset($db_fields[$table_name][$field_name]);
						break;
					case false:
                        if ($ifempty===false) {
						    break;
                        }
					default:
						$db_fields[$table_name][$field_name] = $ifempty;
				}
				
			}
			
		}
		
		$autoset_fields = _Array::expand(_Array::prefix_to_keys(
			$this->objectModel->getDefaultTable(),
			$this->objectFormBuilder->getAutosetValues()));
		// сливаем массивы
	    foreach ($autoset_fields as $table=>$fields) {
	    	if (isset($db_fields[$table])) {
	    	    $db_fields[$table] = array_merge($db_fields[$table],$fields);
	    	} else {
	    	    $db_fields[$table] = $fields;
	    	}
	    }
	    
		return $db_fields;
	    
	}
	
	/**
	 * Возвращает состояние формы (INIT, имя нажатой кнопки)
	 *
	 * @return string
	 */
	public function getFormState () {
		
		return $this->form_state;
		
	}
	
}

?>