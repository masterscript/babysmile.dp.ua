<?php

function cart_show () {
    
	@session_start();
    // считаем сумму товаров и их количество
    $goods = array();
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $key=>$cart) {
            $goods[$key]['name'] = (db::getDB()->selectCell('SELECT name FROM items WHERE id = ?d',$cart['good_id']));
            $goods[$key]['price'] = (db::getDB()->selectCell('
            	SELECT
				  IFNULL(SUM(gc.price),0) + IFNULL(g.price, 0)
				FROM
				  `items` i
				  JOIN goods g ON i.id = g.id
				  LEFT JOIN items c ON c.pid = i.id
				  LEFT JOIN goods gc ON c.id = gc.id
				WHERE
				  i.id = ?d
				GROUP BY
				  i.id',$cart['good_id']));
            $goods[$key]['count'] = $cart['count'];
            $goods[$key]['good_id'] = $cart['good_id'];            
            $goods[$key]['price_sum'] = $goods[$key]['price']*$goods[$key]['count'];
        }
    }
    return $goods;
    
}

?>