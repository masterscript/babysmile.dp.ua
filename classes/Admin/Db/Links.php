<?php

/**
 * Класс для работы со связями между таблицами в БД
 *
 */
class Admin_Db_Links {
	
	private $links;
	
	/**
	 * Конструктор класса
	 *
	 * @param array $links
	 */
	public function __construct ($links) {
		
		$this->links = $this->set($links);
	
	}
	
	/**
	 * Устанавливает связи для таблиц
	 *
	 * @param array $link
	 */
	private function set ($link) {
	    
		$link_modified = array();
	    foreach ($link['fields'] as $l_key=>$links) {
	        foreach ($links as $f_key=>$field) {
	            list($table,$field) = Admin_Template_Config::explode('::',$field);
	            $link_modified['fields'][$l_key][$f_key][$table] = $field;
	        }
	    }
	    $link_modified['type'] = $link['type'];
        return $link_modified;
	    
	}
	
	private function _getIndex($table,$link) {
		
		$index = _Array::search_by_key($table,$link);
			
		if ($index===false) {
			$index = _Array::search_by_key('>'.$table,$link);
		}
		
		return $index;
		
	}
	
	public function getLinkType($table) {
		
		$link = $this->getLink($table);
		
		if (!isset($link[1][0])) {
			return false;
		}
		
		if ($link[1][0]=='>'.$table)
			return 'O2M';
			
		return 'O2O';
		
	}
	
	/**
	 * Проверяет, является ли текущее поле первым в стеке связей
	 *
	 * @param string $table
	 * @param string $field
	 * @return bool
	 */
	public function isFirst ($table) {
	    
		foreach ($this->links['fields'] as $link) {
	    	if ($this->_getIndex($table,$link)===0) {
	    		return true;
	    	}
		}
		return false;
	    
	}
	
	/**
	 * Возвращает первую связь в стеке
	 *
	 * @return array
	 */
	public function getFirst () {
	    
	    $all_links = $this->getAllLinks();
	    return $all_links[0];
	    
	}
	
	/**
	 * Возвращает поле связи по имени таблицы
	 *
	 * @param string $table
	 * @return string
	 */
	public function getByTable ($table) {
	    
		foreach ($this->links['fields'] as $link) {
			
			$index = $this->_getIndex($table,$link);
				
			if ($index!==false) {
				return isset($link[$index][$table]) ? $link[$index][$table] : $link[$index]['>'.$table];				
			}
			
		}
		return false;
	    
	}
	
	/**
	 * Возвращает связь для таблицы
	 *
	 * @param string $table
	 * @return array|bool
	 */
	public function getLink ($table) {
	    
	    $links_list = array();
	    foreach ($this->links['fields'] as $link) {
			$index = $this->_getIndex($table,$link);
			if ($index!==false) {
				foreach ($link as $value) {
				    $links_list[] = _Array::assoc_to_list($value);
				}
				return $links_list;
			}
		}
	    return false;
	    
	}
	
	/**
	 * Возвращает массив списков связей
	 *
	 * @return array
	 */
	public function getAllLinks () {
	    
	    $all_links = array();
	    foreach ($this->links['fields'] as $links) {
	        foreach ($links as $link) {
	            $all_links[] = _Array::assoc_to_list($link);
	        }
	    }
	    return $all_links;
	    
	}
	
	/**
	 * Возвращает предыдущее поле и таблицу в стеке связей
	 *
	 * @param string $table
	 * @return array|bool
	 */
	public function getPrev ($table) {
	    
		foreach ($this->links['fields'] as $link) {
			$index = $this->_getIndex($table,$link);
			if ($index!==false) {
				if (!isset($link[$index-1])) return false;
				$table = array_keys($link[$index-1]);
				$field = array_values($link[$index-1]);
				return array($table[0],$field[0]);
			}
		}
		return false;
	    
	}
	
}

?>
