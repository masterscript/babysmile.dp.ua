<?php
function getChildFeed($obj)
{
	$childFeed=array();
	if ($obj->issetParam('count') && $obj->issetParam('numerator_name'))
	{
		$obj->setNumerator(
			db::getDB()->selectCell('SELECT count(id) from ?_items as items where items.url LIKE ? and protected<=?d',$obj->getUrl(true).'/%',user::getAccessLevel())
		);
	}
	$childs=db::getDB()->select(
		'SELECT items.id,url,name,title,description,mod_date,filename as image_filename
		from ?_items as items left join ?_top_images as img on items.id=img.id
		where items.pid = ?d and protected<=?d order by sort asc, mod_date desc {limit ?d,?d}'
		,$obj->getId(),user::getAccessLevel(),$obj->getLimitFrom(),$obj->getLimitCount());//будет Notice если в конфиге не определен count... подумать. (подумал, не будет)
	foreach ($childs as $child)
	{
		$child['create_date']=$child['mod_date'];//потом что-нибудь поумнее придумаю...
		$childFeed[]=new page($child);
	}
	return $childFeed;
}
?>