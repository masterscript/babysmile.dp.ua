<?php

/**
 * Класс для переключения языковых версий
 * в управляемом системой администрирования сайте.
 */
class Admin_Langs_Common implements Admin_IController {
	
	private $default;
	private $current;
	private $all = array();
	
	private $root_id;
	
	public function __construct() {
        
		$config = Admin_Core::getObjectGlobalConfig();
		// язык по умолчанию
		$this->default = $config->getConfigSection('LANGUAGES','default');
		// все языки
		if ($this->default) {
			foreach (array_keys($config->getConfigSection('LANGUAGES')) as $lang)
				if ($lang!='default')
					$this->all[] = trim($lang);
		}
		
		// проверка на присутствие мультиязычности
		if (!count($this->all)) {
			$this->root_id = 1;
			return ;
		}
		
		session_start();
		
		// определение текущего языка
		$this->current = $this->default;
		if (isset($_SESSION['front']['lang'])) {
			$lang = $_SESSION['front']['lang'];
			if (in_array($lang,$this->all))
				$this->current = $lang;
		}		
		
		if (!$this->default) $this->root_id = 1;
		else $this->setRootId($this->current);

		$this->init();
		
	}
	
	private function init() {
		
		if (isset($_GET['front_lang'])) {
			if ($this->is_valid($_GET['front_lang'])) {
				$_SESSION['front']['lang'] = $_GET['front_lang'];
				$this->setRootId($_GET['front_lang']);
				Admin_Core::sendLocation('view',$this->root_id);
			}
		}
		
	}
	
	private function setRootId($lang) {
		
		// определение id корневого элемента
		$this->root_id = Admin_Core::getObjectDatabase()->selectCell('SELECT id FROM ?_items WHERE url = ?',$lang);
		if (empty($this->root_id)) {
			unset($_SESSION['front']['lang']);
			throw new Admin_CoreException("Невозможно определить идентификатор корня дерева для языковой версии: '$lang'");
		}
		
	}
	
	private function is_valid($lang) {
		
		return in_array($lang,$this->all);
		
	}
	
	/**
	 * @return integer
	 */
	public function getRootId() {
		
		return $this->root_id;
		
	}
	
	/**
	 * @return array
	 */
	public function getAll() {
		
		return $this->all;
		
	}
	
	/**
	 * @return string
	 */
	public function getCurrent() {
		
		return $this->current;
		
	}


	/**
	 * @see Admin_IController::getTemplateValue()
	 */
	public function getTemplateValue() {
		
		if (count($this->all)<=1) return array();
		
		// формируем массив для передачи в шаблон
		$langs = array();		
		foreach ($this->all as $key=>$lang) {
			$langs[$key]['name'] = $lang;
			if ($lang==$this->current)
				$langs[$key]['current'] = 1;
		}
		
		return array('data'=>$langs);
		
	}
	
}
