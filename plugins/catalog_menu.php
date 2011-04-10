<?php
function catalog_menu($obj) {
    
	$catalog_menu = array();
	$url = $obj->getUrl();
	foreach (db::getDB()->select('SELECT id,name,url,title FROM items WHERE template = ? AND protected<=?d ORDER BY sort','category',user::getAccessLevel()) as $key=>$menu_item)  {
	    $catalog_menu[$key] = $menu_item;	    
        $catalog_menu[$key]['subcategory'] = 
            db::getDB()->select('SELECT name,url,title FROM items WHERE (template = ? OR type = ?) AND pid = ? AND protected<=?d ORDER BY sort','subcategory','good',$menu_item['id'],user::getAccessLevel());
	}
	return $catalog_menu;	
	
}
?>