<?php

/**
 * Общий интерфейс модели данных
 *
 */
interface Admin_IModel {
    
    /**
     * Возвращает массив данных для формы
     * 
     * @param array $sourced_fields поля с собстенным источником данных
     */
    public function getFieldsData ($sourced_fields);
    
    /**
     * Выполняет вставку новых записей в таблицы при отправке формы
     *
     */
    public function insert ();
    
    /**
     * Выполняет обновление записей в таблицах при отправке формы
     *
     */
    public function update ();
    
}

?>
