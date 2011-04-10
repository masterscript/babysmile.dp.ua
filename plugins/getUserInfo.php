<?php
function getUserInfo($obj) {
    	    
	$props = db::getDB()->selectRow('
		SELECT
			u.*,carriers.id carrier_id,regions.id region_id, cities.top is_courier
		FROM users u
		JOIN items i ON i.id = u.id
		LEFT JOIN items offices ON offices.id = u.carrier_office
		LEFT JOIN carrier_offices co ON co.id = offices.id
		LEFT JOIN items carriers ON carriers.id = offices.pid
		LEFT JOIN items cities ON cities.id = u.city_id
		LEFT JOIN items regions ON regions.id = cities.pid
		WHERE i.id = ?d',user::getId()
    );
    
	return $props;
	
}
?>