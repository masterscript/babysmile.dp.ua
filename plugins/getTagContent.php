<?php
function getTagContent($obj) {
    
	$tag_id = db::getDB()->selectCell('SELECT id FROM tags WHERE name=?',@$_GET['word']);
	if (!$tag_id) return array();
	
    if ($obj->issetParam('count') && $obj->issetParam('numerator_name')) {
        $obj->setNumerator(
            db::getDB()->selectCell('
                SELECT count(id) from ?_items as items WHERE (type = ? OR type = ? OR template = ?) AND protected<=?d
                AND id IN (SELECT item_id FROM tags_items WHERE tag_id = ?d)',
                'good','good_set','subcategory',$obj->getId(),user::getAccessLevel(),$tag_id)
        );
    }
    
	$items = db::getDB()->select('
		SELECT type,description,i.id,name,url,title,filename AS img_src,price,availability,price_old FROM items i
		LEFT JOIN goods g ON i.id = g.id
		LEFT JOIN top_images ti ON ti.id = i.id 
		WHERE (type = ? OR type = ? OR template = ?) AND protected<=?d
		AND i.id IN (SELECT item_id FROM tags_items WHERE tag_id = ?d)
		ORDER BY type, sort {limit ?d,?d}','good','good_set','subcategory',user::getAccessLevel(),$tag_id,$obj->getLimitFrom(),$obj->getLimitCount()
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