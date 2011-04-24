<?php
function getGoodList($obj) {
    	
    if ($obj->issetParam('count') && $obj->issetParam('numerator_name')) {
        $obj->setNumerator(
            db::getDB()->selectCell('
                SELECT count(id) from ?_items as items WHERE (type = ? OR type = ? OR template = ?) AND pid = ? and protected<=?d',
                'good','good_set','subcategory',$obj->getId(),user::getAccessLevel())
        );
    }
    
	$items = db::getDB()->select('
		SELECT
			type,description,i.id,name,url,title,filename AS img_src,price,
			availability,IF(price_old>price,price_old,0) price_old
		FROM items i
		LEFT JOIN goods g ON i.id = g.id
		LEFT JOIN top_images ti ON ti.id = i.id 
		WHERE (type = ? OR type = ? OR template = ? OR template = ?) AND url LIKE ? and protected<=?d
		ORDER BY type, sort, create_date DESC {limit ?d,?d}',
		'good', 'good_set', 'subcategory', 'clothers_container', $obj->getUrl() . '/%', user::getAccessLevel(),$obj->getLimitFrom(),$obj->getLimitCount()
    );
    
    foreach ($items as $item) {
    	if ($item['type']=='good_set' && empty($item['price'])) {
    		// расчитываем цену набора по сумме цены товаров, входящих в набор
    		$item['price'] = db::getDB()->selectCell('
    			SELECT SUM(price) FROM goods g
    			JOIN items i ON g.id = i.id
    			WHERE i.pid = ?',$item['id']);
    	}
		$objectItems[] = new page($item);
	}
		
	return $objectItems;
	
}
?>
