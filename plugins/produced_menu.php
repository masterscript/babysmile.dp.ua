<?php

/**
 * Block controller
 *
 * @param current_page $obj
 */
function produced_menu($obj) {

	$ids = db::getDB()->selectCol('
		SELECT biz_id FROM items i
		JOIN goods g ON i.id = g.id
		WHERE url LIKE ? AND type = ?',$obj->getUrl().'%','good');

	if (empty($ids)) $ids = array(0);
	
	if ($obj->getType()!='good' && $obj->getTemplate()!='catalog'
		&& $obj->getTemplate()!='category' && $obj->getTemplate()!='subcategory') {
			$ids = false;
		}
	
	return db::getDB()->select('
		SELECT i.id,name,url,title,country FROM items i
		JOIN biz b ON i.id = b.id
		WHERE `type` = ? AND protected<=?d {AND i.id IN (?a)}
		ORDER BY sort','biz',user::getAccessLevel(),$ids?$ids:DBSIMPLE_SKIP
    );
	
}
?>