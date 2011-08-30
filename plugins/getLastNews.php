<?php

/**
 * @param current_page $obj
 */
function getLastNews($obj) {
    
    $count = $obj->issetParam('count') ? $obj->getParam('count') : 5;
    
	$items = db::getDB()->select('
		SELECT description,create_date,i.id,name,url,title,filename AS img_src FROM items i
		LEFT JOIN top_images ti ON ti.id = i.id
		WHERE type = ? and protected<=?d ORDER BY create_date DESC {limit ?d}',
		'news',user::getAccessLevel(),$count
    );
    
    foreach ($items as $item) {
		$objectItems[]=new page($item);
	}
		
	return $objectItems;
}