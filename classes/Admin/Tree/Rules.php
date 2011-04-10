<?php

class Admin_Tree_Rules implements ArrayAccess {
	
	const SECTION_TREE_RULES = 'TREE_RULES';
	
	/**
	 * Объект для работы с базой данных
	 *
	 * @var DbSimple_MySQL
	 */
	private $objectDb;
	
	/**
	 * id уровня
	 *
	 * @var integer
	 */
	private $level_id;
	
	/**
	 * Правила уровня по умолчанию
	 *
	 * @var array
	 */
	private $default_rules;
	
	/**
	 * Правила текущего уровня
	 *
	 * @var array
	 */
	public static $current_rules;
	
	/**
	 * Имя файла конфигурации уровня
	 *
	 * @var string
	 */
	private $template_name;
	
	/**
	 * Конструктор класса
	 * 
	 * @param array $default_rules массив правил по умолчанию
	 */
	public function __construct($default_rules) {
	
		$this->objectDb = Admin_Core::getObjectDatabase();
		$this->default_rules = $default_rules;
		
	}
	
	private function getLinks($str_link) {
		
	   $link = array_map('trim',explode(';',$str_link));
	   $arr_links = array();
       // анализ связей
       foreach ($link as $param_value) {
	       // другой разделитель, поскольку ":" используется для других целей
	       list($param_name,$param_value) = explode('=',trim($param_value));
	       if ($param_name=='fields') {
	           $param_value_array = array();
	           foreach (Admin_Template_Config::explode(' ',$param_value) as $link) {
	               $param_value_array[] = explode('-',$link);
	           }
	           $param_value = $param_value_array;
	       }
	       $arr_links[$param_name] = $param_value;
       }
       
       return $arr_links;
		
	}
	
	/**
	 * Устанавливает id уровня, по которому будут возвращены настройки
	 *
	 * @param integer $id
	 */
	public function setLevelId ($level_id=0) {
		
		if ($this->level_id == $level_id) return ; // cashing result
		$this->level_id = $level_id;
		$this->template_name = $this->objectDb->selectRow('SELECT `type`,template FROM ?_items WHERE id = ?',$this->level_id);
		if (count($this->template_name)==0) {
			self::$current_rules = $this->default_rules;
			return ;
		}
		$this->template_name = !empty($this->template_name['template']) ? $this->template_name['template'] : $this->template_name['type'];

		// пытаемся получить конфигурационный файл
		try {
			$objectItemConfig = new Admin_Template_Config(SYSTEM_PATH.Admin_Core::PATH_TO_CONFIGS.Admin_Core::PATH_TO_INTERFACE_CONFIGS.$this->template_name.'.conf');
			self::$current_rules = $objectItemConfig->getConfigSection(self::SECTION_TREE_RULES);
		} catch (Admin_TemplateConfigException $e) {
			// код восстановления - использование массива правил по умолчанию
			self::$current_rules = $this->default_rules;
			Admin_Errors::add($e->getMessage(),$e);
		}
		// восстанавливаем недостающие значения из дефолтных
		foreach ($this->default_rules as $key=>$value) {
			if (!isset(self::$current_rules[$key])) {
				self::$current_rules[$key] = $value;
			}
		}
		
	}
	
	public function offsetExists($offset) {
		
		return isset(self::$current_rules[$offset]);
	
	}
	
	public function offsetGet($offset) {
		
	    // для параметра sort_field производим разбиение в массив по запятой
	    if ($offset=='sort_field') {
	        return explode(',',self::$current_rules[$offset]);
	    }
		if ($offset=='link') {
	    	return $this->getLinks(self::$current_rules['link']);
	    }
		return self::$current_rules[$offset]; 
	
	}
	
	// не поддерживается
	public function offsetSet($offset, $value) { }
	
	// не поддерживается
	public function offsetUnset($offset) { }
	
}

?>
