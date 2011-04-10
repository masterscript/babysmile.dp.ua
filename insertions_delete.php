<?php

// удаление объ€влени€, у которых истек срок действи€

error_reporting(E_ALL);
ini_set('display_errors','On');

require_once 'libs/DbSimple/Generic.php';
$db = DbSimple_Generic::connect('mysql://u_babysmile:mWQbGNMu@localhost/babysmile');

$count = $db->select('
				DELETE FROM i, ins
				USING ?_items i
                JOIN insertions ins ON i.id = ins.id
                WHERE expire_date < current_date()');

// write to log
$f = fopen('/var/www/babysmile/cron.log', 'a');
fwrite($f,date('Y-m-d H:i:s').' - Job for "insertion delete" finished. '.$count.' expired insertions were deleted'.PHP_EOL);
fclose($f);

?>
