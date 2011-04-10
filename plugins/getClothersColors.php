<?php
function getClothersColors($obj) {
    	
	return db::getDB()->select('
		SELECT DISTINCT color_name,color_hex
		FROM clothers cl
		JOIN items i ON cl.id = i.id
		WHERE i.pid = ?d AND protected<=?d
		ORDER BY color_name',$obj->getId(),user::getAccessLevel());
	
}
