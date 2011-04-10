<?php
function getGoodSet($obj) {
    	    
	$props = db::getDB()->selectRow('
		SELECT type,description,i.id,name,url,title,price,availability,price_old
		FROM items i
		JOIN goods g ON i.id = g.id
		WHERE i.id = ?d AND protected<=?d',$obj->getId(),user::getAccessLevel()
    );
    
    if ($props['type']=='good_set' && empty($props['price'])) {
    	// расчитываем цену набора по сумме цены товаров, входящих в набор
    	$props['price'] = db::getDB()->selectCell('
    		SELECT SUM(price) FROM goods g
    		JOIN items i ON g.id = i.id
    		WHERE i.pid = ? AND protected<=?d',$obj->getId(),user::getAccessLevel());
    }
		
	return $props;
	
}
?>