<?php

/**
 * Класс, представляющий два поля для ввода пароля
 *
 */
class Admin_Forms_Elements_advpassword extends HTML_QuickForm_password {
	
	public function __construct ($elementName=null,$elementLabel=null,$attributes=null) {
	    
		parent::__construct($elementName,$elementLabel,$attributes);
	
	}
	
	/**
	 * @see HTML_QuickForm_input::toHtml()
	 *
	 * @return string
	 */
	public function toHtml() {
	    
	    // создаем второе поле для ввода пароля
	    $password_repeat = new HTML_QuickForm_password($this->getName().'_repeat');
	    $password_repeat->updateAttributes(array('meta:validator'=>'','id'=>$this->getAttribute('id').'_repeat'));
	    
	    return parent::toHtml().'<br />'.$password_repeat->toHtml();
	    
	}
	
}

?>
