<?php

$cities = Admin_Core::getObjectDatabase()->select('
    	SELECT i.id, i.name FROM ?_items i
    	WHERE i.pid = ?d AND i.template = ?
    ',$_GET['region_id'],'city');
    
echo json_encode($cities);        

?>