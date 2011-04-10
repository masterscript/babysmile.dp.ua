<?php
function getUserOrders($obj) {
	  
	$items = db::getDB()->select('
		SELECT p.*,i.name,i.url FROM user_purchase p
		JOIN items i ON i.id = p.good_id
		JOIN user_orders uo ON uo.code = p.code
		WHERE uo.user_id = ?d
		ORDER BY buy_date DESC',user::getId()
    );
    
    $orders = array();
    foreach ($items as $k=>$v) {
    	if (!isset($orders[$v['code']])) {
    		$orders[$v['code']] = array();
    	}
    	$orders[$v['code']][] = $v;
    }
    
    return $orders;
	
}
?>