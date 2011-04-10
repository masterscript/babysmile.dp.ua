<?php

function ajax_cart_add () {
    
    @session_start();
    $last_index = count(@$_SESSION['cart']);
    $_POST['count'] = (int)$_POST['count'];
    if (!isset($_SESSION['cart']) || !count($_SESSION['cart'])) {    
	    $_SESSION['cart'][$last_index+1]['good_id'] = $_POST['good_id'];
	    $_SESSION['cart'][$last_index+1]['count'] = $_POST['count'];
    } else {
    	foreach ($_SESSION['cart'] as $key=>$cart) {
    		if ($cart['good_id']==$_POST['good_id']) {
    			$_SESSION['cart'][$key]['count'] += $_POST['count'];
    			break;
    		}
    		$_SESSION['cart'][$last_index+1]['good_id'] = $_POST['good_id'];
	    	$_SESSION['cart'][$last_index+1]['count'] = $_POST['count'];
    	}
    }
    
}

?>