<?php

class Admin_Model_Common_Region extends Admin_Model_Common {
    
	/**
	 * @see Admin_Model_Abstract::update()
	 *
	 */
	public function update () {
		
		$this->db_fields['items']['title'] = $this->db_fields['items']['name'];
        parent::update();
		
	}
		
 	
    public function insert() {
    	
    	$this->db_fields['items']['title'] = $this->db_fields['items']['name'];
    	parent::insert();
    	
    }
 	
}
