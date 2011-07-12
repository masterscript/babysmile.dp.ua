<?php

function ajax_add_to_cart ()
{    
    @session_start();
    $last_index = count(@$_SESSION['cart']);
    $_POST['count'] = (int)$_POST['count'];
	
	if ($_POST['count']<=0 || $_POST['count']>=1000) {
		return ;
	}
	
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
	user::getCurrentUser()->calcCart();    
}
