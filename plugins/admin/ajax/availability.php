<?php

ini_set("display_errors","Off");
$objectDb = Admin_Core::getObjectDatabase();        
if ($_POST['id']) {
    $id = (int)$_POST['id'];
    $value = $objectDb->getItem('availability', 'goods', $id);
    if ($value==1) {    	
        $value = 0;
        // отсылаем уведомление всем пользователям, заказавшим данный товар
        $observers = $objectDb->select('
			SELECT
				uo.*,up.*,IFNULL(u.fio,anonym_name) fio,IFNULL(u.email,anonym_email) email,
				i.title,i.name,i.url,g.availability
			FROM user_purchase up
			JOIN user_orders uo ON uo.code = up.code
			LEFT JOIN users u ON u.id = uo.user_id
		    JOIN items i ON i.id = up.good_id
		    JOIN goods g ON g.id = i.id 
			WHERE up.good_id = ?d AND up.status = 0',$id);
        
    	foreach ($observers as $data) {
    		$objectDb->query('
				INSERT INTO goods_notifications SET good_id = ?d {,user_id = ?d} {,email =? }',
				$data['good_id'],$data['user_id']?$data['user_id']:DBSIMPLE_SKIP,$data['anonym_email']?$data['anonym_email']:DBSIMPLE_SKIP);
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
        
    } else {    	
        $value = 1;
        $observers = $objectDb->selectCol('
        	SELECT IFNULL(u.email,gn.email) email
        	FROM goods_notifications gn
        	LEFT JOIN users u ON u.id = gn.user_id
        	WHERE gn.good_id = ?d',$id);
        $data = $objectDb->selectRow('SELECT * FROM goods g JOIN items i ON i.id=g.id WHERE i.id = ?d',$id);
        foreach ($observers as $email) {
        	if ($email) {
			    $headers = array(
			    	'From'    => '=?windows-1251?B?'.base64_encode(iconv("UTF-8", "WINDOWS-1251",'babysmile.dp.ua')).'?=',
			        'X-Mailer'=> 'PHP/'.phpversion(),
			        'To'      => $email,
			        'Subject' => '=?windows-1251?B?'.base64_encode(iconv("UTF-8", "WINDOWS-1251",'Товар доступен')).'?=',
			    );
			        
			    $body = "
			        <p>Здравствуйте.</p>
			        <p>Товар <a href='http://babysmile.dp.ua{$data['url']}'>{$data['name']}</a> появился в наличии.</p>";
			    
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
        }
        $objectDb->query('DELETE FROM goods_notifications WHERE good_id = ?d',$id);
    }
    $objectDb->query('UPDATE goods SET availability = ?d WHERE id = ?d',$value,$id);

    echo $value;

}    	

?>