<?php
function getInstypes($obj) {
    	
	$items = db::getDB()->select('SELECT * FROM insertion_types ORDER BY name ASC');    		
	return $items;
	
}
?>