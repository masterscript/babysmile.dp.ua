<?php
function list_inscat($obj) {
    	
    if ($obj->issetParam('count') && $obj->issetParam('numerator_name')) {
        $obj->setNumerator(
            db::getDB()->selectCell('
                SELECT count(id) from ?_items as items WHERE (template = ?) AND pid = ? and protected<=?d',
                'insertion_cat',$obj->getId(),user::getAccessLevel())
        );
    }
    
	$items = db::getDB()->select('
		SELECT i.type,i.description,i.id,i.name,i.url,i.title,filename AS img_src,COUNT(t_ins.id) AS c_ins
    	FROM items i
		LEFT JOIN top_images ti ON ti.id = i.id
    	LEFT JOIN items ins ON (ins.pid = i.id AND ins.type=?)
    	LEFT JOIN insertions t_ins ON t_ins.id = ins.id AND t_ins.expire_date>=CURDATE()
		WHERE (i.template = ?) AND i.pid = ?d AND i.protected<=?d 
    	GROUP BY i.id
		ORDER BY i.sort  {LIMIT ?d,?d}',
		'insertion','insertion_cat',$obj->getId(),user::getAccessLevel(),
		$obj->getLimitFrom(),$obj->getLimitCount()
    );
    
    foreach ($items as $item) {
		$objectItems[] = new page($item);
	}
		
	return $objectItems;
	
}
?>