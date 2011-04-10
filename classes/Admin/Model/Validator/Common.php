<?php

class Admin_Model_Validator_Common extends Admin_Db_Abstract {
	
	/**
	 * Конструктор класса
	 *
	 */
	public function __construct() {
		
		$this->setDefaultTable(Admin_Core::DEFAULT_TABLE);
        $this->setId(Admin_Core::getItemId());
		
	}
	
	/**
	 * Проверка существования потомков
	 *
	 * @return bool
	 */
	public function validator_common_childs_exists () {
	    
	    return !$this->recordExists($this->getDefaultTable(),array('pid'=>$this->id));
	    
	}
	
	/**
	 * Контроль выбора валюты по умолчанию
	 *
	 * @return bool
	 */
	public function validator_default_currency($value,$meta) {
	    
//		var_dump($this->getItem('id','currency',1,'default'),$this->getId(),$value);
		if ($this->getItem('id','currency',1,'default')==$this->getId() && $value==0) {
			return false;
		}
	    return true;
	    
	}
	
	/**
	 * Проверка значения поля на уникальность
	 *
	 * @return bool
	 */
	public function validator_unique ($value,$meta) {
	    
		list($table,$field) = $this->parseField($meta['name']);
		$value = $this->getItem($field).'/'.$value;
	    return !$this->recordExists($table,array($field=>$value));
	    
	}
	
	/**
	 * Проверка значения поля на уникальность в режиме редактирования
	 *
	 * @return bool
	 */
	public function validator_unique_edit ($value,$meta) {
	    
		list($table,$field) = $this->parseField($meta['name']);
		$value = _Strings::uri_parent($this->getItem($field)).'/'.$value;
		// если url не изменился, не выполняем проверку
		if ($this->getItem($field)==$value) return true;
	    return !$this->recordExists($table,array($field=>$value));
	    
	}
	
	/**
	 * Проверка параметров файла топового изображения
	 *
	 * @return bool
	 */
	public function validator_topimg ($value,$meta) {
        
	    // если не было попытки загрузить файл, то валидатор не запускается
	    if (empty($value['name'])) return true;
	    if (!preg_match('#^[A-Za-z0-9_\-.]+$#',$value['name'])) return false;
	    $objectUpload = new _Upload($meta['name'],'ru');
	    if (!$objectUpload->objectFiles->isValid()) return false; 
        // проверяем параметры изображения
        if (!$objectUpload->checkSize(300000)) return false;
        if (!$objectUpload->checkType('image/jpeg,image/gif,image/pjpeg')) return false;
        if (!$objectUpload->checkMaxImageSize(130,120)) return false;
	    return true;
	    
	}
		
	/**
	 * Контроль уникальности при перемещении
	 *
	 */
	public function validator_unique_move ($value) {
	    
	    // перемещать корень нельзя
	    if ($this->getId()==1) return false;
	    // url элемента, в который будет происходить перемещение
	    $move_url = $this->getItem('url',$this->getDefaultTable(),$value);
	    if (is_null($move_url)) return false;
	    // новый url элемента
	    $url_new = $move_url.'/'._Strings::uri_part($this->getItem('url'));
	    // проверяем уникальность
	    if ($this->recordExists($this->getDefaultTable(),array('url'=>$url_new))) return false;
	    
	    return true;
	    
	}
	
	public function validator_max255 ($value) {
	    
	    return strlen($value)<255;
	    
	}
	
}

?>
