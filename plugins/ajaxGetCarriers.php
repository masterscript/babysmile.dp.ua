<?php

function ajaxGetCarriers()
{
    $carriers = db::getDB()->select('
    	SELECT
		  carriers.id,carriers.name
		FROM
		  items carriers
		  JOIN items offices ON offices.pid = carriers.id
		  JOIN carrier_offices co ON co.id = offices.id
		  WHERE co.city_id = ?d
		  GROUP BY carriers.id',$_GET['city_id']);
    echo json_encode($carriers);
}