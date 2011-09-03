<?php
function getGoodList($obj) {

	$filters = $obj->getCatalogFilters();
	if ($obj->getType() == 'biz') {
		$filters['vendors'] = array($obj->getId());
	}

	$goodsCount = db::getDB()->selectCell(
		'SELECT COUNT(i.id) FROM ?_items i
		LEFT JOIN goods g ON i.id = g.id
		WHERE (type = ? OR type = ? OR template = ? OR template = ?)
		AND protected<=?d
		{AND url LIKE ?}
		{AND g.price>=? AND g.price<=?}
		{AND i.name LIKE ?}
		{AND g.biz_id IN (?a)}
		{AND (price_old > price AND price_old > ?)}',
		'good', 'good_set', 'subcategory', 'clothers_container', user::getAccessLevel(),
		$obj->getType() == 'biz' ? DBSIMPLE_SKIP : $obj->getUrl() . '/%',
		$filters['price']['filtered'] ? $filters['price']['min'] : DBSIMPLE_SKIP, $filters['price']['filtered'] ? $filters['price']['max'] : DBSIMPLE_SKIP,
		$filters['name'] ? $filters['name'] : DBSIMPLE_SKIP,
		$filters['vendors'] ? $filters['vendors'] : DBSIMPLE_SKIP,
		$filters['discount'] ? $filters['discount'] : DBSIMPLE_SKIP
	);
	
    if ($obj->issetParam('count') && $obj->issetParam('numerator_name')) {
        $obj->setNumerator($goodsCount);
    }
    	
	$items = db::getDB()->select(
		'SELECT
			type,description,i.id,name,url,title,filename AS img_src,price,
			availability,IF(price_old>price,price_old,0) price_old
		FROM items i
		LEFT JOIN goods g ON i.id = g.id
		LEFT JOIN top_images ti ON ti.id = i.id 
		WHERE (type = ? OR type = ? OR template = ? OR template = ?) AND protected<=?d
		{AND url LIKE ?}
		{AND g.price>=? AND g.price<=?}
		{AND i.name LIKE ?}
		{AND g.biz_id IN (?a)}
		{AND (price_old > price AND price_old > ?)}
		ORDER BY {?f,} type, sort, create_date DESC {limit ?d,?d}',
		'good', 'good_set', 'subcategory', 'clothers_container', user::getAccessLevel(),
		$obj->getType() == 'biz' ? DBSIMPLE_SKIP : $obj->getUrl() . '/%',
		$filters['price']['filtered'] ? $filters['price']['min'] : DBSIMPLE_SKIP, $filters['price']['filtered'] ? $filters['price']['max'] : DBSIMPLE_SKIP,
		$filters['name'] ? $filters['name'] : DBSIMPLE_SKIP,
		$filters['vendors'] ? $filters['vendors'] : DBSIMPLE_SKIP,
		$filters['discount'] ? $filters['discount'] : DBSIMPLE_SKIP,
		$filters['order']['price'] ? 'g.price ' . $filters['order']['price'] : DBSIMPLE_SKIP,
		$obj->getLimitFrom(),$obj->getLimitCount()
    );
    
    foreach ($items as $item) {
    	if ($item['type']=='good_set' && empty($item['price'])) {
    		// расчитываем цену набора по сумме цены товаров, входящих в набор
    		$item['price'] = db::getDB()->selectCell('
    			SELECT SUM(price) FROM goods g
    			JOIN items i ON g.id = i.id
    			WHERE i.pid = ?',$item['id']);
    	}
		$objectItems[] = new page($item);
	}
		
	
	return array('data' => $objectItems, 'count' => $goodsCount);
	
}
