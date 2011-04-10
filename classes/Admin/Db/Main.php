<?php

/**
 * Главный класс для работы с базой данных;
 * используется в общих случаях, если не требуется
 * использование специальной модели данных
 *
 */
class Admin_Db_Main extends Admin_Db_Abstract {
	
    public function setDefaultTable ($table) {
    	
    	parent::setDefaultTable($table);
    	
    }
	
}

?>
