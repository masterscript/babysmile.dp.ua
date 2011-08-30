<?php
function getChildrenList($obj)
{
	if ($obj->issetParam('count') && $obj->issetParam('numerator_name')) {
        $obj->setNumerator(
            db::getDB()->selectCell('
                SELECT count(id) from ?_items as items
                WHERE type != ? AND url LIKE ? AND protected<=?d',
                'container', $obj->getUrl() . '/%', user::getAccessLevel()
            )
        );
    }
    
	$items = db::getDB()->select('
		SELECT i.id, pid, url, name, title, description, mod_date, filename image_filename
		FROM ?_items i
		LEFT JOIN ?_top_images t ON t.id = i.id
		WHERE type != ? AND url LIKE ? AND protected <= ?d
		ORDER BY sort, create_date DESC
		{LIMIT ?d,?d}',
		'container', $obj->getUrl() . '/%', user::getAccessLevel(), $obj->getLimitFrom(), $obj->getLimitCount()
    );
    
    foreach ($items as $item) {
		$objectItems[]=new page($item);
	}
		
	return $objectItems;
}