<?php

class Admin_Actions_Sort_Photogallery extends Admin_Actions_Sort_Abstract {
	
	private $_photoTable;
	
    public function __construct() {
		
		parent::__construct();
	
	}
	
	/**
	 * @param string $photoTable
	 * @return Admin_Actions_Sort_Photogallery
	 */
	public function setPhotoTable($photoTable) {
		
		$this->_photoTable = $photoTable;
		return $this;
		
	}

	
	public function process () {
	    
	    // определяем item_id
        $item_id = $this->objectModel->getItem('item_id',$this->_photoTable,$_POST['photosTable'][1]);
        // сбрасываем поле sort
        $this->objectModel->updateField($this->objectModel->getPhotoTable(),'sort',0,array('item_id'=>$item_id));
        // устанавливаем в соответствии с переданным массивом
        foreach ($_POST['photosTable'] as $sort=>$photo_id) {
            if (!$photo_id)
                continue;
            $this->objectModel->updateField($this->_photoTable,'sort',$sort,array('id'=>$photo_id));
        }
	    
	}
	
	/**
	 * @see Admin_IController::getTemplateValue()
	 */
	public function getTemplateValue() {
		
		return array();
		
	}
	
}
