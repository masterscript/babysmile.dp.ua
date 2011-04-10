<?php
function getTags($obj)
{
	$count=$obj->issetParam('count')?$obj->getParam('count'):DBSIMPLE_SKIP;
	$levels=$obj->issetParam('levels')?$obj->getParam('levels'):10;
	$tags=db::getDB()->select(
		'SELECT tags.name as text, count(t_items.item_id) as size from
		?_tags_items as t_items join ?_tags as tags on tags.id=t_items.tag_id
		where 1 group by t_items.tag_id {order by count(t_items.item_id) desc limit ?d}',$count);
	if (count($tags))
	{
		$max=$tags[0]['size'];
		$min=$tags[count($tags)-1]['size'];
		$delta=($max-$min)?$max-$min:1;
		$cloud=array();
		foreach ($tags as $tag)
		{
			$cloud[$tag['text']]=round(($tag['size']-$min)/$delta*$levels);
		}
		ksort ($cloud);
		return $cloud;
	}
	else return $tags;
}

?>