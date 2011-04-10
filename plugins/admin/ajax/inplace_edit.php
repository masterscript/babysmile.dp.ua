<?php

$objectDb = Admin_Core::getObjectDatabase();
$objectDb->setId($_GET['id']);

$field = str_replace('editable_','',$_POST['id']);
list($table,$field) = $objectDb->parseField($field,'--');

if ($objectDb->recordExists($table)) {
    $objectDb->updateField($table,$field,$_POST['value']);
    echo $objectDb->getItem($field,$table);
}

?>