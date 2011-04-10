<?php

/**
 * Библиотека работы с массивами
 *
 */
class _Array {
    
	/**
     * Разворачивает одномерный ассоциативный массив в подмассивы
     * по заданному разделителю в ключах
     *
     * @param array $array
     * @param string $delimiter
     */
    static function expand ($array,$delimiter='::') {
    	
		$array_modified = array();		
		foreach ($array as $name=>$value) {
			// преобразовываем имена в radiobox
			$matches = array();
			if (preg_match('#(.+)\[(.+)\]#',$name,$matches)) {
				$name = $matches[1];
			}
			if (strpos($name,$delimiter)!==false) {
				list($table,$field) = explode($delimiter,$name);
				$array_modified[$table][$field] = $value;
			} else {
				$array_modified[][$name] = $value;
			}
		}
		return $array_modified;
    	
    }
    
	/**
     * Разворачивает одномерный ассоциативный массив в подмассивы
     * по заданному разделителю в ключах
     *
     * @param array $array
     * @param string $delimiter
     */
    static function collapse ($array,$delimiter='::') {
    	
		$array_modified = array();		
		foreach ($array as $name=>$sub_array) {
			// преобразовываем имена в radiobox
			/*$matches = array();
			if (preg_match('#(.+)\[(.+)\]#',$name,$matches)) {
				$name = $matches[1];
			}*/
			foreach ($sub_array as $key=>$value) {
				$array_modified[$name.$delimiter.$key] = $value;
			}
		}
		return $array_modified;
    	
    }
    
	/**
     * Добавляет префикс к ключам массивам, если они не содержат заданных символов
     *
     * @param string $prefix
     * @param array $array
     * @param string $delimiter
     * @return array
     */
    static function prefix_to_keys ($prefix,$array,$delimiter='::') {
    	
    	$array_modified = array();
    	if (!count($array) || !$array) {
            return array();
        }
    	foreach ($array as $key=>$value) {
    		if (strpos($key,$delimiter)===false) {
    			$array_modified[$prefix.$delimiter.$key] = $value;
    		} else {
                $array_modified[$key] = $value;
            }
    	}
    	return $array_modified;
    	
    }
    
    /**
     * Выполняет поиск индекса в массиве по ключу подмассива
     *
     * @param string $needle
     * @param array $stack
     * @return integer|bool
     */
    static function search_by_key ($needle,$stack) {
        
        foreach ($stack as $key=>$values) {
            if (array_key_exists($needle,$values)) {
                return $key;
            }
        }
        return false;
        
    }
    
    /**
     * Преобразует строку ассоциативного массива в список
     *
     * @param array $array
     * @return array
     */
    static function assoc_to_list ($array) {
        
        $key = array_keys($array);
		$value = array_values($array);
	    return array($key[0],$value[0]);
        
    }
    
}
