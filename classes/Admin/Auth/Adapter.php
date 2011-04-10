<?php

class Admin_Auth_Adapter implements Zend_Auth_Adapter_Interface {
	
	private $username;
	private $password;
	
	private $data = array();
	
	private $db;
	
	/**
	 * @var Zend_Auth_Result
	 */
	private $result;
	
	public function __construct() {
		
		$this->db = Admin_Core::getObjectDatabase();
		
		if ($this->hasIdentity()) {
			$this->_setData(Zend_Auth::getInstance()->getIdentity());
		}
		
	}
	
	private function _setData($identity) {
		
		$this->data = $this->db->selectRow(
				'SELECT u.*,i.*,g.id group_id,g.group_name FROM ?_users u
				JOIN ?_groups g ON u.group_id = g.id
				JOIN ?_items i ON u.id = i.id
				WHERE url = ? AND access_level>2','/users/'.$identity);
		$this->data['login'] = str_replace('/users/','',$this->data['url']);
		
	}
	
	public function setPassword($password) {
		
		$this->password = $password;
		
	}
	
	public function setUsername($username) {
		
		$this->username = $username;
		
	}

	
	/**
	 * @see Zend_Auth_Adapter_Interface::authenticate()
	 * @return Zend_Auth_Result
	 */
	public function authenticate() {
		
		$result = $this->db->selectCell('
			SELECT COUNT(*) FROM ?_users u
			JOIN ?_groups g ON u.group_id = g.id
			JOIN ?_items i ON u.id = i.id
			WHERE url = ? AND pass = ? AND access_level>2',
			'/users/'.$this->username,md5($this->password)
		);
    	
    	if ($result==0) {
    		return $this->result = new Zend_Auth_Result(Zend_Auth_Result::FAILURE,$this->username,array('Неправильные имя пользователя или пароль'));
    	} else {
    		$this->_setData($this->username);
    		return $this->result = new Zend_Auth_Result(Zend_Auth_Result::SUCCESS,$this->username);
    	}
		
	}
	
	/**
	 * @return Zend_Auth_Result
	 */
	public function getResult() {
		
		return $this->result;
		
	}
	
	public function hasIdentity() {
		
		return Zend_Auth::getInstance()->hasIdentity();
		
	}
	
	/**
	 * @return array
	 */
	public function getData() {
		
		return $this->data;
		
	}
	
	public function __get($name) {
		
		if (isset($this->data[$name]))
			return $this->data[$name];
			
		throw new Admin_CoreException("Call to undefined property $name");
		
	}
	
}
