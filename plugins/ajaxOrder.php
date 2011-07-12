<?php

require_once 'classes/Admin/Errors.php';
require_once 'Mail.php';
require_once 'Mail/mime.php';

function ajaxOrder()
{
    @session_start();
    
	if (isset($_POST['doClearCart'])) {
    	unset($_SESSION['cart']);
    	echo '{is_errors:0, clear:1}';
    	return ;
    }

    try {
    	
	    if (isset($_POST['doOrder']) && isset($_SESSION['cart'])) {
	    	
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
		    $notes = $_POST['note'];
		    
		    $isCourier = db::getDB()->selectCell('SELECT top FROM ?_items WHERE id = ?d',$city_id);
		    
		    if (empty($phone))
		    	throw new FormException('Необходимо указать номер телефона','phone');
		    if ($floor<1 || $floor>1000)
	    		throw new FormException('Укажите число от 1 до 1000','floor');
		    if (!$carrier_office && $delivery=='carrier')
		    	throw new FormException('Отсутствуют офисы компании перевозчика в выбранном Вами регионе. Укажите другой способ доставки','delivery');
		    if (!$isCourier && $delivery=='courier')
		    	throw new FormException('Доставка курьером осуществляется только по Днепропетровску. Укажите другой способ доставки','delivery');
		    	
		    if ($delivery!='carrier') {
		    	$carrier_office = 0;
		    }
	
		    if (isset($_POST['byDefault'])) {
			    db::getDB()->query(
			    	'UPDATE users SET phone = ?, phone_home = ?, city_id = ?d,	    		
			    		address = ?, house_number = ?, app_number = ?, floor = ?, intercom = ?,
			    		delivery = ?, carrier_office = ?d
			    	WHERE id = ?d',
			    	$phone,$phone_home,$city_id,$address,$houseNum,$appNum,$floor,$intercom,$delivery,$carrier_office,user::getId()
			    );
		    }
		    
		    // сохраняем заказ пользователя
	    	db::getDB()->query('
	    		INSERT INTO user_orders (user_id,phone,phone_home,address,house_number,app_number,floor,intercom,delivery,city_id,carrier_office,notes)
	    		VALUES (?d,?,?,?,?,?,?d,?,?,?d,?d,?)',user::getId(),$phone,$phone_home,$address,$houseNum,$appNum,$floor,$intercom,$delivery,$city_id,$carrier_office,$notes
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
	        	Пользователь {$_SESSION['user']->getNickName()} выполнил заказ на сайте.
	        	Время заказа: ".date('Y-m-d H:i:s').".
	        	Подробную информацию о заказе можно просмотреть по адресу: http://admin.babysmile.dp.ua/global/purchase_log?code=$order_code
	        ");
	        
	        $mail = Mail::factory('mail');
	        $mail->send($recipients, $headers, $body);
	        
	        // посылаем письмо пользователю
	        $recipients = $_SESSION['user']->getEmail();
	        $headers = array(
		    	'From'    => '=?windows-1251?B?'.base64_encode(iconv("UTF-8", "WINDOWS-1251",'babysmile.dp.ua')).'?=robot@babysmile.dp.ua',
		        'X-Mailer'=> 'PHP/'.phpversion(),
		        'To'      => '=?windows-1251?B?'.base64_encode(iconv("UTF-8", "WINDOWS-1251",$_SESSION['user']->getNickName())).'?='.$_SESSION['user']->getEmail(),
		        'Subject' => '=?windows-1251?B?'.base64_encode(iconv("UTF-8", "WINDOWS-1251",'Заказ на сайте babysmile.dp.ua')).'?=',
		    );
            $body = "
                <p>Уважаемый <strong>{$_SESSION['user']->getNickName()}</strong>!</p>
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
            
	        // счет
	        if (isset($_POST['sendAccount'])) {
	        	
	        	$body .= "<p>Полученный Вами файл содержит счет-фактуру для оплаты товара. Распечатав счет, Вы можете оплатить его в любом отделении банка. 
							Обращаем Ваше внимание, что оплата через отделения КБ «Приватбанка» осуществляется с минимальной комиссией.</p>";
	        	
	        	require_once 'utils/rtf.php';
	        	$data = array();
	        	$user = db::getDB()->selectRow('
	        		SELECT uo.*,u.fio FROM users u
	        		JOIN user_orders uo ON uo.user_id = u.id
	        		WHERE u.id = ?d AND uo.code = ?',user::getId(),$order_code);
	        	$data['user'] = $user['fio'].'<br/>тел. '.$user['phone'];
	        	
	        	$data['positions'] = db::getDB()->select('
	        		SELECT
					  i.name, p.count, IFNULL(SUM(gc.price), 0) + IFNULL(g.price, 0) AS price
					FROM
					  user_purchase p
					  JOIN goods g ON g.id = p.good_id
					  JOIN items i ON i.id = g.id
					  LEFT JOIN items c ON c.pid = i.id
					  LEFT JOIN goods gc ON c.id = gc.id
					WHERE
					  p.code = ?
					GROUP BY
					  i.id;
	        	',$order_code);
	        	
	        	// размер скидки расчитывается как сумма разниц между старой и новой ценой, умноженная на количество;
			    // скидка расчитывается только в том случае, когда старая цена больше новой
			    $data['discount'] = db::getDB()->selectCell('
			    	SELECT
					  FORMAT(SUM(t1.`sum`),2)
					FROM
					  (
					  SELECT
					    (IFNULL(SUM(IF(gc.price_old > gc.price, gc.price_old - gc.price, 0)), 0) + IFNULL(IF(g.price_old > g.price, g.price_old - g.price, 0), 0)) * p.count `sum`
					  FROM
					    user_purchase p
					    JOIN goods g ON g.id = p.good_id
					    JOIN items i ON i.id = g.id
					    LEFT JOIN items c ON c.pid = i.id
					    LEFT JOIN goods gc ON c.id = gc.id
					  WHERE
					    p.code = ?
					  GROUP BY
					    i.id
					  ) AS t1;',$order_code);
	        	
		    	$data['code'] = $order_code;
		    	$data['sum'] = db::getDB()->selectCell('
		    		SELECT
						SUM(t1.`sum`)
					FROM (
						SELECT
						  (IFNULL(SUM(gc.price), 0) + IFNULL(g.price, 0))*p.count `sum`
						FROM
						  user_purchase p
						  JOIN goods g ON g.id = p.good_id
						  JOIN items i ON i.id = g.id
						  LEFT JOIN items c ON c.pid = i.id
						  LEFT JOIN goods gc ON c.id = gc.id
						WHERE
						  p.code = ?
						GROUP BY
						  i.id
					) AS t1',$order_code);
		    	
		    	require_once 'utils/num2str.php';
		    	$data['in_words'] = num2str($data['sum']);
	        	$data['account'] = '№ СФ-'.$order_code.' от '.date('d.m.Y').' г.';
				$data['valid_date'] = date("d.m.Y",strtotime(date("Y-m-d", strtotime(date("Y-m-d"))) . " +3 day"));
	        	generateRtf($data)->save($order_code.'.rtf');
		        $mime->addAttachment($order_code.'.rtf','application/octet-stream');
		        
	        }	        
	        
	        $body .= "<p>Напоминаем Вам, что:</p>
	        		  <p>
						1. После поступления денежных средств на расчетный счет, менеджер свяжется с Вами для согласования всех условий и сроков доставки<br/>
						2. Доставка товара осуществляестя в течении 1-4 рабочих дней с момента согласованного отправления Товара любым выбранным Вами перевозчиком (<a href='http://www.autolux.ua/'>Автолюкс</a>, <a href='http://www.intime.com.ua/'>Интайм</a>, <a href='http://www.delivery-auto.com.ua/'>Деливери</a>, <a href='http://www.novaposhta.com.ua/'>Нова Пошта</a>,  и др.)<br/> 
						3. После отправки товара, менеджер сообщит Вам номер транспортной декларации отправленного товара. Получатель Товара должен знать номер декларации и обязан иметь при себе паспорт для его получения<br/>
						4. Оплата за доставку осуществляется при получении Товара. Тариф на оплату устанавливается выбранной Вами компанией перевозчиком<br/>
						5. Доставка товара общей стоимостью более 2000 грн. осуществляется за счет Нашего портала
					  </p>";
	        
	        $body .= "<p>Информацию о заказах можно отслеживать на <a href='http://babysmile.dp.ua/user-info'>странице пользователя</a></p>";
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
    		
	        @unlink($order_code.'.rtf');
	        unset($_SESSION['cart']);
	        
	        echo json_encode(array('is_errors'=>0,'sent'=>1,'order_code'=>$order_code));
	        
	    }
    	
    } catch (FormException $e) {
    	echo json_encode(array('is_errors'=>1, 'error_msg'=>$e->getMessage(), 'field'=>$e->getFieldName(), 'index'=>$e->getFieldIndex()));
    } catch (Exception $e) {
    	echo json_encode(array('is_errors'=>1, 'error_msg'=>$e->getMessage()));
    }
    
}