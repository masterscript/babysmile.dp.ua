<?php
function good_description($obj) {
       	
	$item = db::getDB()->selectRow('
		SELECT
			i.description,i.id,i.name,i.url,i.title,filename AS img_src,
			price,availability,delivery_period,is_build,i_biz.name AS biz_name,
			i_biz.url AS biz_url,
			gi.id AS is_notificated, video_code,
			IF(price_old>price,price_old,0) price_old
		FROM items i
		JOIN goods g ON i.id = g.id
		LEFT JOIN biz b ON g.biz_id = b.id
		LEFT JOIN items i_biz ON g.biz_id = i_biz.id
		LEFT JOIN top_images ti ON ti.id = i.id
		LEFT JOIN goods_notifications gi ON gi.good_id = g.id AND gi.user_id = ?d 
		WHERE i.id = ?d',user::getId(),$obj->getId()
    );
    
	return new page($item);
	
}
?>