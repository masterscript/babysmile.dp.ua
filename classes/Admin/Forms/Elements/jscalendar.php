<?php

class Admin_Forms_Elements_jscalendar extends Admin_Forms_Elements_Abstract_jscalendar {
    
    public function __construct ($elementName = null,$elementLabel = null,$attributes = null,$options = array()) {
        
        parent::__construct($elementName,$elementLabel,$attributes,$options);
        $this->theme = 'calendar-blue';
        // добавляем опции
        $trigger_id = $this->getName().'_calendar_trigger';
        $this->setConfig('button',$trigger_id);
        $this->setConfig('inputField',$this->getName());
        // изображение для активации календаря
        $this->trigger_html = '<img src="'.SITE_SUBDIR.self::BASEPATH.'img.gif" id="'.$trigger_id.'" />';
        
    }
	
}

?>
