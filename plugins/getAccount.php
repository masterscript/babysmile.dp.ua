<?php

function getAccount($order_code) {

	$order_code = $_GET['order_code'];
	
    require_once 'utils/rtf.php';
    $data = array();
    $user = db::getDB()->selectRow('
        SELECT uo.*,u.fio FROM users u
        JOIN user_orders uo ON uo.user_id = u.id
        WHERE u.id = ?d AND uo.code = ?',user::getId(),$order_code);
    $data['user'] = $user['fio'].'<br/>тел. '.$user['phone'];
    
    $data['positions'] = db::getDB()->select('
        SELECT
		  i.name, p.count, IFNULL(SUM(gc.price), 0) + IFNULL(g.price, 0) AS price
		FROM
		  user_purchase p
		  JOIN goods g ON g.id = p.good_id
		  JOIN items i ON i.id = g.id
		  LEFT JOIN items c ON c.pid = i.id
		  LEFT JOIN goods gc ON c.id = gc.id
		WHERE
		  p.code = ?
		GROUP BY
		  i.id;
        ',$order_code);
    
    // размер скидки расчитывается как сумма разниц между старой и новой ценой, умноженная на количество;
    // скидка расчитывается только в том случае, когда старая цена больше новой
    $data['discount'] = db::getDB()->selectCell('
    	SELECT
		  FORMAT(SUM(t1.`sum`),2)
		FROM
		  (
		  SELECT
		    (IFNULL(SUM(IF(gc.price_old > gc.price, gc.price_old - gc.price, 0)), 0) + IFNULL(IF(g.price_old > g.price, g.price_old - g.price, 0), 0)) * p.count `sum`
		  FROM
		    user_purchase p
		    JOIN goods g ON g.id = p.good_id
		    JOIN items i ON i.id = g.id
		    LEFT JOIN items c ON c.pid = i.id
		    LEFT JOIN goods gc ON c.id = gc.id
		  WHERE
		    p.code = ?
		  GROUP BY
		    i.id
		  ) AS t1;',$order_code);
        
    $data['code'] = $order_code;
    $data['sum'] = db::getDB()->selectCell('
    	SELECT
			SUM(t1.`sum`)
		FROM (
			SELECT
			  (IFNULL(SUM(gc.price), 0) + IFNULL(g.price, 0))*p.count `sum`
			FROM
			  user_purchase p
			  JOIN goods g ON g.id = p.good_id
			  JOIN items i ON i.id = g.id
			  LEFT JOIN items c ON c.pid = i.id
			  LEFT JOIN goods gc ON c.id = gc.id
			WHERE
			  p.code = ?
			GROUP BY
			  i.id
		) AS t1',$order_code);
    
    require_once 'utils/num2str.php';
    $data['in_words'] = num2str($data['sum']);
    $data['account'] = '№ СФ-'.$order_code.' от '.date('d.m.Y').' г.';
	$data['valid_date'] = date("d.m.Y",strtotime(date("Y-m-d", strtotime(date("Y-m-d"))) . " +3 day"));
    generateRtf($data)->sendRtf($order_code);
        
}
