<?php
function getSearchResultCount($obj)
{
	$search=isset($_GET['words'])?strip_tags($_GET['words']):'';
	return db::getDB()->selectCell('SELECT count(items.id)
				 FROM ?_items as items
				 	LEFT JOIN ?_content AS content ON content.id=items.id
				 WHERE items.protected<=? and (MATCH (name,title,description) AGAINST (?)>0 OR MATCH (content.words) AGAINST (?)>0)'
				,user::getAccessLevel(),$search,$search);
}
?>