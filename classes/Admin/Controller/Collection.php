<?php

/**
 * Класс коллекции текущих объектов всех
 * задействованных контроллеров на странице
 *
 */
class Admin_Controller_Collection {
	
	/**
     * Экземляр класса Admin_Controller_Collection
     *
     * @var Admin_Controller_Collection
     */
	static private $instance = NULL;
	
	/**
	 * Массив объектов
	 *
	 * @var array
	 */
	private $objects = array();
	
	private $reflection;
	
	private function __construct() {
		
	}
	
	/**
	 * Возвращает экземпляр класса Admin_Controller_Collection
	 *
	 * @return Admin_Controller_Collection
	 */
	static public function getInstance () {
	    
	    if (self::$instance == NULL) {
	        self::$instance = new Admin_Controller_Collection();
	    }
	    
	    return self::$instance;
	    
	}
	
	/**
	 * Добавляет объект в коллекцию
	 *
	 * @param object $object
	 */
	public function addObject ($object) {
		
		$class_name = get_class($object);
		$this->objects[$class_name] = $object;
		$this->reflection[$class_name] = new ReflectionClass($class_name);
		
	}
	
	/**
	 * Возвращает запрошенный объект по уникальной части имени его класса ()
	 *
	 * @param string $name
	 * @return object
	 */
	public function __get($name) {
		
		foreach ($this->objects as $class_name=>$object) {
			if (preg_match('#^Admin_'.$name.'[_]{0,1}[_a-zA-Z]*?$#i',$class_name)) {
				return $object;
			}
		}
		throw new Admin_CoreException('Запрошен несуществующий объект: '.$name);
		
	}

}

?>