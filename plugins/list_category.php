<?php
function list_category($obj) {
    
    if ($obj->issetParam('count') && $obj->issetParam('numerator_name')) {
        $obj->setNumerator(
            db::getDB()->selectCell('
                SELECT count(id) from ?_items as items WHERE (template = ? OR type = ?) AND pid = ? and protected<=?d',
                'subcategory','good',$obj->getId(),user::getAccessLevel())
        );
    }	
	$items = db::getDB()->select('
		SELECT
			description,i.id,name,url,title,filename AS img_src,type,price,
			IF(price_old>price,price_old,0) price_old, availability
		FROM items i
		LEFT JOIN top_images ti ON ti.id = i.id
		LEFT JOIN goods g ON g.id = i.id 
		WHERE (template = ? OR type = ?) AND pid = ? and protected<=?d
		ORDER BY sort,create_date DESC {limit ?d,?d}','subcategory','good',$obj->getId(),user::getAccessLevel(),$obj->getLimitFrom(),$obj->getLimitCount()
    );    
    
    foreach ($items as $item) {
		$objectItems[]=new page($item);
	}
		
	return $objectItems;
	
}
?>
