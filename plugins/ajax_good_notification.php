<?php

require_once 'classes/Admin/Errors.php';

function ajax_good_notification() {
    
	ini_set("display_errors","Off");
	
    @session_start();
    
    try {
    	
    	if (user::getId()) {
    		
    		$good_id = (int)$_POST['good_id'];
    		
    		$isNotificated = db::getDB()->selectCell('SELECT COUNT(*) FROM goods_notifications WHERE good_id = ?d AND user_id = ?d',$good_id,user::getId());
	    	if ($isNotificated)
	    		throw new FormException('Слежение за этим товаром уже установлено','email');
	
	    	db::getDB()->query('
	    		INSERT INTO goods_notifications (good_id,user_id)
	    		VALUES (?d,?d)',$good_id,user::getId()
	    	);
    		
    	} else {
    		
	    	$email = $_POST['email'];
	    	$good_id = (int)$_POST['good_id'];
		    
		    if (!preg_match('#^(\S+)[a-z0-9._-]+@([a-z0-9.-]+$)#is',$email))
	    		throw new FormException('Необходимо указать правильный адрес электронной почты','email');
	    		
	    	$isNotificated = db::getDB()->selectCell('SELECT COUNT(*) FROM goods_notifications WHERE good_id = ?d AND email = ?',$good_id,$email);
	    	if ($isNotificated)
	    		throw new FormException('Слежение за этим товаром уже установлено для указаного e-mail','email');
	
	    	db::getDB()->query('
	    		INSERT INTO goods_notifications (good_id,email)
	    		VALUES (?d,?)',$good_id,$email
	    	);
	    	
    	}
    	
        echo json_encode(array('is_errors'=>0));
	        
    } catch (FormException $e) {    	
    	echo json_encode(array('is_errors'=>1, 'error_msg'=>$e->getMessage(), 'field'=>$e->getFieldName(), 'index'=>$e->getFieldIndex()));
    } catch (Exception $e) {
    	echo json_encode(array('is_errors'=>1, 'error_msg'=>$e->getMessage()));
    }
    
}

?>