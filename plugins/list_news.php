<?php
function list_news($obj) {
    
    if ($obj->issetParam('count') && $obj->issetParam('numerator_name')) {
        $obj->setNumerator(
            db::getDB()->selectCell('
                SELECT count(id) from ?_items as items WHERE type = ? and protected<=?d',
                'news',user::getAccessLevel())
        );
    }
    
	$items = db::getDB()->select('
		SELECT description,create_date,i.id,name,url,title,filename AS img_src FROM items i
		LEFT JOIN top_images ti ON ti.id = i.id
		WHERE type = ? and protected<=?d ORDER BY create_date DESC {limit ?d,?d}','news',user::getAccessLevel(),$obj->getLimitFrom(),$obj->getLimitCount()
    );
    
    foreach ($items as $item) {
		$objectItems[]=new page($item);
	}
		
	return $objectItems;
	
}
?>