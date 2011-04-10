<?php
function list_currency($obj) {
    
	return db::getDB()->select('SELECT * FROM currency');
    
}
?>