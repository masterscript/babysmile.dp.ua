<?php

require_once 'Mail.php';

function send_passw () {
        
    if (isset($_POST['doSend'])) {
        $user_id = db::getDB()->selectCell('SELECT id FROM users WHERE email=?',$_POST['email']);
        if ($user_id) {
            $new_passw = rand(10000,99999);
            db::getDB()->query('UPDATE users SET pass = md5(?) WHERE id = ?d',$new_passw,$user_id);
            $recipients = $_POST['email'];
            $headers['From']    = 'info@babysmile.dp.ua';
            $headers['To']      = $_POST['email'];
            $headers['Subject'] = 'Baby Smile';
            
            $body = iconv("UTF-8", "WINDOWS-1251",'Ваш новый пароль: '.$new_passw);
            
            $params = array(
                'host'=>'dnepr.info'
            );
            $mail = Mail::factory('mail',$params);
            $mail->send($recipients, $headers, $body);
        }
    }
    	
}

?>