<?php
function getContent($obj)
{
	$content_file=config::getConfigValue('FOLDERS','content').$obj->getId().'.html';
	if (file_exists($content_file)) return file_get_contents($content_file);
	else return '';
}
?>