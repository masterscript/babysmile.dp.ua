<?php

/**
 * Класс панели действий
 *
 */
class Admin_Actions_Panel_Common extends Admin_Actions_Panel {
	
	/**
	 * Объект для работы с базой данных
	 *
	 * @var Admin_Db_Main
	 */
	private $objectDb;
	
	/**
	 * Конструктор класса
	 *
	 */
	public function __construct() {
		
		parent::__construct();
		
		$this->objectDb = Admin_Core::getObjectDatabase();
		$this->setItemId();
		$this->setActionsList();
		$this->setCommonConfig();
		
	}
	
	protected function redefiningNames ($action_ident,$action_name) {
		
		if ($action_ident=='add_content') {
			if ($this->objectDb->selectCell('SELECT id FROM ?_content WHERE id = ?',$this->item_id))
				return 'Редактировать контент'; 
		}
		return $action_name;
		
	}
		
	/**
	 * Возвращает переменные шаблона
	 *
	 * @return array
	 */
	public function getTemplateValue () {
		
		return array(
			'actions'=>$this->actions_list
		);
		
	}
	
}
