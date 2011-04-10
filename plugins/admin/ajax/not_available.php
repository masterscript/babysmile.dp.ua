<?php

$objectDb = Admin_Core::getObjectDatabase();        
$id = (int)$_POST['id'];
$data = $objectDb->selectRow('
	SELECT
		uo.*,up.*,IFNULL(u.fio,anonym_name) fio,IFNULL(u.email,anonym_email) email,i.title,i.name,i.url,
    	g.availability
	FROM user_purchase up
	JOIN user_orders uo ON uo.code = up.code
	LEFT JOIN users u ON u.id = uo.user_id
    JOIN items i ON i.id = up.good_id
    JOIN goods g ON g.id = i.id 
	WHERE up.id = ?d',$id);
if ($data['availability']==1) {
	
	$objectDb->query('
		INSERT INTO goods_notifications SET good_id = ?d {,user_id = ?d} {,email =? }',
		$data['good_id'],$data['user_id']?$data['user_id']:DBSIMPLE_SKIP,$data['anonym_email']?$data['anonym_email']:DBSIMPLE_SKIP);
    $objectDb->query('UPDATE goods SET availability = 0 WHERE id = ?d',$data['good_id']);
	
	ini_set("display_errors","Off");
	$email = $data['email'];
    $headers = array(
    	'From'    => '=?windows-1251?B?'.base64_encode(iconv("UTF-8", "WINDOWS-1251",'babysmile.dp.ua')).'?=',
        'X-Mailer'=> 'PHP/'.phpversion(),
        'To'      => '=?windows-1251?B?'.base64_encode(iconv("UTF-8", "WINDOWS-1251",$data['fio'])).'?= <'.$email.'>',
        'Subject' => '=?windows-1251?B?'.base64_encode(iconv("UTF-8", "WINDOWS-1251",'Товар недоступен')).'?=',
	);
	        
    $body = "
        <p>Здравствуйте <strong>{$data['fio']}</strong>.</p>
        <p>Товар <a href='http://babysmile.dp.ua{$data['url']}'>{$data['name']}</a> на данный момент недоступен для заказа.</p>
        <p>Когда товар появится в наличии, Вы будете уведомлены об этом по электронной почте</p>";	    	
    $body .= "<p>С уважением, администрация сайта<br/>
			Web:	<a href='http://babysmile.dp.ua'>www.babysmile.dp.ua</a><br/>
			E-mail: <a href='mailto:babysmile@ua.fm'>babysmile@ua.fm</a><br/>
			г. Днепропетровск<br/>
			(056) 788-23-83<br/>
			(067) 561-82-52</p>";
    
    $mime = new Mail_mime();
	$mime->setHTMLBody(iconv("UTF-8", "WINDOWS-1251",$body));
        
    $mail = Mail::factory('mail');
    $body = $mime->get(array('html_encoding'=>'windows-1251','head_charset'=>'windows-1251','html_charset'=>'windows-1251','text_charset'=>'windows-1251'));
    $headers = $mime->headers($headers);
    $mail->send($email, $headers, $body);    
    
}
