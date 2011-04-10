<?php

/**
 * Абстрактный класс модели данных
 *
 */
abstract class Admin_Model_Abstract extends Admin_Db_Abstract implements Admin_IModel {
    
    /**
     * Данные для основной формы редактирования элемента
     *
     * @var array
     */
    protected $field_data;
    
    /**
     * Данные для таблиц БД
     *
     * @var array
     */
    protected $db_fields = array();
    
    /**
     * Массив постобработчиков формы
     *
     * @var array
     */
    protected $posthandlers;
    
    /**
     * Array of m2m tables related to current item:
     * key - is table name
     * value - array of field linked to item and othe link field names.
     * @var array
     */
    protected $_m2m_tables = array();
    
    protected $_scenario;
    
    public function __construct() {
        
        $this->setDefaultTable(Admin_Core::DEFAULT_TABLE);
        $this->setId(Admin_Core::getItemId());
        
    }

	private function _setM2MTables() {
    	
    	if (!self::$objectLinks)
    		return;
    		
    	foreach ($this->db_fields as $table_name=>$fields) {
    		
			if (self::$objectLinks->getLinkType($table_name)=='O2M') {
				
                $item_field = self::$objectLinks->getByTable($table_name);
                // detect link field by "[]" present
                $link_field = null;
                foreach (array_keys($fields) as $field) {
                	$matches = array();
                	if (preg_match('/(.*?)\[\]$/',$field,$matches)) {
                		$link_field = $matches[1];
                	}
                }
                
                if (!$link_field)
                	throw new Admin_CoreException("Cannot find link field with '[]' for M2M link type");
                	
                $this->_m2m_tables[$table_name] = array($item_field,$link_field);
                
            }
    	}
    	
    }
    
    protected function _beforeAction() {
    	
    	// delete m2m tables
		foreach (array_keys($this->_m2m_tables) as $table_name) {
			unset($this->db_fields[$table_name]);
		}
    	
    }
    
    protected function _afterAction() {
    	
    	$id_value = $this->_scenario == 'insert' ? $this->getLastId() : $this->id;
    	
    	foreach ($this->_m2m_tables as $table_name=>$fields) {
    		list($item_field,$other_field) = $fields;
    		// delete all relations
    		$this->deleteMulti(array("`$item_field` = '{$this->id}'"),$table_name);
    		// insert new relations
    		$values = $_POST["$table_name::$other_field"];
    		foreach ($values as $value) {
    			$this->insertValues($table_name,$fields,array($id_value,$value));
    		}
    	}
    	
    }
    
	public function insert() {
		
		$this->_scenario = 'insert';
		
		$this->_beforeAction();
		parent::setValues($this->db_fields);
		parent::insert();
        $this->execPosthandlers();
        $this->_afterAction();
		
	}
	
	public function update() {
		
		$this->_scenario = 'update';
		
		$this->_beforeAction();
		parent::setValues($this->db_fields);
		parent::update();
	    $this->execPosthandlers();
	    $this->_afterAction();
	    
	}
	
	public function delete ($table=false) {
		
		$this->_beforeAction();
		parent::delete($table);
		$this->execPosthandlers();
		$this->_afterAction();
		
	}
	
	/**
	 * Добавляет таблицы из массива полей к списку рабочих таблиц
	 *
	 * @param array $db_fields
	 */
	public function setValues ($db_fields) {
		
		foreach ($db_fields as $table) {
			if (is_string($table)) {
				parent::setTable($table);
			}
		}
		
		parent::setValues($db_fields);
		// исключаем поля с пустыми значениями
//		$this->eliminateEmptyValues();
		$this->db_fields = $this->values;
        $this->_setM2MTables();
		
	}
	
    /**
     * Исключает из массива данных полей названия полей, для которых существует собственный источник данных
     *
     * @param array $sourced_fields
     */
    protected function eliminateSourcedFields ($sourced_fields) {
    	
    	foreach ($sourced_fields as $value) {
    		unset($this->field_data[$value]);
    	}
    	
    }
    
	/**
	 * Возвращает значения для заполнения формы
	 *
	 * @param Admin_Forms_Builder $objectFormBuilder
	 * @return array
	 */
	public function getFieldsData ($objectFormBuilder) {
	    
		// все поля формы
		$this->field_data = $objectFormBuilder->getFieldsNames();
		// исключаем поля с собственным источником данных
		foreach ($objectFormBuilder->getFieldsWithSource() as $name) {
			$index = array_search($name,$this->field_data);
			unset($this->field_data[$index]);
		}
		
        // разворачиваем в подмасcивы
        $this->field_data = _Array::expand(array_flip($this->field_data));
		// заполняем данными из таблицы БД
	    $this->field_data = $this->showForForm($this->field_data);
	    // сворачиваем в один массив для передачи в поля формы
		$this->field_data = _Array::collapse($this->field_data);
				
	    return $this->field_data;
	    
	}
    
    /**
     * Возвращает объект для работы с источниками данных формы
     *
     * @return Admin_Model_Source_Common
     */
    public function getObjectModelSource () {
        
       return Admin_Controller_Factory::createObject('Admin_Model_Source',Admin_Core::getItemConfigName(),Admin_Core::getActionClassName()); 
        
    }
    
    public function getObjectModelValidator () {
        
        return Admin_Controller_Factory::createObject('Admin_Model_Validator',Admin_Core::getItemConfigName(),Admin_Core::getActionClassName());
        
    }
    
    public function getObjectModelPosthandler () {
        
        return Admin_Controller_Factory::createObject('Admin_Model_Posthandler',Admin_Core::getItemType(),Admin_Core::getActionClassName());
        
    }
    
	/**
	 * Устанавливает значение в поле
	 *
	 * @param string $table
	 * @param string $name
	 * @param mixed $value
	 */
	public function setDbField ($name,$value,$table=false) {
		
		if (!$table) $table = $this->getDefaultTable();
		$this->db_fields[$table][$name] = $value;
		
	}
    
	/**
	 * Возвращает значение поля из БД по имени поля на форме
	 *
	 * @param string $field
	 * @return mixed
	 */
	public function getDbValueByField ($field) {
	    
	    if (strpos($field,'::')!==false) {
	        list($table,$field) = explode('::',$field);
	    } else {
	        $table = $this->getDefaultTable();
	    }
	    return $this->getItem($field,$table);
	    
	}
	
	/**
	 * Возвращает значение поля из формы
	 * Можно использовать в формате table::field
	 * либо передавать таблицу отдельным параметром
	 *
	 * @param string $field
	 * @param string $table
	 * @return mixed
	 */
	public function getFormValue ($field,$table=false) {
	    
	    if (strpos($field,'::')!==false) {
	        list($table,$field) = explode('::',$field);
	    } else {
	        if (!$table) $table = $this->getDefaultTable();
	    }
	    if (isset($this->db_fields[$table][$field])) {
	        return $this->db_fields[$table][$field];
	    } else {
            // таблица не определена - поиск по массиву
            foreach ($this->db_fields as $table=>$fields) {
                if (is_integer($table) && isset($fields[$field])) {
                    return $fields[$field];
                }
            }
	        return false;
	    }
	    
	}
	
	/**
	 * Исключает поле из списка для операций с БД
	 *
	 * @param string $field
	 * @param string|bool $table
	 */
	public function eliminateDbField ($field,$table=false) {
		
		if (!$table) $table = $this->getDefaultTable();
		if (isset($this->db_fields[$table][$field])) {
			unset($this->db_fields[$table][$field]);
		}
		
	}
	
	/**
	 * Устанавливает массив постобработчиков формы
	 *
	 * @param array $posthandlers
	 */
	public function setPosthandlers ($posthandlers) {
	    
	    $this->posthandlers = $posthandlers;
	    
	}
	
	/**
	 * Выполняет постобработчики формы
	 *
	 */
	private function execPosthandlers () {
	    
	    if (!$this->posthandlers) return ;
	    $objectPosthandler = $this->getObjectModelPosthandler();
	    foreach ($this->posthandlers as $func_name) {
	        $func_name = 'posthandler_'.$func_name;
	        if (method_exists($objectPosthandler,$func_name)) {
	            call_user_func(array($objectPosthandler,$func_name));
	        } else {
	            throw new Admin_FormBuilderException('Вызов несуществующего постобработчика формы: '.$func_name);
	        }
	    }
	    
	}
	
	/**
     * Updates url field on autogenerated value.
     * Impements to exist record in default table.
     * @param integer $id
     */
    protected function _autoUrl($id) {
    	
    	// parent url + id
		$url = $this->getItem('url','items',$this->getItem('pid','items',$id)).'/'.$id;
		$this->updateField($this->getDefaultTable(),'url',$url,array('id'=>$id));
    	
    }
	
}

?>
