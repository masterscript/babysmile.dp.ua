<?php
function getInsertion($obj) {
    
	$item = db::getDB()->selectRow('
		SELECT i.url,i.create_date,ins.*,r.name region_name,ins_t.name type_name FROM items i
		JOIN insertions ins ON ins.id = i.id
		JOIN regions r ON r.id = ins.region_id
		JOIN insertion_types ins_t ON ins_t.id = ins.type_id
		WHERE i.id = ?d and checked=1 and expire_date >= current_date()',$obj->getId()
    );
    
    if (empty($item)) return false;

	return new Insertion($item);
	
}
?>