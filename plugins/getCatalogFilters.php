<?php

/**
 * @param current_page $page
 */
function getCatalogFilters($page) {
	
	$filters = array();

	$filters['price'] = db::getDB()->selectRow('
		SELECT MIN(price) mmin, MAX(price) mmax
		FROM ?_goods g
		JOIN ?_items i ON i.id = g.id
		WHERE url LIKE ? AND price > 0', $page->getURL() . '/%');
	
	$price = isset($_GET['price']);
	$filters['price']['min'] = isset($_GET['priceMin']) ? (int)$_GET['priceMin'] : $filters['price']['mmin'];
	$filters['price']['max'] = isset($_GET['priceMax']) ? (int)$_GET['priceMax'] : $filters['price']['mmax'];
	
	$filters['name'] = isset($_GET['name']) ? $_GET['name'] : '';
	$filters['vendors'] = isset($_GET['vendors']) ? (array)$_GET['vendors'] : array();	
	$filters['discount'] = isset($_GET['discount']) ? 1 : 0;
	
	return $filters;
    
}
