<?php

$objectDb = Admin_Core::getObjectDatabase();        
if ($_POST['status']=='delete') {
	$order_code = $objectDb->selectCell('SELECT `code` FROM user_purchase WHERE id = ?d',$_POST['id']);
    $objectDb->query('DELETE FROM user_purchase WHERE id = ?d',$_POST['id']);
    if (!$objectDb->selectCell('SELECT COUNT(*) FROM user_purchase WHERE code = ?',$order_code)) {
    	$objectDb->query('DELETE FROM user_orders WHERE code = ?',$order_code);
    }
} else {
    $objectDb->query('UPDATE user_purchase SET status = ?d WHERE id = ?d',$_POST['status'],$_POST['id']);
    require_once 'Mail.php';
    
    $data = $objectDb->selectRow('
    	SELECT
    		p.code,u.id user_id,IFNULL(i_u.name,anonym_name) user_name,IFNULL(u.email,anonym_email) email,g.name good_name, g.url
    	FROM user_purchase p
    	JOIN user_orders uo ON uo.code = p.code
    	LEFT JOIN items i_u ON i_u.id = uo.user_id
    	LEFT JOIN users u ON u.id = i_u.id
    	JOIN items g ON g.id = p.good_id
    	WHERE p.id = ?d',$_POST['id']);
    
	switch ($_POST['status']) {
    	case 0: $data['status'] = 'находится в обработке'; break;
    	case 1: $data['status'] = 'принят и находится на исполнении'; break;
    	case 2: $data['status'] = 'выполнен'; break;
    }
    
    $recipients = $data['email'];
    $headers = array(
    	'From'    => '=?windows-1251?B?'.base64_encode(iconv("UTF-8", "WINDOWS-1251",'babysmile.dp.ua')).'?=',
        'X-Mailer'=> 'PHP/'.phpversion(),
        'To'      => '=?windows-1251?B?'.base64_encode(iconv("UTF-8", "WINDOWS-1251",$data['user_name'])).'?='.$data['email'],
        'Subject' => '=?windows-1251?B?'.base64_encode(iconv("UTF-8", "WINDOWS-1251",'Изменение статуса заказа')).'?=',
    );
        
    $body = "
        <p>Здравствуйте, <strong>{$data['user_name']}</strong>.</p>
        <p>Ваш заказ №{$data['code']} по товару <a href='http://babysmile.dp.ua{$data['url']}'>{$data['good_name']}</a> {$data['status']}.</p>";
    
	switch ($_POST['status']) {
    	case 0: $body .= '<p>С Вами свяжется наш менеджер для уточнения сроков и условий исполнения заказа.</p>'; break;
    	case 1: $body .= '<p>С Вами свяжется наш менеджер для уточнения сроков и условий исполнения заказа.</p>'; break;
    	case 2: $body .= '<p>Спасибо за использование нашего портала. Надеемся на дальнейшее сотрудничество.</p>'; break;
    }
        
	if ($data['user_id']) {
    	$body .= "Информацию о заказах можно отслеживать на <a href='http://babysmile.dp.ua/user-info'>странице пользователя</a>";
    }
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
    $mail->send($recipients, $headers, $body);
    
}
