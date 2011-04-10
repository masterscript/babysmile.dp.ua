<?php

require_once 'PEAR/Pager/Pager.php';

class Admin_Childs_Goodsavailable_Common extends Admin_Actions_Abstract {
	
    /**
     * Объект Pager
     *
     * @var Pager_Common
     */
    private $objectPager;
    
	public function __construct() {
		
		parent::__construct();
		Admin_Forms_Action::processFormPersister();
	
	}
	
	/**
	 * Возвращает массив потомков в соответствии с установленным признаком показа
	 *
	 * @return array
	 */
	public function process () {
	    
	    $basic_sql = '
	    	SELECT *
            FROM items i
            JOIN goods g ON i.id = g.id
            WHERE pid = ?d AND type = \'good\'
	    ';	    
	    
	    // поля для фильтрации по LIKE
	    $selected_fields_like = array('name','description');
	    // формируем условие для поиска
	    $where_parts = array();
	    foreach ($selected_fields_like as $field) {
	        if (isset($_GET[$field]) && !empty($_GET[$field])) {
	            $where_parts[] = "`$field` LIKE '$_GET[$field]%'";
	        }
	    }
    	//  поля для фильтрации по "="
	    $selected_fields_eq = array('availability');
	    // формируем условие для поиска
	    foreach ($selected_fields_eq as $field) {
	        if (isset($_GET[$field]) && (!empty($_GET[$field]) || $_GET[$field]==0)) {
	            $where_parts[] = "`$field` = '$_GET[$field]'";
	        }
	    }	        
	    $where = implode(' AND ',$where_parts);
	    if (!empty($where)) $where = 'AND '.$where;
	    
	    // добавляем параметры сортировки
        if (in_array(@$_GET['sort_direction'],array('asc','desc')) && in_array(@$_GET['sort_name'],array_merge($selected_fields_like, $selected_fields_eq))) {
	    	$order = "ORDER BY {$_GET['sort_name']} {$_GET['sort_direction']}";
	    } else {
	    	$order = 'ORDER BY availability ASC';
	    }
	    
	    // получаем данные
        $data = $this->objectModel->query($basic_sql.' '.$where.' '.$order,$this->objectModel->getId());

	    // настраиваем Pager
	    $params = array(
	    	'path'		 => SITE_SUBDIR.'/view',
	    	'fileName'   => 'goodsavailable',
            'mode'       => 'Jumping',
            'perPage'    => 30,
            'delta'      => 10,
            'itemData'   => $data
        );
        $this->objectPager = _Pager::factory($params);
        
        return $this->objectPager->getPageData();
	    
	}
	
	public function getTemplateValue() {
		
		return array(
			'data'=>$this->process(),
			'links'=>$this->objectPager->getLinks()
		);
	
	}
	
}

?>
