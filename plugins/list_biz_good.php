<?php
function list_biz_good($obj) {
    
	$item_url = db::getDB()->selectCell('SELECT url FROM items WHERE id = ?d',$_GET['id']);
	
    if ($obj->issetParam('count') && $obj->issetParam('numerator_name')) {
		$obj->setNumerator(
			db::getDB()->selectCell('
				SELECT count(items.id) from goods,items WHERE goods.id=items.id and type = ?
				AND biz_id = ? {AND url LIKE ?} AND protected<=?d',
				'good',$obj->getId(),$item_url?$item_url.'/%':DBSIMPLE_SKIP,user::getAccessLevel())
		);
	}
    
	$items = db::getDB()->select('
		SELECT
			description,i.id,name,url,title,filename AS img_src,price,availability,
			IF(price_old>price,price_old,0) price_old
		FROM items i
		JOIN goods g ON i.id = g.id
		LEFT JOIN top_images ti ON ti.id = i.id 
		WHERE type = ? AND biz_id = ? {AND url LIKE ?} AND protected<=?d ORDER BY sort,create_date DESC {limit ?d,?d}',
		'good',$obj->getId(),$item_url?$item_url.'/%':DBSIMPLE_SKIP,user::getAccessLevel(),$obj->getLimitFrom(),$obj->getLimitCount()
    );
    
    foreach ($items as $item) {
		$objectItems[]=new page($item);
	}
		
	return $objectItems;
	
}
?>