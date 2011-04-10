<?php
function getImageSourcePage($obj)
{
	$id=isset($_GET['id'])?$_GET['id']:0;
	$attr=db::getDB()->selectRow('SELECT items.id,url,name,title from ?_items as items,?_content_images as ci where items.id=ci.item_id and ci.id=?',$id);
	$page=new page($attr);
	return $page->getHTMLlink('long');
}
?>