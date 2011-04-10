<?php
function getClothers($obj) {

	$colors = $obj->getClothersColors();
	
	$clothers = db::getDB()->select('
		SELECT cl.*,g.availability,gn.id is_notificate
		FROM clothers cl
		JOIN goods g ON cl.id = g.id
		JOIN items i ON cl.id = i.id
		LEFT JOIN goods_notifications gn ON gn.good_id = cl.id AND user_id = ?d
		WHERE i.pid = ?d AND protected<=?d
		ORDER BY size,color_name',user::getId(),$obj->getId(),user::getAccessLevel());
	
	$matrix = array();
	
	foreach ($clothers as $params) {
		foreach ($colors as $color) {
			if ($params['color_name']==$color['color_name']) {
				$matrix[$params['size']][$color['color_name']] = $params;
			} else {
				$matrix[$params['size']][$color['color_name']] = array();
			}
		}
	}
	
	return $matrix;
	
}
