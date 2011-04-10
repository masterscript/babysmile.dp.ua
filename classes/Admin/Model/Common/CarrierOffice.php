<?php

class Admin_Model_Common_CarrierOffice extends Admin_Model_Common {
    
	/*protected function _autoTitle($id) {
		
		$title = $this->getItem('name','items',$id);
		$this->updateField($this->getDefaultTable(),'title',$title,array('id'=>$id));
		
	}
	
	protected function _autoName($id) {
		
		$name = $this->getItem('name','items',$id);
		$cityName = $this->selectCell('SELECT name FROM ?_items WHERE id = (SELECT city_id FROM ?_carrier_offices WHERE id = ?d)',$id);
		$this->updateField($this->getDefaultTable(),'name',$cityName.' '.$name,array('id'=>$id));
		
	}*/
	
	/**
	 * @see Admin_Model_Abstract::update()
	 *
	 */
	public function update () {
		
		$this->db_fields['items']['title'] = $this->db_fields['items']['name'];
		$this->db_fields['carrier_offices']['city_id'] = $_POST['carrier_offices::city_id'];
		
        parent::update();
        
	}
		
 	
    public function insert() {
    	
    	$this->db_fields['items']['title'] = $this->db_fields['items']['name'];
    	$this->db_fields['carrier_offices']['city_id'] = $_POST['carrier_offices::city_id'];
    	
    	parent::insert();

    	// генерируем url
		$this->_autoUrl($this->getLastId());
    	
    }
 	
}
