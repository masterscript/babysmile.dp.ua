<?php
function main_menu($obj) {
    
	return db::getDB()->select('SELECT name,url,title FROM items WHERE menu_item = 1 AND protected<=?d ORDER BY sort',user::getAccessLevel());
	
}
?>