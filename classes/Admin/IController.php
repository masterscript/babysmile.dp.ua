<?php

/**
 * Общий интерфейс контроллера шаблона
 *
 */
interface Admin_IController {
    
    /**
     * Возвращает массив переменных для передачи в шаблон
     *
     */
    public function getTemplateValue ();
    
}

?>
