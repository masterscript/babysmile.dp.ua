<?php

function ajax_del_from_cart () {
    
    @session_start();
    foreach ($_SESSION['cart'] as $key=>$cart) {
    	if ($cart['good_id']==$_POST['good_id']) {
    		unset($_SESSION['cart'][$key]);
    		break;
    	}
    }
    if (!count($_SESSION['cart'])) unset($_SESSION['cart']);
    	
}

?>