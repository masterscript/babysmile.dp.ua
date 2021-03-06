<?php
function sale_block($obj) {
    	
	$items = db::getDB()->select('
		SELECT i.id,name,url,title,price,price_old,filename AS img_src, i.type FROM items i
		JOIN goods g ON i.id = g.id
		LEFT JOIN top_images ti ON ti.id = i.id 
		WHERE sale_flag = 1 AND protected<=?d ORDER BY sort',user::getAccessLevel()
    );
    
    foreach ($items as $item) {
    	if ($item['type']=='good_set' && empty($item['price'])) {
    		// расчитываем цену набора по сумме цены товаров, входящих в набор
    		$item['price'] = db::getDB()->selectCell('
    			SELECT SUM(price) FROM goods g
    			JOIN items i ON g.id = i.id
    			WHERE i.pid = ?',$item['id']);
    	}
		$objectItems[]=new page($item);
	}
		
	return $objectItems;
	
}
?>