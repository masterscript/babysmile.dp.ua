<?php
function getRegions($obj) {
    	
	$items = db::getDB()->select('SELECT * FROM regions ORDER BY name ASC');    		
	return $items;
	
}
?>