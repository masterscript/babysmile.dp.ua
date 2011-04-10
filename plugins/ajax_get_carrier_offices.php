<?php

function ajax_get_carrier_offices() {
    
	ini_set("display_errors","Off");
    @session_start();

    $offices = db::getDB()->select('
	    	SELECT i.id,i.name FROM items i
	    	JOIN carrier_offices co ON co.id = i.id
			WHERE i.pid = ?d AND co.city_id = ?d AND i.template = ?',$_GET['carrier_id'],$_GET['city_id'],'carrier_office');
    
    echo json_encode($offices);
    
}
