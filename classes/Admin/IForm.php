<?php

/**
 * Общий интерфейс контроллера формы
 *
 */
interface Admin_IForm {
    
    /**
     * Возвращает массив переменных для передачи контроллеру действия
     *
     */
    public function getDbFields ();
    
    public function processForm ();    
    
}

?>
