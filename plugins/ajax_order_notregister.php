<?php

require_once 'classes/Admin/Errors.php';
require_once 'Mail.php';
require_once 'Mail/mime.php';

function ajax_order_notregister() {
    
	//ini_set("display_errors","Off");
	
    @session_start();
    
    try {
    	
	    if ($_POST && isset($_SESSION['cart'])) {
	    	
	    	$phone = $_POST['phone'];
	    	$email = $_POST['email'];
	    	$name = $_POST['name'];
		    $notes = $_POST['note'];
		    
		    if (empty($name))
		    	throw new FormException('Необходимо указать имя','name');
		    if (empty($phone))
		    	throw new FormException('Необходимо указать номер телефона','phone');
		    if (!preg_match('#^(\S+)[a-z0-9._-]+@([a-z0-9.-]+$)#is',$email))
    			throw new FormException('Необходимо указать правильный адрес электронной почты','email');
	
		    // сохраняем заказ пользователя
	    	db::getDB()->query('
	    		INSERT INTO user_orders (phone,anonym_name,anonym_email,notes)
	    		VALUES (?,?,?,?)',$phone,$name,$email,$notes
	    	);
	    	
	    	$order_id = mysql_insert_id();
	    	$order_code = $order_id;
	    	
	    	db::getDB()->query('UPDATE user_orders SET code = ? WHERE id = ?d',$order_code,$order_id);
	    	
	    	foreach ($_SESSION['cart'] as $cart) {
	    		db::getDB()->query('
	    			INSERT INTO user_purchase (code,good_id,count,buy_date)
	    			VALUES (?,?d,?d,?)
	    		',$order_code,$cart['good_id'],$cart['count'],date('Y-m-d H:i:s'));
	    	}
	    	
	    	// посылаем письмо админу
	    	$recipients = 'babysmile@ua.fm';
	        $headers['From']    = 'robot@babysmile.dp.ua';
	        $headers['To']      = 'babysmile@ua.fm';
	        $headers['Subject'] = 'Baby Smile Order';
	        $body = iconv("UTF-8", "WINDOWS-1251","
	        	Пользователь $name выполнил заказ на сайте.
	        	Время заказа: ".date('Y-m-d H:i:s').".
	        	Подробную информацию о заказе можно просмотреть по адресу: http://admin.babysmile.dp.ua/global/purchase_log?code=$order_code
	        ");
	        
	        $mail = Mail::factory('mail');
	        $mail->send($recipients, $headers, $body);
	        
	        // посылаем письмо пользователю
	        $recipients = $email;
	        $headers = array(
		    	'From'    => '=?windows-1251?B?'.base64_encode(iconv("UTF-8", "WINDOWS-1251",'babysmile.dp.ua')).'?=robot@babysmile.dp.ua',
		        'X-Mailer'=> 'PHP/'.phpversion(),
		        'To'      => '=?windows-1251?B?'.base64_encode(iconv("UTF-8", "WINDOWS-1251",$name)).'?='.$email,
		        'Subject' => '=?windows-1251?B?'.base64_encode(iconv("UTF-8", "WINDOWS-1251",'Заказ на сайте babysmile.dp.ua')).'?=',
		    );
            $body = "
                <p>Уважаемый <strong>$name</strong>!</p>
				<p>Благодарим за то, что Вы воспользовались услугами нашего Web-портала.</p>
                <p>Код заказа: $order_code.</p>
                <p>Товары:</p>";
            
            $goods = db::getDB()->select('
		    	SELECT g.name, g.url
		    	FROM user_purchase p
		    	JOIN items g ON g.id = p.good_id
		    	WHERE p.code = ?',$order_code);
		    foreach ($goods as $good) {
		    	$body .= "<li><a href='http://babysmile.dp.ua{$good['url']}'>{$good['name']}</a></li>";
		    }
            
            $mime = new Mail_mime();
            
	        $body .= "<p>Напоминаем Вам, что:</p>
	        		  <p>
						1. После поступления денежных средств на расчетный счет, менеджер свяжется с Вами для согласования всех условий и сроков доставки<br/>
						2. Доставка товара осуществляестя в течении 1-4 рабочих дней с момента согласованного отправления Товара любым выбранным Вами перевозчиком (<a href='http://www.autolux.ua/'>Автолюкс</a>, <a href='http://www.intime.com.ua/'>Интайм</a>, <a href='http://www.delivery-auto.com.ua/'>Деливери</a>, <a href='http://www.novaposhta.com.ua/'>Нова Пошта</a>,  и др.)<br/> 
						3. После отправки товара, менеджер сообщит Вам номер транспортной декларации отправленного товара. Получатель Товара должен знать номер декларации и обязан иметь при себе паспорт для его получения<br/>
						4. Оплата за доставку осуществляется при получении Товара. Тариф на оплату устанавливается выбранной Вами компанией перевозчиком<br/>
						5. Доставка товара общей стоимостью более 2000 грн. осуществляется за счет Нашего портала
					  </p>";
	        
		    $body .= "<p>С уважением, администрация сайта<br/>
					Web:	<a href='http://babysmile.dp.ua'>www.babysmile.dp.ua</a><br/>
					E-mail: <a href='mailto:babysmile@ua.fm'>babysmile@ua.fm</a><br/>
					г. Днепропетровск<br/>
					(056) 788-23-83<br/>
					(067) 561-82-52</p>";
	        
	        $mime->setHTMLBody(iconv("UTF-8", "WINDOWS-1251",$body));
	        $mail = Mail::factory('mail');
    		$body = $mime->get(array('html_encoding'=>'windows-1251','head_charset'=>'windows-1251','html_charset'=>'windows-1251','text_charset'=>'windows-1251'));
    		$headers = $mime->headers($headers);
    		$mail->send($recipients, $headers, $body);
    		
	        unset($_SESSION['cart']);
	        echo json_encode(array('is_errors'=>0,'sent'=>1,'order_code'=>$order_code));
	        
	    }
    	
    } catch (FormException $e) {
    	echo json_encode(array('is_errors'=>1, 'error_msg'=>$e->getMessage(), 'field'=>$e->getFieldName(), 'index'=>$e->getFieldIndex()));
    } catch (Exception $e) {
    	echo json_encode(array('is_errors'=>1, 'error_msg'=>$e->getMessage()));
    }
    
}

?>