<?php

/**
 * Класс, реализующий добавление контента к странице
 *
 */
class Admin_Model_Move_Common extends Admin_Model_Abstract {
    
	public function __construct() {
		
		parent::__construct();
	
	}
	
	/**
	 * Выполняет перемещение элемента
	 *
	 */
	public function move () {
		
	    $move_id = $_POST['move_item_id'];
	    // url элемента, в который будет происходить перемещение
	    $move_url = $this->getItem('url',$this->getDefaultTable(),$move_id);
	    // новый url элемента
	    $url_new = $move_url.'/'._Strings::uri_part($this->getItem('url'));
	    // старый url элемента
	    $url_old = $this->getItem('url');
	    // выполняем перемещение
	    $this->updateField($this->getDefaultTable(),'url',$url_new);
	    $this->updateField($this->getDefaultTable(),'pid',$move_id);
	    // изменяем url у потомков
		$this->changeChildsUrl($url_new,$url_old);
	    
 	}

	
}

?>
