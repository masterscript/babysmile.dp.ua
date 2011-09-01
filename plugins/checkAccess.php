<?php

function checkAccess($obj)
{
	if (user::getCurrentUser()->getId() != $obj->getId()) {
    	header('HTTP/1.0 403 Forbidden');
		$page=new page_403('Forbidden');
		$page->executeModules();
		$page->display();
		die();
	}
}