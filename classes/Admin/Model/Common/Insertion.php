<?php

require_once 'Mail.php';
require_once 'Mail/mime.php';

class Admin_Model_Common_Insertion extends Admin_Model_Abstract {
    
	public function __construct() {
		
		parent::__construct();
	
	}
	
	/**
	 * @see Admin_Model_Abstract::update()
	 *
	 */
	public function update () {
		
		if ($_POST['insertions::checked']) {
			$headers = array(
	                  'From'    => '=?windows-1251?B?'.base64_encode(iconv("UTF-8", "WINDOWS-1251",'Сайт babysmile.dp.ua')).'?=',
	                  'X-Mailer'=> 'PHP/'.phpversion(),
	                  'To'      => '=?windows-1251?B?'.base64_encode(iconv("UTF-8", "WINDOWS-1251",$this->getFormValue('insertions::author'))).'?= '.$this->getFormValue('insertions::email'),
	                  'Subject' => '=?windows-1251?B?'.base64_encode(iconv("UTF-8", "WINDOWS-1251",'Статус объявления')).'?='
	                 ); 
	        $body = iconv("UTF-8", "WINDOWS-1251","
	        	<h3>Здравствуйте, {$this->getFormValue('insertions::author')}</h3>
	        	<p>Объявление прошло проверку модератором</p>
	        	<p>Ваше объявление можно просмотреть по адресу: <a href='http://babysmile.dp.ua{$this->getItem('url')}'>http://babysmile.dp.ua{$this->getItem('url')}</a></p>
	        	<p>Ваше исходное объявление:</p>
	        	<p>{$this->getItem('text','insertions')}</p>
	        ");
	        
	        $mailMime = new Mail_mime();
	        $mailMime->setHTMLBody($body);
	        $body = $mailMime->get(array('html_encoding'=>'windows-1251','head_charset'=>'windows-1251','html_charset'=>'windows-1251'));
	        $headers = $mailMime->headers($headers);
	        
	        $mail = Mail::factory('mail');
	        $mail->send($this->getFormValue('insertions::email'), $headers, $body);
		}
        
        parent::update();
		
	}
		
 	
	/**
 	 * @see Admin_Model_Abstract::delete()
 	 *
 	 */
 	public function delete () {
 		
 		// отправка сообщению автору 
 		$headers = array(
                  'From'    => '=?windows-1251?B?'.base64_encode(iconv("UTF-8", "WINDOWS-1251",'Сайт babysmile.dp.ua')).'?=',
                  'X-Mailer'=> 'PHP/'.phpversion(),
                  'To'      => '=?windows-1251?B?'.base64_encode(iconv("UTF-8", "WINDOWS-1251",$this->getItem('author','insertions'))).'?= '.$this->getItem('email','insertions'),
                  'Subject' => '=?windows-1251?B?'.base64_encode(iconv("UTF-8", "WINDOWS-1251",'Статус объявления')).'?='
                 ); 
        $body = iconv("UTF-8", "WINDOWS-1251","
        	<h3>Здравствуйте, {$this->getItem('author','insertions')}</h3>
        	<p>Объявление не прошло проверку модератором и удалено, т.к. не соответствует 
        		<a href='http://babysmile.dp.ua/insertions/prrazmobavlen'>правилам размещения объявлений на сайте</a>
        	</p>
        	<p>Ваше исходное объявление:</p>
        	<p>{$this->getItem('text','insertions')}</p>
        ");
        
        $mailMime = new Mail_mime();
        $mailMime->setHTMLBody($body);
        $body = $mailMime->get(array('html_encoding'=>'windows-1251','head_charset'=>'windows-1251','html_charset'=>'windows-1251'));
        $headers = $mailMime->headers($headers);
        
        $mail = Mail::factory('mail');
        $mail->send($this->getItem('email','insertions'), $headers, $body);

 	    // удаляем связанные с объявлением изображения
 	    foreach ($this->show('insertion_photos',array('insertion_id'=>$this->getId())) as $img) {
 	        @unlink(FRONT_SITE_PATH.'/i/insertions/'.$img['filename']);
 	    }
 	    // вызываем родительский метод
 	    parent::delete();
 	    
    }
 	
}

?>
