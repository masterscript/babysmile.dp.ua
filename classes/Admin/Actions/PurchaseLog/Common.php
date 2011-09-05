<?php

/**
 * Класс вывода пользователей для формирования рассылки
 *
 */
class Admin_Actions_PurchaseLog_Common extends Admin_Actions_Abstract {
	
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
	    	SELECT
	    		uo.*,carriers.name carrier,offices.name carrier_office, cities.name city, regions.name region,
	    		up.*,IFNULL(u.fio,anonym_name) fio,IFNULL(u.email,anonym_email) email,i.title,i.name,i.url,
	    		g.availability,
	    		IFNULL(SUM(gc.price), 0) + IFNULL(g.price, 0) AS price,
	    		(IFNULL(SUM(gc.price), 0) + IFNULL(g.price, 0)) * up.count AS price_sum
            FROM user_purchase up
            JOIN user_orders uo ON uo.code = up.code
            LEFT JOIN users u ON u.id = uo.user_id
            JOIN items i ON i.id = up.good_id
            JOIN goods g ON g.id = i.id
            LEFT JOIN items c ON c.pid = i.id
		  	LEFT JOIN goods gc ON c.id = gc.id
            LEFT JOIN items offices ON offices.id = uo.carrier_office
            LEFT JOIN items carriers ON carriers.id = offices.pid            
            LEFT JOIN items cities ON cities.id = uo.city_id
            LEFT JOIN items regions ON regions.id = cities.pid
            WHERE (1) /*WHERE*/
            GROUP BY i.id,up.code
	    ';	    
	    
	    // поля для фильтрации по LIKE
	    $selected_fields = array('up.code','i.title','fio','buy_date','count');	    	    
	    // формируем условие для поиска
	    $where_parts = array();
	    foreach ($selected_fields as $field) {
			$key = str_replace('.','_',$field);
	        if (isset($_GET[$key]) && !empty($_GET[$key])) {
	            $where_parts[] = "$field LIKE '$_GET[$key]%'";
	        }
	    }
    	//  поля для фильтрации по "="
	    $selected_fields = array('status');	    	    
	    // формируем условие для поиска
	    foreach ($selected_fields as $field) {
	        if (isset($_GET[$field]) && (!empty($_GET[$field]) || $_GET[$field]==0)) {
	            $where_parts[] = "$field = '$_GET[$field]%'";
	        }
	    }	        
	    $where = implode(' AND ',$where_parts);
	    if ($where) $where = ' AND '.$where;
	    
	    // добавляем параметры сортировки
	    if (in_array(@$_GET['sort_direction'],array('asc','desc')) && in_array(@$_GET['sort_name'],array('up.code','title','fio','status','buy_date','price','price_sum'))) {
	    	$order = "ORDER BY {$_GET['sort_name']} {$_GET['sort_direction']}";
	    } else {
	    	$order = 'ORDER BY buy_date DESC, up.code, status DESC';
	    }
	    
	    // получаем данные
	    $basic_sql = str_replace('/*WHERE*/',$where.' /*WHERE*/',$basic_sql);
	    $data = $this->objectModel->query($basic_sql.' '.$order);

	    // настраиваем Pager
	    $params = array(
	    	'path'		 => SITE_SUBDIR.'/global',
	    	'fileName'   => 'purchase_log',
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
		    'count'=>$this->objectModel->getCount('user_purchase',false),
			'links'=>$this->objectPager->getLinks()
		);
	
	}
	
}

?>
