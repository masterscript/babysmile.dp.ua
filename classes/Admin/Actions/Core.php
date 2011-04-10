<?php

/**
 * Работа с конфигурацией действий в ядре
 *
 */
class Admin_Actions_Core extends Admin_Actions_Panel {
	
	public function __construct () {
	    
		parent::__construct();
		
	    $this->setItemId();
		$this->setActionsList();
		$this->setCommonConfig();
	    
	}
	
	/**
	 * @see Admin_Actions_Panel::redefiningNames()
	 *
	 * @param string $action_ident
	 * @param string $action_name
	 */
	protected function redefiningNames($action_ident, $action_name) {
	}
	
	/**
	 * @see Admin_IController::getTemplateValue()
	 *
	 */
	public function getTemplateValue() {
	}

	
	/**
	 * Выполняет проверку доступа к действию для текущего элемента
	 *
	 * @return bool
	 */
	public function check () {
	    
		return $this->getAcl()->isAllowed(
				Zend_Auth::getInstance()->hasIdentity()?Admin_Core::getAuth()->login:'guest',
				Admin_Core::getActionName()
			);
//	    return in_array(Admin_Core::getActionName(),$this->getActionsIdent());
	    
	}
	
	/**
	 * Возвращает группу для действия, если действие принадлежит только к одной группе
	 *
	 * @param string $action_ident
	 * @return array|bool
	 */
	public function getActionGroup($action_ident) {
	    
	    $groups = parent::getActionGroup($action_ident);
	    $count_groups = count($groups);
	     
	    if ($count_groups>1 || $count_groups==0) return false;
	    
	    return $groups[0]; 
	    
	}

	
}
