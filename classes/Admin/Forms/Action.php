<?php

include_once "HTML/FormPersister.php";

class Admin_Forms_Action extends HTML_MetaFormAction {
	
	private $db_fields;
	
	private $errorMessage=array(
		"numeric"=>"Необходимо число",
		"numeric_notnegative"=>"Необходимо неотрицательное число",
		"integer"=>"Необходимо целое число",
		"filled"=>"Необходимо значение",
		"email"=>"Неправильный формат адреса электронной почты",
		"hexnumber"=>"Необходимо шестнадцатеричное число",
	    "password"=>"Неправильный ввод пароля",
		"url_part"=>"Неправильный адрес URL",
		"unique"=>"Нарушена уникальность",
	    "unique_edit"=>"Нарушена уникальность",
		"unique2"=>"Нарушена уникальность",
	    "unique_edit2"=>"Нарушена уникальность",
		"unique_move"=>"Нарушена уникальность URL. Перемещение невозможно",
	    "topimg"=>"Неправильный формат топового изображения. Ширина и высота должна быть 101px. Размер не должен превышать 100Кб",
		"rec_biz_unique"=>"Предприятие уже является лидером на выбранной странице",
	    "manual_common"=>""
	);
	
	/**
	 * Сообщения об общих ошибках валидации 
	 *
	 * @var array
	 */
	private $errorCommonMessage=array(
        "childs_exists"=>"У элемента есть потомки. Удаление невозможно",
		"prices_exists"=>"У элемента есть прайс-позиции. Удаление невозможно"
    );
	
	private $errors;
	
	/**
	 * Объект набора валидаторов, специфичных для типа данных
	 *
	 * @var Admin_Model_Validator_Common
	 */
	static private $objectValidator;
	
	/**
     * Инициализация обработчиков формы
     *
     * @return HTML_MetaForm
     */
	static public function init () {
    	
		$metaForm = new HTML_MetaForm('mysignature');
    	ob_start(array($metaForm,'process'));
    	ob_start(array('HTML_FormPersister', 'ob_formPersisterHandler'));

    	/** @var Admin_Model_Common $objectCollection */
        $objectCollection = Admin_Controller_Collection::getInstance();
    	self::$objectValidator = $objectCollection->model->getObjectModelValidator();
    	
    	return $metaForm;
    	
    }
    
    /**
     * Запуск обработчика потока библиотеки HTML_FormPersister
     *
     */
    static public function processFormPersister () {
        
        ob_start(array('HTML_FormPersister', 'ob_formPersisterHandler'));
        
    }
	
	/**
	 * @see HTML_MetaFormAction::process()
	 *
	 * @param array $commonValidators общие валидаторы формы
	 * @param array $fieldNames
	 * @param string $defaultAction
	 * @return string
	 */
	public function process ($commonValidators=NULL,$fieldNames=NULL,$defaultAction=NULL) {
		
		$form_state = parent::process($fieldNames,$defaultAction);
		// ручная проверка общих валидаторов формы
		if ($form_state!='INIT' && $form_state!=NULL && is_array($commonValidators)) {
		     foreach ($commonValidators as $name) {
		         $func_name = 'validator_common_'.$name;
    		     if (!method_exists(self::$objectValidator,$func_name)) {
        		    throw new Admin_CoreException('Вызов неизвестного валидатора: '.$func_name);
        		 }
        		 $state = call_user_func(array(self::$objectValidator,$func_name));
        		 if (!$state && !parent::process(array($name))) {
        		     return NULL;
        		 }
		     }
		}
		return $form_state;
		
	}

    
    /**
     * Переадресовывает вызовы неизвестных валидаторов к объекту модели
     *
     * @param string $name
     * @param array $args
     */
    public function __call ($name,$args) {
    	
    	if (strpos($name,'validator_')===0) {
    		// вызываем валидатор у объекта модели
    		if (!method_exists(self::$objectValidator,$name)) {
    		    throw new Admin_CoreException('Вызов неизвестного валидатора: '.$name);
    		}
    		// вызываем валидатор
    		return call_user_func_array(array(self::$objectValidator,$name),$args);
    	} else {
    	    throw new Admin_CoreException('Обращение к неизвестному методу: '.$name);
    	}
    	
    }
    
    /**
     * Общий валидатор для запуска в ручном режиме
     * Если он запущен, значит ошибка валидации происходит 
     * в любом случае. Факт ошибки определяется перед его запуском
     * и зависит от других условий, которые в самом валидаторе неопределены
     * 
     * @param string $field_name поле, для которого выполняется валидация
     * @return bool
     */
    public function validator_manual_common ($field_name) {
        
        // переопределяем текст ошибки
        $this->errorMessage['manual_common'] = isset($this->errorCommonMessage[$field_name])
            ?$this->errorCommonMessage[$field_name]
            :"Текст ошибки неопределен";
        return false;
        
    }
    
	/**
	 * Валидатор числа
	 *
	 * @param string $value
	 * @return bool
	 */
    public function validator_numeric ($value) {
		
    	if (empty($value)) return true;
		return is_numeric($value);
		
	}
	   
	/**
	 * Валидатор неотрицательного числа
	 *
	 * @param string $value
	 * @return bool
	 */
	public function validator_numeric_notnegative ($value) {
		
		if (empty($value)) return true;
		return (is_numeric($value) and $value>=0);
		
	}  
	
	/**
	 * Валидатор целого числа
	 *
	 * @param string $value
	 * @return bool
	 */
	public function validator_integer ($value) {
		
		if (empty($value)) return true;
		return preg_match('#^\d{0,4}$#',$value) or empty($value);
		
	}
	    
	/**
	 * Валидатор адреса электронной почты
	 *
	 * @param string $value
	 * @return bool
	 */
	public function validator_email ($value) {
		
		if (strlen($value)==0) return true;
		return preg_match('#^(\S+)[a-z0-9._-]+@([a-z0-9.-]+$)#is',$value);
		
	}
	
	/**
	 * Валидатор URL адреса
	 *
	 * @param string $value
	 * @return bool
	 */
	/*public function validator_url ($value) {
		
		if (strlen($value)==0) return true;
		return preg_match('#^http(\S+)[a-z0-9._-]+@([a-z0-9.-]+$)#is',$value);
		
	}*/
	
	/**
	 * Валидатор части адреса url
	 *
	 * @param mixed $value
	 */
	public function validator_url_part ($value) {
		
		return preg_match('#^[a-zA-Z\-_0-9]+$#',$value) || empty($value);
		
	}
	
	/**
	 * Запуск валидатора добавляет в массив значения полей, которые должны быть добавлены в таблицу базы данных
	 *
	 * @param string $value
	 * @return bool
	 */
	public function validator_dbfield ($value) {
		
		if (is_array($value) and isset($value['name'])) {
		    $value = basename($value['name']); //if input type=file
		}
		$this->db_fields[$this->currentElement] = $value;
		return true;
		
	}
	
	/**
	 * Валидатор пароля (используется проверка на соответствие значений в двух полях)
	 *
	 * @param mixed $value
	 * @return bool
	 */
	public function validator_password ($value) {
	    
	    if (empty($this->metaForm->MF_POST[$this->currentElement.'_repeat']) && empty($value)) {
	        return true;
	    }
	    if (isset($this->metaForm->MF_POST[$this->currentElement.'_repeat'])) {
	        return ($this->metaForm->MF_POST[$this->currentElement.'_repeat']==$value && strlen($value)>5);
	    }
	    return strlen($value)>5;
	    
	}
	
	/**
	 * Возвращает ошибки валидации
	 *
	 * @return array
	 */
	public function getErrors () {
		
		foreach (parent::getErrors() as $e) {
			$validator_name = str_replace(strtolower(__CLASS__).'::validator_','',$e['validator']);
			$message = isset($this->errorMessage[$validator_name])?$this->errorMessage[$validator_name]:'Текст ошибки неопределен';
			$this->errors[$e['name']]['message'] = $message;
			$this->errors[$e['name']]['validator'] = $validator_name;
			$this->errors[$e['name']]['label'] = isset($e['meta']['label'])?$e['meta']['label']:'validator '.$validator_name;
			$this->errors[$e['name']]['value'] = $e['meta']['value'];
		}
		
		return $this->errors;
		      
	}
	
	/**
	 * Добавление/изменение сообщения об ошибке валидации
	 *
	 * @param string $validator_name
	 * @param string $message
	 */
	public function addErrorMessage ($validator_name,$message) {
		
		$this->errorMessage[$validator_name] = $message;
		
	}
	
	/**
	 * Возвращает поля для занесения в базу данных
	 *
	 * @return array
	 */
	public function getDbFields () {
		
		if (!$this->db_fields) return array();
		return _Array::expand($this->db_fields);
		
	}
	                      
}

?>
