<?php

abstract class Admin_Actions_Abstract implements Admin_IActions, Admin_IController {
	
	/**
	 * Объект формы
	 *
	 * @var Admin_Forms_Common
	 */
    protected $objectForm;
	
    /**
     * Объект модели
     *
     * @var Admin_Model_Common
     */
	protected $objectModel;
	
	/**
	 * Конструктор класса
	 *
	 * @param Admin_Forms_Common $objectForm
	 */
	public function __construct() {
		
		$this->objectModel = $this->createObjectModel();		
		
	}
	
	/**
	 * Создает объект модели запрошенного элемента
	 *
	 * @return Admin_Model_Common
	 */
	private function createObjectModel () {
		
		try {
			$config_name = Admin_Core::getOverridenConfig(true);
		} catch (Admin_FormBuilderException $e) {
			$config_name = Admin_Core::getItemConfigName();
		}
		return Admin_Controller_Factory::createObject('Admin_Model',$config_name,Admin_Core::getActionClassName());
		
	}
	
	/**
	 * Создает объект формы запрошенного элемента
	 * 
	 * @param Admin_Model_Common $objectModel
	 * @return Admin_Forms_Common
	 */
	protected function createObjectForm ($objectModel) {
	    
	    return Admin_Controller_Factory::createObject('Admin_Forms',Admin_Core::getActionClassName(),Admin_Core::getItemConfigName(),$objectModel);
	    
	}
	
}

?>
