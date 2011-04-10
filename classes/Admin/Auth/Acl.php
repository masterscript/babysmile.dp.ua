<?php

class Admin_Auth_Acl extends Zend_Acl {
	
	public function __construct() {
		
		$this->addRole(new Zend_Acl_Role('guest'));
		
	}
	
	private function _parseRole($role) {
		
		if (is_string($role)) {
			if ($role=='*')
				return null;
			return explode(',',$role);
		}
		
		return $role;
		
	}
	
	/**
	 * @see Zend_Acl::allow()
	 * @param Zend_Acl_Role_Interface|string|array $roles
	 * @param Zend_Acl_Resource_Interface|string|array $resources
	 * @param string|array $privileges
	 * @param Zend_Acl_Assert_Interface $assert
	 * @return Zend_Acl
	 */
	public function allow($roles = null, $resources = null, $privileges = null, Zend_Acl_Assert_Interface $assert = null) {
		
		parent::allow($this->_parseRole($roles), $resources, $privileges, $assert);
		
	}
	
	/**
	 * @see Zend_Acl::deny()
	 * @param Zend_Acl_Role_Interface|string|array $roles
	 * @param Zend_Acl_Resource_Interface|string|array $resources
	 * @param string|array $privileges
	 * @param Zend_Acl_Assert_Interface $assert
	 * @return Zend_Acl
	 */
	public function deny($roles = null, $resources = null, $privileges = null, Zend_Acl_Assert_Interface $assert = null) {
		
		parent::deny($this->_parseRole($roles), $resources, $privileges, $assert);
		
	}

	
}
