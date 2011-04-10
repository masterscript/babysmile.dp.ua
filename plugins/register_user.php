<?php
function register_user ($obj) {
	
	require_once('recaptcha/recaptchalib.php');
	$publickey = "6Ld6FAsAAAAAAN0qgoDwLW5RtTCHuXSrs9Bfnlt5"; // you got this from the signup page
	$ret = array('captcha'=>recaptcha_get_html($publickey));
	
	if (isset($_POST['doRegister'])) {				
		
		// проверка полей
		$errors = array();
		
		$privatekey = "6Ld6FAsAAAAAAPVVzXxmfxNFSvIxW019Mt3oGKiD";
		$resp = recaptcha_check_answer ($privatekey,
                                $_SERVER["REMOTE_ADDR"],
                                $_POST["recaptcha_challenge_field"],
                                $_POST["recaptcha_response_field"]);
		
		if (!$resp->is_valid) {
		  $errors['captcha'] = true;
		}
		
		if (!preg_match('#^[a-zA-Z.\-_0-9]+$#',$_POST['login'])) {
			$errors['login'] = true;
		} else {
			$user_exists = db::getDB()->selectCell('SELECT COUNT(*) FROM items WHERE url = ?','/users/'.$_POST['login']);
			if ($user_exists>0) {
				$errors['login_exists'] = true;
			}
		}
		if (strlen($_POST['passw1'])<=3) {
			$errors['passw_short'] = true;
		}
		if ($_POST['passw1']!=$_POST['passw2']) {
			$errors['passw_compare'] = true;
		}
		if (!preg_match('#^(\S+)[a-z0-9._-]+@([a-z0-9.-]+$)#is',$_POST['email'])) {
			$errors['email'] = true;
		} else {
		    $email_exists = db::getDB()->selectCell('SELECT COUNT(*) FROM users WHERE email = ?',$_POST['email']);
			if ($email_exists>0) {
				$errors['email_exists'] = true;
			}
		}
		if (empty($_POST['name'])) $errors['name'] = true;
		if (empty($_POST['phone'])) $errors['phone'] = true;
		
		$valid_form = count($errors)===0;
		if ($valid_form) {
			db::getDB()->query(
				'INSERT INTO items (pid,url,type,create_date,mod_date,name,protected)
				 VALUES (7,?,?,?,?,?,2)','/users/'.$_POST['login'],'user',date('Y-m-d H:i:s'),date('Y-m-d H:i:s'),$_POST['name']);
			$user_id = mysql_insert_id();
			db::getDB()->query(
				'INSERT INTO users (id,fio,pass,email,phone)
				 VALUES (?,?,?,?,?)',$user_id,$_POST['name'],md5($_POST['passw1']),$_POST['email'],$_POST['phone']);
    		// вход пользователя
            /*unset ($_SESSION['user']);
    	    $user=user::init($_POST['login'],$_POST['passw1']);
    		if (user::getUserGroup()) {
    			session_name('SID');
    			$_SESSION['user'] = $user;
    		}
    		header('Location: http://'.$_SERVER['HTTP_HOST'].'/');
    		die();  */          
		}
		
		return array_merge($ret,array('valid_form'=>$valid_form,'errors'=>$errors));
		
	}
	
	return $ret;
	
}

?>