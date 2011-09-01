<?php

function ajaxUserRegister($page)
{
	$response = array();
	if ($_POST) {				
		
		$email = $_POST['login'];
		$pass = $_POST['pass'];
		$passAgain = $_POST['pass_again'];
		
		$errors = array();
		
		if (!preg_match('#^(\S+)[a-z0-9._-]+@([a-z0-9.-]+$)#is',$email)) {
			$errors[] = 'Неправильный формат почтового ящика';
		} else {
		    $email_exists = db::getDB()->selectCell('SELECT COUNT(*) FROM users WHERE email = ?',$email);
			if ($email_exists>0) {
				$errors[] = 'Пользователь с таким почтовым ящиком уже существует';
			}
		}
		if (strlen($pass)<=3) {
			$errors[] = 'Пароль должен быть длинее 3 символов';
		}
		if ($pass != $passAgain) {
			$errors[] = 'Введенные пароли не совпадают';
		}
		
		if (empty($errors)) {
			$login = str_replace(array('@', '.'), '-', $email);
			db::getDB()->query(
				'INSERT INTO items (pid,url,type,create_date,mod_date,protected)
				 VALUES (7,?,?,NOW(),NOW(),2)', '/users/'.$login, 'user');
			$user_id = mysql_insert_id();
			db::getDB()->query(
				'INSERT INTO users (id, pass, email)
				 VALUES (?,?,?)',$user_id, md5($pass), $email);
			
            unset($_SESSION['user']);
    	    $user=user::init($email, $pass);
    		if (user::getUserGroup()) {
    			session_name('SID');
    			$_SESSION['user'] = $user;
    		}
    		$response['returnUrl'] = $page->getUrl();
		}
		
		$response['error'] = implode(".\n", $errors);
		
	}
	
	echo json_encode($response);
}