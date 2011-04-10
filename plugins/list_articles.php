<?php
function list_articles($obj) {
    
    if ($obj->issetParam('count') && $obj->issetParam('numerator_name')) {
        $obj->setNumerator(
            db::getDB()->selectCell('
                SELECT count(id) from ?_items as items WHERE (template = ? OR type = ?) AND pid = ? and protected<=?d',
                $obj->getId(),'articles','article',user::getAccessLevel())
        );
    }
    
	$items = db::getDB()->select('
		SELECT description,name,url,title,type FROM items i
		WHERE (template = ? OR type = ?) AND pid = ? and protected<=?d ORDER BY type DESC,sort {limit ?d,?d}','articles','article',
		$obj->getId(),user::getAccessLevel(),$obj->getLimitFrom(),$obj->getLimitCount()
    );
    
    foreach ($items as $item) {
		$objectItems[]=new current_page($item['url']);
	}
		
	return $objectItems;
	
}
?>