<?php

require_once 'PEAR/Pager/Pager.php';

class Admin_Childs_Common extends Admin_Childs_Abstract {
	
    /**
     * Объект Pager
     *
     * @var Pager_Common
     */
    protected $objectPager;
    
    /**
     * Признак показа всех потомков
     *
     * @var bool
     */
    protected $show_all;
    
	public function __construct() {
		
		parent::__construct();
		$this->setConfig();
		$this->show_all = @$_GET['mode']=='all';
		
		Admin_Forms_Action::processFormPersister();
	
	}
	
	/**
	 * Возвращает массив потомков в соответствии с установленным признаком показа
	 *
	 * @return array
	 */
	protected function getChilds () {
	    
	    // устанавливаем связь для выборки
	    if (!isset($this->config['link'])) {
	        throw new Admin_TemplateConfigException('Отсутствует параметр link в секции CHILDS');
	    }
	    $this->objectModel->setLink($this->config['link']);
	    // поля, участвующие в выборке
	    $selected_fields = array_keys($this->config['fields']);	    
	    
	    // формируем условие для поиска
	    $where = array(); $where_parts = array();
	    foreach ($selected_fields as $field) {
	        if (isset($_GET[$field]) && !empty($_GET[$field])) {
	            $where_parts[$field] = $_GET[$field].'%';
	        }
	    }
	    if (count($where_parts)) {
	        $where[] = $this->objectModel->where($where_parts,'AND','LIKE',true);
	    }
	    if ($this->show_all) {
	        $where[] = $this->objectModel->where(array('url'=>$this->objectModel->getItem('url')."/%"),'AND','LIKE');	        
	    } else {
	        $where[] = $this->objectModel->where(array('pid'=>$this->objectModel->getId()));
	    }
	    $display_types = $this->getDisplayTypes();
	    if ($display_types!==false) {
	    	$where[] = $display_types;
	    }
	    $where = implode(' AND ',$where);
	    
	    // добавляем параметры сортировки
	    if (in_array(@$_GET['sort_direction'],array('asc','desc')) && in_array(@$_GET['sort_name'],$selected_fields)) {
	    	$order = array($_GET['sort_name'],$_GET['sort_direction']);
	    } else {
	    	$order = array($this->config['sort_field'],$this->config['sort_direction']);
	    	// добавляем имя таблицы
	    	$this->config['sort_field'] = $this->objectModel->collapseField($this->config['sort_field']);
	    }
	    
	    // получаем данные
	    $childs = $this->objectModel->showMulti($selected_fields,$where,$order);

	    // настраиваем Pager
	    $params = array(
	    	'path'		 => SITE_SUBDIR,
	    	'fileName'   => 'view',
            'mode'       => 'Jumping',
            'perPage'    => 20,
            'delta'      => 10,
            'itemData'   => $childs
        );
        $this->objectPager = _Pager::factory($params);
        
        return array_merge(
            $this->config,
            array('data'=>$this->objectPager->getPageData(),
            	'count'=>$this->objectModel->getCount(false,array('pid'=>$this->objectModel->getId())))
            );
	    
	}
	
	public function getTemplateValue() {
		
		return array(
			'childs'=>$this->getChilds(),
			'links'=>$this->objectPager->getLinks()
		);
	
	}
	
}

?>
