<?php

/**
 * Класс, содержащий в себе методы получения данных для формы 
 *
 */
class Admin_Model_Source_Common extends Admin_Db_Abstract {
	
	/**
	 * Конструктор класса
	 *
	 */
	public function __construct() {
		
		$this->setDefaultTable(Admin_Core::DEFAULT_TABLE);
        $this->setId(Admin_Core::getItemId());
		
	}
    

	/**
	 * Возвращает часть url для формы редактирования
	 *
	 * @return string
	 */
	public function source_url_part () {
				
		return _Strings::uri_part($this->getItem('url'));
		
	}
	
	/**
	 * Возвращает список уровней доступа к элементу
	 *
	 * @return array
	 */
	public function source_protected () {
	    
	    return array('для всех посетителей','для зарегистрированных','закрытая страница');
	    
	}
	
	public function source_biz_list () {
		
		$biz_container_id = $this->selectCell('SELECT id FROM ?_?s WHERE template = ?','items','biz_main');
		return array('0'=>'-----------')+$this->showForSelect('items',array('name'),array('pid'=>$biz_container_id),' `name` ASC');
		
	}
	
	/**
	 * Возвращает список полов
	 *
	 * @return array
	 */
	public function source_sex () {
	    
	    return array('unknown'=>'неизвестно','male'=>'мужской','female'=>'женский');
	    
	}
	
	/**
	 * Возвращает тип (шаблон) элемента
	 *
	 * @return string
	 */
	public function source_type () {
		
		if ($this->getItem('template')!='') {
			return $this->getItem('template');
		}
		return $this->getItem('type');
		
	}
	
	public function source_user_groups () {
	    
	    return $this->showForSelect('groups','group_name');
	    
	}
	
	public function source_carrier_regions_add() {
	    
	    return $this->selectCol('
	    	SELECT name AS ARRAY_KEYS,name FROM regions
	    	WHERE name NOT IN (SELECT name FROM items WHERE pid = ?d)
	    	ORDER BY name
	    ',$this->getId());
	    
	}
	
	public function source_carrier_regions_edit() {
	    
	    return $this->selectCol('
	    	SELECT name AS ARRAY_KEYS,name FROM regions
	    	WHERE name NOT IN (SELECT name FROM items WHERE pid = 
	    		(SELECT pid FROM items WHERE id = ?d) AND name <> (SELECT name FROM items WHERE id = ?d)
	    	) ORDER BY name
	    ',$this->getId(),$this->getId());
	    
	}
	
	public function source_html_static () {
		
		$objectConfig = Admin_Core::getObjectGlobalConfig();
		$content_path = basename($objectConfig->getConfigSection('FOLDERS','content'));
		$filename = SYSTEM_PATH.'/'.$content_path.'/'.$this->getItem('id').'.html';
		if (file_exists($filename)) {
			return file_get_contents($filename);
		}
		return '';
		
	}
	
    public function source_check_anounce () {
	            
	    return $this->getCount('main_annonces');
	    
	}
	
	public function source_banners_types () {
		
		return array('image'=>'Изображение','flash'=>'Flash анимация');
		
	}
	
	public function source_small_banners () {
		
		return $this->selectCol('
			SELECT id AS ARRAY_KEYS, name FROM ?_?s
			WHERE id NOT IN (SELECT banner_id FROM ?_?s WHERE item_id = ?d) ORDER BY id',
			'smallbanners','smallbannerspos',$this->getId()
		);
		
	}
	
	public function source_banners_position () {
		
		return array('none'=>'неактивный','left'=>'слева','right'=>'справа');
		
	}
	
    public function autoset_item_id () {
		
		return Admin_Core::getItemId();
		
	}
	
	public function autoset_current_date () {
		
		return date('Y-m-d H:i:s');
		
	}
    
}

?>