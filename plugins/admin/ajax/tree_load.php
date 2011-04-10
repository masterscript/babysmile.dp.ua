<?php

$id = intval($_GET['id']);
$virtual_id = isset($_GET['virtual_id'])?$_GET['virtual_id']:-1; 
$objectTree = new Admin_Tree_Common();
$objectTree->processAjaxRequest($id,$virtual_id);

?>