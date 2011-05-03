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
	$filters['price']['filtered'] = isset($_GET['priceMin']) && isset($_GET['priceMax']);
	
	$filters['name'] = isset($_GET['name']) ? $_GET['name'] : false;
	$filters['vendors'] = is_array($_GET['vendors']) ? $_GET['vendors'] : array();	
	$filters['discount'] = isset($_GET['discount']) ? 1 : 0;
	$filters['vendorsList'] = db::getDB()->select(
		'SELECT v.* FROM ?_items i
		JOIN ?_goods g ON g.id = i.id
		JOIN ?_items v ON g.biz_id = v.id		
		WHERE i.url LIKE ?
		GROUP BY v.id', $page->getURL() . '/%'
	);
	
	if ($filters['vendors']) {
		foreach ($filters['vendorsList'] as $k=>$v) {
			$filters['vendorsList'][$k]['active'] = in_array($v['id'], $filters['vendors']);
		}
	}
	
	$filters['is_active'] = $filters['price']['filtered'] || $filters['name']
		|| $filters['vendors'] || $filters['discount'];
		
	$filters['order'] = array(
		'price' => (isset($_GET['price_order']) && in_array($_GET['price_order'], array('asc', 'desc'))) ?
			$_GET['price_order'] : false
	);
	
	return $filters;    
}