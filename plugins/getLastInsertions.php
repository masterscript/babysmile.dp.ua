<?php
function getLastInsertions($obj) {
    
	$items = db::getDB()->select('
		SELECT i.url,i.create_date,ins.*,r.name region_name,ins_t.name type_name, ins_cat.name cat_name,
			ins_cat.url cat_url
		FROM items i
		JOIN items ins_cat ON ins_cat.id = i.pid
		JOIN insertions ins ON ins.id = i.id
		JOIN regions r ON r.id = ins.region_id
		JOIN insertion_types ins_t ON ins_t.id = ins.type_id
		WHERE (i.type = ?) and i.protected<=?d and checked=1 and expire_date >= current_date()
		ORDER BY i.create_date DESC LIMIT 0,3','insertion',user::getAccessLevel()
    );

    $objects = array();
    foreach ($items as $item) {
    	$objects[] = new Insertion($item);
    }
	return $objects;
	
}
?>