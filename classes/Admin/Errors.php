<?php


class Admin_CoreException extends Exception  { }
	class Admin_UserException extends Admin_CoreException { }
	class Admin_TemplateConfigException extends Admin_CoreException  { }
	class Admin_TreeException extends Admin_CoreException { }
	class Admin_DbException extends Admin_CoreException { }
		class Admin_ModelException extends Admin_DbException { }
	class Admin_FormsException extends Admin_CoreException { }
		class Admin_FormBuilderException extends Admin_FormsException { }
	class Admin_ActionsException extends Admin_CoreException { }
	    class Admin_ActionsPanelException extends Admin_ActionsException { }
	class Admin_FileException extends Admin_CoreException { }
	class Admin_ImagesException extends Admin_CoreException { }
	class FormException extends Exception {
		
		private $field_name;
		
		private $field_index; // если поле находится в массиве
		
		public function __construct($message,$field_name=false,$field_index=0) {
			
			$this->message = $message;
			$this->field_name = $field_name;
			$this->field_index = $field_index;
			
		}
		
		public function getFieldIndex () { return $this->field_index; }

		public function getFieldName () { return $this->field_name; }
		
	}
/**
 * Логгирование ошибок ядра
 *
 */
final class Admin_Errors {
	
	private static $instance = NULL;
	
	private static $errors = array();
	
	private function __construct () {
		
	}
	
	private function __clone() {
				
	}
	
	public static function getInstance () {
		
		if (self::$instance == NULL) {
			$instance = new Errors();
		}
		return $instance;
		
	}
	
	/**
	 * Добавление ошибки в массив
	 *
	 * @param string $message
	 * @param Exception $exception_inst
	 */
	public static function add ($message,$exception_inst=false) {
		
		if ($exception_inst) {
			$message .= ' Line: '.$exception_inst->getLine().' File: '.$exception_inst->getFile();
		}
		self::$errors[] = $message;
		
	}
	
	/**
	 * Возвращает массив накопленных ошибок
	 *
	 * @return array
	 */
	public static function get () {
		
		return self::$errors;
		
	}
	
	/**
	 * Вывод ошибок
	 *
	 * @param Exceptions $exception_inst
	 */
	public static function prnt ($exception_inst,$return=false) {
		
		ob_start();
		echo "<pre>";
		print_r(self::get());
		echo "<hr>";
		echo "<h3>Trace:</h3>";
		print_r($exception_inst->getTrace());
		echo "</pre>";
		
		if (!$return) {
			ob_flush();
		} else {
			return ob_get_clean();
		}
		
	}
	
	/**
	 * Выводит массив на экран
	 *
	 * @param array $array
	 */
	public static function prnt_array ($array) {
		
		echo "<pre>";
		print_r($array);
		echo "</pre>";
		
	}
	
	
	
}
?>