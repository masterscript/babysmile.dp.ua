<?php

class Admin_Info_Common extends Admin_Info_Abstract {
	
	public function __construct () {
		
		parent::__construct ();
		$this->collect();
	
	}
	
	public function collect () {
		
		parent::collect();
		
	}
	
	/**
	 * @see Admin_IController::getTemplateValue()
	 *
	 */
	public function getTemplateValue () {
		
//		Admin_Errors::prnt_array($this->element_info);
		return array('element_info'=>$this->element_info,'jscode'=>$this->inplace_editor_jscode);
		
	}
	
}

?>
