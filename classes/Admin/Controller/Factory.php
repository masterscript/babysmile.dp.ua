<?php

/**
 * Класс-фабрика для создания объектов
 *
 */
class Admin_Controller_Factory {
    
    const COMMON_CLASS_NAME = 'Common';
	
	/**
	 * Создание нового объекта
	 *
	 * @param string $class_name имя класса задается в конфигурационном файле
	 * @param string $item_type тип объекта
	 * @param string $action_class класс действия
	 * @return object объект запрошенного класса
	 */
	static function createObject ($class_name,$item_type=NULL,$action_class=NULL,$args=array()) {
		
		$itemTypeParts = explode('_',$item_type);
		$item_type = '';
		foreach ($itemTypeParts as $part) {
			$item_type .= ucfirst($part);
		}
		
		// если тип объекта то создаем объект общего класса
	    if (!$item_type) {
		    $item_type = self::COMMON_CLASS_NAME;
		}
		// если имя действия не передано, то создаем объект общего класса
		$action_class_name = $action_class;
		if (!$action_class) {
		    $action_class_name = self::COMMON_CLASS_NAME;
		}
		
		// проверяем существование классов 
		if ($action_class===NULL) {
			if (!Admin_Core::checkClassExists($class_name.'_'.$item_type)) {
				$item_type = self::COMMON_CLASS_NAME;
			}
		} elseif (!Admin_Core::checkClassExists($class_name.'_'.$action_class_name.'_'.$item_type)) {
            if (!Admin_Core::checkClassExists($class_name.'_'.$action_class_name.'_'.self::COMMON_CLASS_NAME)) {
			    $action_class_name = self::COMMON_CLASS_NAME;
            } else {
                $item_type = self::COMMON_CLASS_NAME;
            }
		}
		
		// формируем постфикс к имени создаваемого класса
		if ($item_type==self::COMMON_CLASS_NAME && $action_class_name==self::COMMON_CLASS_NAME) {
			$class_postfix = self::COMMON_CLASS_NAME;
		} elseif ($action_class!==NULL) {
			$class_postfix = $action_class_name.'_'.$item_type;
			if (!Admin_Core::checkClassExists($class_name.'_'.$class_postfix)) {
				$class_postfix = $action_class_name;
			}
		} else {
			$class_postfix = $item_type;
		}
		
	    $created_class = $class_name.'_'.$class_postfix;
	    if (!Admin_Core::checkClassExists($created_class)) {
	    	throw new Admin_CoreException('Класс '.$created_class.' не существует');
	    }
	    /*echo $created_class;
	    echo '<hr>';*/
	    
	    $object = new $created_class($args);
	    
	    // получаем ссылку на объект-коллекцию 
	    $objectCollection = Admin_Controller_Collection::getInstance();
	    
	    // добавляем объект в коллекцию
	    $objectCollection->addObject($object);
	    
		return $object;
		
	}
	
}

?>
