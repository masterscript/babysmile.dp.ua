<?php
function list_catalog($obj) {
    
    if ($obj->issetParam('count') && $obj->issetParam('numerator_name')) {
        $obj->setNumerator(
            db::getDB()->selectCell('
                SELECT count(items.id) from items WHERE template = ? and protected<=?d',
                'category',user::getAccessLevel())
        );
    }	
	$items = db::getDB()->select('
		SELECT description,i.id,name,url,title,filename AS img_src FROM items i
		LEFT JOIN top_images ti ON ti.id = i.id 
		WHERE template = ? and protected<=?d ORDER BY sort {limit ?d,?d}','category',user::getAccessLevel(),$obj->getLimitFrom(),$obj->getLimitCount()
    );
    
    foreach ($items as $item) {
		$objectItems[]=new page($item);
	}
		
	return $objectItems;
	
}
?>