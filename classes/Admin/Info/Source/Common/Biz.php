<?php

class Admin_Info_Source_Common_Biz extends Admin_Info_Source_Common {

	public function info_address() {
		
		return $this->selectCell("SELECT concat(street,', ',house_num,biz_subj.house_num_add,' офис ',appartment) FROM biz_subj WHERE id = ?",$this->getId());
		
	}
	
}

?>
