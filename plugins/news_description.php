<?php
function news_description($obj) {
       	
	$item = db::getDB()->selectRow('
		SELECT description,create_date,i.id,name,url,title,filename AS img_src
		FROM items i
		LEFT JOIN top_images ti ON ti.id = i.id 
		WHERE i.id = ?',$obj->getId()
    );
    
	return new page($item);
	
}
?>