<?php

class Admin_Actions_Sort_Common extends Admin_Actions_Sort_Abstract {
	
    public function __construct() {
		
		parent::__construct ();
	
	}
	
	public function process () {
	    
	    // определяем parent
        $parent_id = $this->objectModel->getItem('pid',false,$_POST['table-sort'][0]);
        // сбрасываем поле sort
        $this->objectModel->updateField(false,'sort',0,array('pid'=>$parent_id));
        // устанавливаем в соответствии с переданным массивом
        foreach ($_POST['table-sort'] as $sort=>$item_id) {
            $this->objectModel->updateField(false,'sort',$sort+1,array('id'=>$item_id));
        }
	    
	}
	
	public function getTemplateValue() {
		
	    $childs = $this->objectModel->select('SELECT * FROM ?_items WHERE pid = ? {AND ?s} ORDER BY sort ASC',$this->objectModel->getId(),$this->getDisplayTypes());
		return array('childs'=>$childs);
	
	}
	
}

?>
