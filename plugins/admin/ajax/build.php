<?php

$objectDb = Admin_Core::getObjectDatabase();        
if ($_POST['id']) {
    $id = (int)$_POST['id'];
    $value = $objectDb->getItem('is_build', 'goods', $id);
    if ($value==1) {
        $value = 0;
    } else {
        $value = 1;
    }
    $objectDb->query('UPDATE goods SET is_build = ?d WHERE id = ?d',$value,$id);

    echo $value;

}    	

?>