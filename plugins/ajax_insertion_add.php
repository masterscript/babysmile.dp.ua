<?php

require_once 'classes/Admin/Errors.php';
require_once 'classes/Library/Images.php';
require_once 'Mail.php';
require_once 'Mail/mime.php';

function ajax_insertion_add () {
    
	ini_set("display_errors","Off");
	
    @session_start();

    try {
    	
    	// загрузка изображений
    	$file_names = array();
	    if (isset($_FILES['photo'])) {
	    	
	    	$files = $_FILES['photo'];
	    	
		    foreach ($files['tmp_name'] as $key=>$tmp_file) {
		    	
		    	// проверка типа изображения
		    	try {
		    		$image = new _Images($tmp_file);
		    	} catch (Admin_ImagesException $e) {
		    		if ($e->getCode()==_Images::ERROR_TYPE_MISMATCH)
		    			throw new FormException($e->getMessage(),'photo[]',$key);
		    	}		    	
		    	
		    	// проверка размера
		    	if ($files['size'][$key]>150*1024)
		    		throw new FormException('Превышен размер файла (более 150 Кб)','photo[]',$key);
		    	
		    	if (empty($files['name'][$key])) continue;
		    		
		    	// генерация уникального имени файла
		    	$tmp_name = tempnam(FRONT_SITE_PATH.'/i/insertions/',date('YmdHis'));
		    	@unlink($tmp_name);
		    	$dest = $tmp_name.'.'.$image->getType();
		    	
		    	// перемещение файла в конечную директорию
		    	if (!move_uploaded_file($tmp_file,$dest))
		    		throw new Exception('Не удалось переместить временный файл в директорию '.$dest);
		    		
		    	// запись в массив
		    	$file_names[] = basename($dest);
		    	
		    }
		    
	    }
	    
	    // обработка остальных полей
	    $pid = intval($_POST['pid']);
	    $type_id = intval($_POST['type']);
	    $region_id = intval($_POST['region']);
	    $author = $_POST['author'];
	    $phone = $_POST['phone'];
	    $email = $_POST['email'];
	    $text = $_POST['text'];
	    $expire_date = $_POST['date'];
	    
	    if (empty($author))
	    	throw new FormException('Необходимо указать контактное лицо','author');
	    if (empty($phone))
	    	throw new FormException('Необходимо указать номер телефона','phone');
	    if (!preg_match('#^(\S+)[a-z0-9._-]+@([a-z0-9.-]+$)#is',$email))
    		throw new FormException('Необходимо указать правильный адрес электронной почты','email');
    	if (empty($text))
	    	throw new FormException('Объявление должно содержать текст','text');
	    
	    if (!preg_match('#^\d{4}-\d{2}-\d{2}$#',$expire_date))
	    	throw new FormException('Неправильный формат даты','date');
	    $date_parts = explode('-',$expire_date);
//	    $time = mktime(0,0,0,$date_parts[1],$date_parts[2],$date_parts[0]);
	    if (!checkdate($date_parts[1],$date_parts[2],$date_parts[0]))
	    	throw new FormException('Неправильный формат даты','date');
	    
	    // запись в БД
	    $next_id = db::getDB()->selectCell('select max(last_insert_id(id+1)) from items');
	    $url = db::getDB()->selectCell('SELECT url FROM items WHERE id = ?d',$pid).'/'.$next_id;
	    db::getDB()->query('INSERT INTO items (pid,url,type,create_date,name,title) VALUES(?d,?,?,NOW(),?,?)',$pid,$url,'insertion','Объявление','Объявление');
	    
	    $next_id = mysql_insert_id();
	    db::getDB()->query('
	    	INSERT INTO insertions (id,region_id,type_id,text,author,email,phone,expire_date)
	    	VALUES(?d,?d,?d,?,?,?,?,?)',$next_id,$region_id,$type_id,$text,$author,$email,$phone,$expire_date);

	    foreach ($file_names as $fname) {
	    	db::getDB()->query('INSERT INTO insertion_photos(insertion_id,filename) VALUES(?d,?)',$next_id,$fname);
	    }
		
		$headers = array(
	                  'From'    => '=?windows-1251?B?'.base64_encode(iconv("UTF-8", "WINDOWS-1251",'Автоматическая система')).'?=',
	                  'X-Mailer'=> 'PHP/'.phpversion(),
	                  'To'      => '=?windows-1251?B?'.base64_encode(iconv("UTF-8", "WINDOWS-1251",'Администратор сайта')).'?= <babysmile@ua.fm>',
	                  'Subject' => '=?windows-1251?B?'.base64_encode(iconv("UTF-8", "WINDOWS-1251",'Новое объявление на сайте')).'?='
	                 ); 
		$body = iconv("UTF-8", "WINDOWS-1251","
			<h3>Новое объявление на сайте</h3>
			<p>IP адрес: {$_SERVER['REMOTE_ADDR']}</p>
			<p>Ссылка на объявление: <a href='http://admin.babysmile.dp.ua/view?id=$next_id'>http://admin.babysmile.dp.ua/view?id=$next_id</a></p>
		");
		
		$mailMime = new Mail_mime();
		$mailMime->setHTMLBody($body);
		$body = $mailMime->get(array('html_encoding'=>'windows-1251','head_charset'=>'windows-1251','html_charset'=>'windows-1251'));
		$headers = $mailMime->headers($headers);
		
		$mail = Mail::factory('mail');
		$mail->send('babysmile@ua.fm', $headers, $body);
	    
	    echo json_encode(array('is_errors'=>0));
	    
    } catch (FormException $e) {
    	echo json_encode(array('is_errors'=>1, 'error_msg'=>$e->getMessage(), 'field'=>$e->getFieldName(), 'index'=>$e->getFieldIndex()));
    } catch (Exception $e) {
    	echo json_encode(array('is_errors'=>1, 'error_msg'=>$e->getMessage()));
    }
    
}

?>