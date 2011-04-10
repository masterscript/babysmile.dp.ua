<?php

class Admin_Actions_Sort_Banners extends Admin_Actions_Sort_Abstract {
	
    public function __construct() {
		
		parent::__construct();
	
	}
	
	public function process () {
	    
        // сбрасываем поле sort
        $this->objectModel->updateField('banners','sort',0);
        // устанавливаем в соответствии с переданным массивом
        foreach ($_POST['banners'] as $sort=>$id) {
            if (!$id)
                continue;
            $this->objectModel->updateField('banners','sort',$sort,array('id'=>$id));
        }
	    
	}
	
	/**
	 * @see Admin_IController::getTemplateValue()
	 */
	public function getTemplateValue() {
		
		return array();
		
	}
	
}
