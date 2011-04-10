<?php

function ajax_get_cities() {
    
	ini_set("display_errors","Off");
    @session_start();

    $cities = db::getDB()->select('SELECT id,name FROM items WHERE pid = ?d AND template = ? ORDER BY name',$_GET['region_id'],'city');
    
    echo json_encode($cities);
    
}
