<?php
function getImageSource($obj)
{
	$id=isset($_GET['id'])?$_GET['id']:0;
	$path2small=db::getDB()->selectCell('SELECT img_path from ?_content_images where id=?d',$id);
	if ($path2small)
		$path2big=config::getConfigValue('FOLDERS','content_images').config::getConfigValue('FOLDERS','content_big_images').'/'.substr($path2small,strlen(config::getConfigValue('FOLDERS','content_images')));
	else $path2big=false;
	return $path2big;
}
?>