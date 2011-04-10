<?php
function getInsertions($obj) {

	// формируем условие фильтра
	$filter = '';
	if (isset($_GET['doFilter'])) {
		
		if (isset($_GET['region']) && !empty($_GET['region']))
			$filter .= ' AND region_id = '.intval($_GET['region']);
			
		if (isset($_GET['type']) && !empty($_GET['type']))
			$filter .= ' AND type_id = '.intval($_GET['type']);
			
		if (isset($_GET['text']) && !empty($_GET['text']))
			$filter .= " AND text LIKE '%".mysql_real_escape_string($_GET['text'])."%'";
		
	}
	
    if ($obj->issetParam('count') && $obj->issetParam('numerator_name')) {
        $obj->setNumerator(
            db::getDB()->selectCell('
                SELECT count(i.id) from ?_items as i
                JOIN insertions ins ON i.id = ins.id
                WHERE (type = ?) AND pid = ? and protected<=?d
                 and checked=1 and expire_date >= current_date()'.$filter,
                'insertion',$obj->getId(),user::getAccessLevel())
        );
    }
    
	$items = db::getDB()->select('
		SELECT i.url,i.create_date,ins.*,r.name region_name,ins_t.name type_name FROM items i
		JOIN insertions ins ON ins.id = i.id
		JOIN regions r ON r.id = ins.region_id
		JOIN insertion_types ins_t ON ins_t.id = ins.type_id
		WHERE (type = ?) AND pid = ? and protected<=?d and checked=1 and expire_date >= current_date()'.$filter.
		' ORDER BY create_date DESC {limit ?d,?d}','insertion',$obj->getId(),user::getAccessLevel(),$obj->getLimitFrom(),$obj->getLimitCount()
    );

    $objects = array();
    foreach ($items as $item) {
    	$objects[] = new Insertion($item);
    }
	return $objects;
	
}
?>