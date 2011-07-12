<?php

function ajaxIsCityTop()
{
    @session_start();
    $data = array('top'=>db::getDB()->selectCell('SELECT top FROM items WHERE id = ?d',$_GET['city_id']));
    echo json_encode($data);
}