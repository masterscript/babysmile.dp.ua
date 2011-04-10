<?php
function getSearchResult($obj)
{
	$search=isset($_GET['words'])?strip_tags($_GET['words']):'';
	if ($search!='')
	{
		$searchResult=array();
		if ($obj->issetParam('count') && $obj->issetParam('numerator_name'))
		{
			$obj->setNumerator($obj->getSearchResultCount());
		}
		$find_items=db::getDB()->select(
			'SELECT items.id,items.url,items.name,items.title,items.description,items.mod_date,filename as image_filename,
						MATCH (name,title,description) AGAINST (?) as rate, MATCH (content.words) AGAINST (?) as rate2
				 FROM ?_items as items
				 		LEFT JOIN ?_content AS content ON content.id=items.id
				 		LEFT JOIN ?_top_images AS img ON items.id=img.id
				 WHERE items.protected<=? and (MATCH (name,title,description) AGAINST (?)>0 OR MATCH (content.words) AGAINST (?)>0)
				 ORDER BY rate DESC, rate2 DESC
				 {limit ?d,?d}'
			,$search,$search,user::getAccessLevel(),$search,$search,$obj->getLimitFrom(),$obj->getLimitCount());

		foreach ($find_items as $find)
		{
			$find['create_date']=$find['mod_date'];
			$searchResult[]=new page($find);
		}
		return $searchResult;
	}
	else
	{
		if ($obj->issetParam('count') && $obj->issetParam('numerator_name'))
		{
			$obj->setNumerator(0); //т.к. полюбому нужно проинициализировать нумератор если он есть в конфиге
		}
	}
}
?>