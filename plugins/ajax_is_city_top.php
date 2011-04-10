<?php

function ajax_is_city_top() {
    
	ini_set("display_errors","Off");
    @session_start();

    $data = array('top'=>db::getDB()->selectCell('SELECT top FROM items WHERE id = ?d',$_GET['city_id']));
    
    echo json_encode($data);
    
}
