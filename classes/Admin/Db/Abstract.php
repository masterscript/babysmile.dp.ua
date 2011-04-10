<?php

abstract class Admin_Db_Abstract extends DbSimple_Generic {
	
	/**
	 * Объект класса DbSimple_Mysql
	 *
	 * @var DbSimple_Mysql
	 */
	private static $objectDb;
	
	/**
	 * Объект для работы со связями
	 *
	 * @var Admin_Db_Links
	 */
	protected static $objectLinks;
	
	/**
	 * id элемента
	 *
	 * @var integer
	 */
    protected $id;
    
    /**
     * Префикс таблиц
     *
     * @var string
     */
    private static $prefix;
    
    /**
     * Название поля id
     *
     * @var string
     */
    protected $id_field = 'id';
    
    /**
     * Массив рабочих таблиц
     *
     * @var array
     */
    private $tables = array();
    
    /**
     * Таблица по умолчанию
     *
     * @var string
     */
    private $default_table;
    
    /**
     * Значение для операций с таблицами
     *
     * @var array
     */
    protected $values = array();
    
    /**
     * Подключение к базе данных
     *
     * @param string $dsn параметры подключения
     */
    static public function db_connect ($dsn) {
    	
    	if (!self::$objectDb) {
    		self::$objectDb = parent::connect($dsn);
    		// устанавливаем префикс таблиц из файла конфигурации
    		$objectConfig = Admin_Core::getObjectGlobalConfig();
	        self::$prefix = $objectConfig->getConfigSection('DATABASE','table_prefix');
    	}
    	
    }
    
    /**
     * Проверяет наличие таблицы в списке рабочих таблиц
     *
     * @param string $table
     * @return string имя таблицы
     */
    private function checkTable ($table) {
        
        /*if (!in_array($table,$this->tables) && is_string($table)) {
    		throw new Admin_DbException("Для таблицы $table не выполен метод setTable");
    	}*/
    	return $table;
        
    }
    
    /**
     * Переадресация вызовов неизвестных методов классу DbSimple_Mysql
     *
     * @param string $name
     * @param array $args
     * @return mixed
     */
    public function __call ($name,$args) {
    	
    	if (method_exists(self::$objectDb,$name)) {
    		return call_user_func_array(array(self::$objectDb,$name),$args);
    	} else {
    		throw new Admin_CoreException("Method $name doesn't exists in DbSimple_Mysql");
    	}
    	
    }
    
    /**
     * Устанавливает имя рабочей таблицы или добавляет таблицы к уже существующим
     *
     * @param mixed $table
     */
	protected function setTable ($table) {
      
		if (is_array($table)) {
			$this->tables = $table;
		} else {
			$this->tables[] = $table;
		}
	    
	}
	
	public function setDefaultTable ($table_name) {
		
		$this->default_table = $table_name;
		$this->setTable($table_name);
		
	}
	
	/**
	 * Исключает таблицу из списка рабочих по ее имени
	 *
	 * @param string $table
	 * @return mixed
	 */
	protected function eliminateTable ($table) {
		
		$index = array_search($table,$this->tables);
		if ($index!==false) {
			unset($this->tables[$index]);
		}
		return $index;
		
	}
    
	/**
	 * Устанавливает id текущего элемента
	 *
	 * @param integer $id
	 */
	public function setId ($id) {
	
		$this->id = $id;
	
	}
	
	/**
	 * Устанавливает название поля id
	 *
	 * @param string $id
	 */
	protected function setIdField ($id_field) {
	
		$this->id_field = $id_field;
	
	}
	
	/**
	 * Возвращает id текущего элемента
	 *
	 * @return integer
	 */
	public function getId() {
		
		return $this->id;
		
	}
    
    /**
     * Возвращает значение поля по имени
     *
     * @param string $name имя поля
     * @param integer $id
     * @return mixed
     */
    public function getItem ($name,$table=false,$id=false,$id_field=false) {
    	
    	$id = $id?$id:$this->id;
    	$id_field = $id_field?$id_field:$this->id_field;
    	$table = $table?$table:$this->default_table;                                                              
    	return self::$objectDb->selectCell('SELECT ?# FROM ?_?s WHERE ?# = ?',$name,$this->checkTable($table),$id_field,$id);
    	
    }
    
    /**
     * Возвращает значение из базы по переданному источнику в виде table::field
     *
     * @param string $source_field
     * @return mixed
     */
    public function getItemFromSource($source_field) {
    	
    	list($table,$field) = $this->parseField($source_field);
    	return $this->getItem($field,$table);
    	
    }
    
    /**
     * Устанавливает значения, которые будут участвовать в операциях вставки, обновления таблиц базы данных
     *
     * @param array $values
     */
    protected function setValues ($values) {
      
    	$this->values = $values;
      
    }
    
    protected function eliminateEmptyValues () {
    	
    	// удаляем поля с пустыми значениями
    	foreach ($this->values as $table=>$fields) {
    		foreach ($fields as $key=>$value) {
    			if ($value==='') {
    				unset($this->values[$table][$key]);    				
    			}
    		}
    	}
    	
    	// удаляем таблицы без полей
    	foreach ($this->values as $table=>$fields) {
    		if (count($this->values[$table])==0) {
                unset($this->values[$table]);
            }
    	}
    	
    }
    
    /**
     * Возвращает поля для одной записи
     * из нескольких таблиц
     * 
     * @param array $db_fields
     * @return array
     */
	protected function showForForm ($db_fields) {
		
	    if (is_null(self::$objectLinks)) {
	        throw new Admin_DbException('Не установлены связи для таблиц');
	    }
	    $field_data = array();
	    foreach ($db_fields as $table=>$fields) {
	        // ключевое поле
    	    $id_field = self::$objectLinks->getByTable($table);
	    	if ($id_field===false) {
    	    	throw new Admin_DbException("Таблица $table отсутствует в стеке связей. Возможно из-за этого произошло неполное обновление данных");
    	    }
    	    // получаем данные
    	    if (self::$objectLinks->getLinkType($table)=='O2M') {
    	    	foreach (array_keys($fields) as $field) {
    	    		$field_data[$table][$field] = self::$objectDb->selectCol('SELECT ?# FROM ?_?s WHERE ?# = ?',$field,$table,$id_field,$this->id);
    	    	}	        	
    	    } else {
    	    	$field_data[$table] = self::$objectDb->selectRow('SELECT ?# FROM ?_?s WHERE ?# = ?',array_keys($fields),$table,$id_field,$this->id);
    	    }
	    }
		return $field_data;
	
	}
	
	/**
	 * Возвращает имя таблицы и название поля из склееной строки
	 *
	 * @param string $field
	 * @param string $delimiter
	 * @return array
	 */
	public function parseField ($field,$delimiter='::') {
		
		if (strpos($field,$delimiter)!==false) {
			return explode($delimiter,$field);
		} else {
			return array($this->getDefaultTable(),$field);
		}
		
	}
	
	/**
	 * Выполняет сворачивание поля в сокращенную запись
	 *
	 * @param string $field
	 * @param string $table
	 * @param string $delimiter
	 * @return string
	 */
	public function collapseField ($field,$table=false,$delimiter='::') {
	    
	    // если уже имя поля содержит разделитель, то просто возвращаем его
	    if (strpos($field,$delimiter)!==false) return $field;
	    if (!$table) $table = $this->default_table;
	    return $table.$delimiter.$field;
	    
	}
	
	/**
	 * Возвращает данные для списка выбора формы
	 *
	 * @param string $table название таблицы
	 * @param mixed $fields список полей
	 * @param array $where дополнительное условие array($field,$value)
	 * @return array
	 */
	public function showForSelect ($table,$fields='*',$where=array(),$order='id ASC',$add_where = '') {
	    
	    $where = trim($this->where($where).' '.$add_where);
	    return self::$objectDb->selectCol('
	    	SELECT id AS ARRAY_KEYS, ?# FROM ?_?s {WHERE ?s} ORDER BY '.$order,
	        $fields, $table, $where?$where:DBSIMPLE_SKIP
	    );
	    
	}
	
	public function getCount ($table=false,$where=array(),$field='*') {
	    
	    if (!$table) $table = $this->default_table;
		if (!count($where) && $where!==false) {
			$where = array($this->id_field=>$this->id);
		}
		$where = $this->where($where);
	    return self::$objectDb->selectCell('SELECT COUNT(?s) FROM ?_?s {WHERE ?s}',$field,$table,$where?$where:DBSIMPLE_SKIP);
		
	}
	
	/**
	 * Возвращает записи по заданному условию
	 *
	 */
	public function show ($table=false,$where=array(),$fields=array('*'),$order='') {
		
		if (!$table) $table = $this->default_table;
		if (count($where)) $where = $this->where($where); 
	    else $where = false;
		if (!empty($order)) $order = $this->order($order);
		$fields=(array)$fields;
		return self::$objectDb->select('SELECT ?f FROM ?_?s {WHERE ?s} ?s',$fields,$table,$where?$where:DBSIMPLE_SKIP,$order);
		
	}
	
	/**
	 * Показывает записи из нескольких связанных таблиц
	 *
	 * @return array
	 */
	public function showMulti ($fields='*',$where=array(),$order=array()) {
		
	    // формируем список полей для выбора
	    $prefix = self::$prefix;
	    $select_fields = '';
	    if (is_array($fields)) {
	        foreach ($fields as $value) {
	            list($table,$field) = $this->parseField($value);
	            $select_fields[] .= $prefix.$table.'.'.$field.' AS `'.$this->collapseField($field,$table).'`';
	        }
	        $select_fields = implode(', ',$select_fields);
	    } else {
	        $select_fields = $fields;
	    }
	    
	    // таблица, к которой будут присоединяться по связи остальные таблицы
	    list($table_first,$field_first) = self::$objectLinks->getFirst();
	    $query = "SELECT $select_fields FROM $prefix$table_first";
	    
	    // проходим по связи и линкуем остальные таблицы
	    foreach (self::$objectLinks->getAllLinks() as $link) {
	        list($table,$field) = $link;
	        if (!self::$objectLinks->isFirst($table)) {
	            $query .= " LEFT JOIN $prefix$table ON $prefix$table_first.$field_first = $prefix$table.$field";
	        }
	    }
	    
	    // обработка условия выборки
	    if (is_array($where) && count($where)) {
	        $query .= ' WHERE '.$this->where($where);
	    } elseif (is_string($where) && !empty($where)) {
	        $query .= ' WHERE '.$where;
	    }
	    
	    // добавление сортировки
	    if (count($order)) {
	    	$query .= ' '.$this->order($order);
	    }
	    
	    // выполнение сформированного запроса
	    return self::$objectDb->select($query);
		
	}
	
	/**
     * Вставка данных в таблицу
     *
     */
	protected function insert () {
		
	    if (is_null(self::$objectLinks)) {
	        throw new Admin_DbException('Не установлены связи для таблиц');
	    }
	    
		// цикл по таблицам
		foreach ($this->values as $table=>$values) {
		    if (is_string($table)) $this->checkTable($table);
	    	if (is_integer($table)) $table = $this->default_table;
	    	
	    	// анализ связей
	    	if (!self::$objectLinks->isFirst($table)) {
	    	    $linked_field = self::$objectLinks->getByTable($table);
	    	    // таблица отсутствует в стеке связей
	    	    if ($linked_field===false) {
	    	    	throw new Admin_DbException("Таблица $table отсутствует в стеке связей. Возможно из-за этого произошла неполная вставка данных");
	    	    }
	    	    // предыдущая связанная таблица
	    	    list($prev_table,$prev_field) = self::$objectLinks->getPrev($table);
	    	    // добавляем ключевое поле для вставки по связи
	    	    $values[$linked_field] = $this->getLastId($prev_table,$prev_field);
	    	}
	    	
			self::$objectDb->query(
				'INSERT INTO ?_?s (?#) VALUES (?a)',
				$table,
				array_keys($values),
				array_values($values)
			);
		}
	
	}
	
	/**
	 * Обновляет записи
	 *
	 * @param array $where массив условий
	 */
	protected function update () {
		
	    if (is_null(self::$objectLinks)) {
	        throw new Admin_DbException('Не установлены связи для таблиц');
	    }
		foreach ($this->values as $table=>$values) {
		    if (is_string($table)) $this->checkTable($table);
	    	if (is_integer($table)) $table = $this->default_table;
	    	// ключевое поле
    	    $id_field = self::$objectLinks->getByTable($table);
    	    if ($id_field===false) {
    	    	throw new Admin_DbException("Таблица $table отсутствует в стеке связей. Возможно из-за этого произошло неполное обновление данных");
    	    }
    	    // делаем вставку вместо обновления, если запись не существует в связанной таблице
    	    if (!self::$objectLinks->isFirst($table) && !$this->recordExists($table,array($id_field=>$this->id))) {
    	    	 $values[$id_field] = $this->id;
    	    	 self::$objectDb->query('INSERT INTO ?_?s (?#) VALUES (?a)',$table,array_keys($values),array_values($values));
    	    } else {
    	    	// обновление
    	    	if ($values) {
					self::$objectDb->query('UPDATE ?_?s SET ?a WHERE ?# = ?',$table,$values,$id_field,$this->id);
    	    	}
    	    }
		}
	  
	}
	
	/**
	 * Обновляет запись
	 *
	 * @param string $table имя таблицы
	 * @param string|array $field имя поля
	 * @param mixed|array $value новое значение
	 * @param array $where условие
	 * @return mixed
	 */
	public function updateField ($table,$field,$value,$where=array()) {
	    
	    if (!$table) $table = $this->default_table;
	    if (!count($where)) {
			$where = array($this->id_field=>$this->id);
		}
	    $where = $this->where($where);
	    return self::$objectDb->query('UPDATE ?_?s SET ?# = ? WHERE ?s',$table,$field,$value,$where);
	    
	}
	
	public function updateFromArray ($fields,$where=array(),$table=false) {
		
		if (!$table) $table = $this->default_table;
	    if (!count($where)) {
			$where = array($this->id_field=>$this->id);
		}
	    $where = $this->where($where);
	    return self::$objectDb->query('UPDATE ?_?s SET ?a WHERE ?s',$table,$fields,$where);
		
	}
	
	/**
	 * Вставляет новую запись
	 *
	 * @param string $table
	 * @param array $values
	 * @return mixed
	 */
	public function insertRecord ($table,$values) {
	    
	    return self::$objectDb->query('INSERT INTO ?_?s (?#) VALUES (?a)',$table,array_keys($values),array_values($values));
	    
	}
	
	public function insertValues($table,$fields,$values) {
		
		return self::$objectDb->query('INSERT INTO ?_?s (?#) VALUES (?a)',$table,$fields,$values);
		
	}
	
	/**
	 * Склеивает условие для запроса
	 *
	 * @param mixed $field
	 * @param mixed $value
	 * @param string $operator
	 * @return string
	 */
	private function implodeQuery ($field,$value,$operator='AND') {
		
		if (!is_array($field) && !is_array($value)) {
			return "`$field` = '$value'";
		}
		if (count($field)!=count($value) || count($field)<=1) {
			throw new Admin_DbException('Количество полей не соответствует количеству значений в методе implodeQuery');
		}
		for ($i=0; $i<count($field); $i++) {
			if (empty($field[$i]) || empty($value[$i])) continue;
			$query[] = "`{$field[$i]}` = '{$value[$i]}'";
		}
		return implode(" $operator ",$query);
		
	}
	
	/**
	 * Возвращает имя таблицы по умолчанию
	 *
	 * @return string
	 */
	public function getDefaultTable () {
		
		return $this->default_table;
		
	}
	
	/**
	 * Изменяет url элемента и выполняет перестроение url потомков
	 *
	 * @param string $table
	 * @param string $url_new
	 * @param string $url_old
	 */
	protected function changeChildsUrl ($url_new,$url_old,$table=false) {
        
	    if (!$table) $table = $this->default_table;
		$this->query(
			'UPDATE ?_?s SET url=CONCAT(?, SUBSTRING(url, LENGTH(?)+1))
			 WHERE url LIKE ?',
			 $table,$url_new,$url_old,addcslashes($url_old.'/','%_').'%'
		);
		
	}
	
	
	/**
	 * Проверка существования записи
	 *
	 * @param string $table
	 * @param array $where
	 * @return bool
	 */
	public function recordExists ($table,$where=array()) {
        
	    if (!count($where)) {
			$where = array($this->id_field=>$this->id);
		}
		return self::$objectDb->selectCell(
			'SELECT COUNT(*) FROM ?_?s WHERE ?s',$table,$this->where($where)) > 0;
	
	}
	
	/**
	 * Соединяет условия в строку
	 *
	 * @param array $where
	 * @param string $cond
	 * @param bool $eliminateEmpty исключить пустые значения из запроса
	 * @return string
	 */
	public function where ($where,$cond='AND',$operator='=',$eliminateEmpty=false) {
		
	    if ($where===false) return false;
		$where_modified = array();
		foreach ($where as $field=>$value) {		    
		    if ($eliminateEmpty && empty($value)) continue;
		    if (strpos($field,'::')!==false) {
		        list($table,$field) = $this->parseField($field);
		        $field = self::$prefix."$table.$field";
		    }
		    if (!is_string($field)) {
		        // передано сразу условие
		        $where_modified[] = $value;
		    } else {
		        // формируем условие из поля и его значения
			    $where_modified[] = "$field $operator '$value'";
		    }
		}
		return implode(" $cond ",$where_modified);
		
	}
	
	/**
	 * Возвращает часть запроса ORDER
	 *
	 * @param array $order
	 * @return string
	 */
	protected function order ($order) {
		
		$order = (array)$order;
		if (count($order==1)) array_push($order,'ASC');
		list($field,$direction) = $order;
		return "ORDER BY $field $direction";
		
	}
	
	/**
	 * Возвращает id последней вставленной записи
	 *
	 * @return integer
	 */
	public function getLastId ($table=false,$id_field=false) {
	    
		if (!$table) $table = $this->default_table;
	    if (!$id_field) {
	        $id_field = $this->id_field;
	    }
		return self::$objectDb->selectCell('SELECT MAX(?#) FROM ?_?s',$id_field,$table);
	
	}
	
	/**
	 * Устанавливает связи для таблиц
	 *
	 * @param array $link
	 */
	public function setLink ($link) {
	    
		if (self::$objectLinks==NULL) {
			self::$objectLinks = new Admin_Db_Links($link);
		}
	    
	}
    
	/**
	 * Удаляет запись по id
	 *
	 * @param string $table
	 * @param integer $id
	 * @param string $id_field
	 * @return mixed
	 */
    public function deleteRecord ($table=false,$id=false,$id_field=false) {

        if (!$table) $table = $this->default_table;
        if (!$id) $id = $this->id;
        if (!$id_field) $id_field = $this->id_field;
        return self::$objectDb->query('DELETE FROM ?_?s WHERE ?# = ?',$table,$id_field,$id);
        
    }
    
    
    public function deleteMulti ($where,$table=false) {
    	
    	if (!$table) $table = $this->default_table;
    	$where = $this->where($where);
    	return self::$objectDb->query('DELETE FROM ?_?s WHERE ?s',$table,$where);
    	
    }
    
    /**
     * Удаляет записи по связям
     *
     */
    public function delete ($table=false) {
        
        if (!$table) $table = $this->default_table;
        // удаляем записи по всем связям
        foreach (self::$objectLinks->getAllLinks() as $link) {
            list($tablename,$fieldname) = $link;
            $tablename = str_replace('>','',$tablename);
            $this->deleteRecord($tablename,false,$fieldname);
        }
        
    }
    
    /**
     * Возвращает информацию о родительском элементе
     *
     */
    public function getParentItem ($name=false,$table=false,$id=false,$pid_field='pid') {
        
        if (!$table) $table = $this->default_table;
        if (!$id) $id = $this->id;
        $params = self::$objectDb->selectRow(
        	'SELECT * FROM ?_?s WHERE id = (SELECT ?# FROM ?_?s WHERE id = ?)',$table,$pid_field,$table,$id);
        if ($name) {
            return $params[$name];
        }
        return $params;
        
    }

	public function getJoinOn(Admin_Db_Links $links = null) {
		
		if (is_null($links)) {
			$links = self::$objectLinks;
		}
		
		if (is_null($links)) {
	        throw new Admin_DbException('Не установлены связи для таблиц');
	    }
	    
	    list($table_first,$field_first) = $links->getFirst();
	    $sql = '';
		foreach ($links->getAllLinks() as $link) {
	        list($table,$field) = $link;
	        if (!$links->isFirst($table)) {
	            $sql .= " LEFT JOIN ?_$table ON ?_$table_first.`$field_first` = ?_$table.`$field`";
	        }
	    }
	    
	    return $sql;
	    
	}
    
}

?>
