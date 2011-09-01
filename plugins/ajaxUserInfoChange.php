<?php

require_once 'classes/Admin/Errors.php';

function ajaxUserInfoChange() {
    
    @session_start();

    try {
    	
	    $fio = $_POST['fio'];
	    $email = $_POST['email'];
	    $birthday = $_POST['birthday'];
	    $sex = $_POST['sex'];
	    $phone = $_POST['phone'];
	    $phone_home = $_POST['phone_home'];
	    $city_id = intval($_POST['city_id']);
	    $address = $_POST['address'];
	    $houseNum = $_POST['house_number'];
	    $appNum = $_POST['app_number'];
	    $floor = (int)$_POST['floor'];
	    $intercom = $_POST['intercom'];
	    $delivery = $_POST['delivery'];
	    $carrier_office = intval($_POST['carrier_office']);
	    
	    $isCourier = db::getDB()->selectCell('SELECT top FROM ?_items WHERE id = ?d',$city_id);
	    
	    if (empty($fio))
	    	throw new FormException('Необходимо указать имя','fio');
	    if (empty($phone))
	    	throw new FormException('Необходимо указать номер телефона','phone');
	    if (!preg_match('#^(\S+)[a-z0-9._-]+@([a-z0-9.-]+$)#is',$email))
    		throw new FormException('Необходимо указать правильный адрес электронной почты','email');
	    if (!preg_match('#^\d{4}-\d{2}-\d{2}$#',$birthday))
	    	throw new FormException('Неправильный формат даты','birthday');
	    if ($floor<1 || $floor>1000)
	    	throw new FormException('Укажите число от 1 до 1000','floor');
	    if (!$carrier_office && $delivery=='carrier')
	    	throw new FormException('Отсутствуют офисы компании перевозчика в выбранном вами регионе. Укажите другой способ доставки','delivery');
	    if (!$isCourier && $delivery=='courier')
	    	throw new FormException('Доставка курьером осуществляется только по Днепропетровску. Укажите другой способ доставки','delivery');
	    	
	    $date_parts = explode('-',$birthday);
	    if (!checkdate($date_parts[1],$date_parts[2],$date_parts[0]))
	    	throw new FormException('Неправильный формат даты','birthday');

	    if (!empty($_POST['pass'])) {
	    	$pass = $_POST['pass'];
	    	$pass2 = $_POST['pass2'];
	    	if ($pass!=$pass2 || strlen($pass)<3) {
	    		throw new FormException('Пароли должны совпадать. Пароль должен содержать не менее 3 символов','pass');
	    	}
	    } else {
	    	$pass = false;
	    }
	    
	    if ($delivery!='carrier') {
	    	$carrier_office = 0;
	    }
	    
	    db::getDB()->query(
	    	'UPDATE users SET fio = ?, email = ?, birthday = ?, sex = ?, phone = ?, phone_home = ?, city_id = ?d,	    		
	    		address = ?, house_number = ?, app_number = ?, floor = ?, intercom = ?,
	    		delivery = ?, carrier_office = ?d{, pass = MD5(?)}
	    	WHERE id = ?d',
	    	$fio,$email,$birthday,$sex,$phone,$phone_home,$city_id,
	    	$address,$houseNum,$appNum,$floor,$intercom,
	    	$delivery,$carrier_office,$pass?$pass:DBSIMPLE_SKIP,
	    	user::getId()
	    );
	    
	    echo json_encode(array('is_errors'=>0));
	    
    } catch (FormException $e) {
    	echo json_encode(array('is_errors'=>1, 'error_msg'=>$e->getMessage(), 'field'=>$e->getFieldName(), 'index'=>$e->getFieldIndex()));
    } catch (Exception $e) {
    	echo json_encode(array('is_errors'=>1, 'error_msg'=>$e->getMessage()));
    }
    
}