<?php
function list_article($obj) {
    
    if ($obj->issetParam('count') && $obj->issetParam('numerator_name')) {
        $obj->setNumerator(
            db::getDB()->selectCell('
                SELECT count(id) from ?_items as items WHERE type = ? and pid = ?d and protected<=?d',
                'article',$obj->getId(),user::getAccessLevel())
        );
    }
    
	$items = db::getDB()->select('
		SELECT description,name,url,title FROM items i
		WHERE type = ? and pid = ?d and protected<=?d ORDER BY sort {limit ?d,?d}','article',$obj->getId(),
		user::getAccessLevel(),$obj->getLimitFrom(),$obj->getLimitCount()
    );
    
    foreach ($items as $item) {
		$objectItems[]=new page($item);
	}
		
	return $objectItems;
	
}
?>