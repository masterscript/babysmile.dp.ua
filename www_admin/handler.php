<?php
/**
 * Перехватчик запросов
 */

include_once '../config.php';

/**
 * Автозагрузка классов
 * @todo проверка существования файла с классом
 * @param string $class_name имя класса с разделителями "_"
 */
function __autoload($class_name) {
				
	if (strpos($class_name,'_')===0) {
		// подключается библиотека функций
		$class_name = 'Library'.$class_name;
	}
	$path = str_replace('_','/',$class_name).'.php';
	include_once ($path);
	
}

$core = Admin_Core::getInstance();

?>