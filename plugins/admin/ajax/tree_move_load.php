<?php

$id = intval($_GET['id']);
$virtual_id = isset($_GET['virtual_id'])?$_GET['virtual_id']:-1; 
$exclude_id = isset($_GET['exclude_id'])?$_GET['exclude_id']:-1;
$objectTree = new Admin_Tree_MoveElement_Common();
$objectTree->setExcludeIds($exclude_id);
$objectTree->processAjaxRequest($id,$virtual_id);

?>