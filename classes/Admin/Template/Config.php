<?php

/**
 * Работа с конфигурационным файлом
 *
 */
class Admin_Template_Config {
	
	private $cur = false;
	
	private $config_names;
	
	/**
	 * Массив, соответствующий конфигурационному файлу
	 *
	 * @var array
	 */
	private $arrParsedConfig = array();
	
	/**
	 * Конструктор класса.
	 * Принимает название одного или нескольких
	 * файлов конфигурации.
	 * @param string|array $config_name
	 */
	public function __construct($config_name) {
		
		$this->config_names = $config_names = (array)$config_name;
		
		foreach ($config_names as $name) {
			
			if (!file_exists($name))
				throw new Admin_TemplateConfigException('Конфигурационный файл не существует: '.$name);
				
			$this->arrParsedConfig = array_merge($this->arrParsedConfig,$this->parseConfig($name));
			
		}
	
	}
	
	/**
	 * Разбор конфигурационного файла
	 *
	 */
	private function parseConfig($config_name) {
		
		return parse_ini_file($config_name,true);
	
	}
		
	/**
	 * Возвращает конфигурационный файл в виде массива
	 * 
	 * @return array массив параметров конфигурационного файла
	 */
	public function getParsedConfig() {
		
		return $this->arrParsedConfig;
	
	}
	
	/**
	 * Возвращает определенную секцию конфигурационного файла
	 *
	 * @param string $section_name имя секции
	 * @return array массив параметров секции
	 */
	public function getConfigSection($section_name,$section_item=false) {
		
		if (!key_exists($section_name, $this->arrParsedConfig)) {
			throw new Admin_TemplateConfigException ( "Секция $section_name отстутствует в конфигурации '".implode(',',$this->config_names)."'" );
		}
		if ($section_item) {
			return $this->arrParsedConfig [$section_name][$section_item];
		} else {
			return $this->arrParsedConfig [$section_name];
		}
	
	}
	
	/**
	 * Разбивает строку по разделителю; если разделитель отстутствует, то возвращает строку в массив
	 *
	 * @param string $delimiter
	 * @param string $source
	 * @param integer $limit
	 * @return array
	 */
	static public function explode($delimiter, $source) {
		
		if (strpos($source,$delimiter)!==false) {
			return explode ($delimiter,$source);
		} else {
			return array ($source);
		}
	
	}
	
	/**
	 * Проверка на принадлежность элемента к массиву
	 * с учетом модификатора "-" (все кроме) перед элементом массива
	 *
	 * @param string $needle
	 * @param array $haystack
	 * @return bool|integer true - элемент входит в массив, false - не входит, 1 - не входит в перечень исключенных
	 */
	static public function in_array($needle, $haystack) {
		
		$is_alternate = false;
		foreach ($haystack as $value) {
			if (strpos($value,'-') !== false) {
				$is_alternate = 0;
				if ($needle == str_replace('-', '', $value )) {
					return false;
				}
			}
		}
		if ($is_alternate===0) return $is_alternate;
		return in_array($needle,$haystack);
	
	}
	
	/**
	 * Выполняет подстановку плейсхолдеров из массива в строке
	 *
	 * @param string $string
	 * @param array $placeholders
	 */
	static function ph_replace($string, $placeholders) {
		
		if (!$placeholders) return $string;
		foreach ( $placeholders as $key => $value ) {
			$string = str_replace ( '{' . $key . '}', $value, $string );
		}
		return $string;
	
	}
	
	
}

?>
