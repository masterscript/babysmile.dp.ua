<?php

function currency_change() {
    
    $code = $_GET['code'];
    $id = db::getDB()->selectCell('SELECT id FROM currency WHERE abbr = ?',$code);
    if ($id) {
    	$_SESSION['currency'] = $id;
    }
    
//    var_dump($_SERVER['HTTP_REFERER']);
    header('Location:'.$_SERVER['HTTP_REFERER']);
    
}

?>