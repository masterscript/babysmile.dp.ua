<?php

require_once 'smarty/libs/plugins/modifier.convert_currency.php';

function ajax_cart_refresh () {
    
    @session_start();
    // считаем сумму товаров и их количество
    $price_sum = 0;
    $count_sum = 0;
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $cart) {
            $price_sum += (db::getDB()->selectCell('
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
				  i.id
            ',$cart['good_id']))*$cart['count'];
            $count_sum += $cart['count'];
        }
    }
    if ($_POST['data_type']=='price') echo smarty_modifier_convert_currency($price_sum);
    if ($_POST['data_type']=='count') echo $count_sum;
    
}

?>