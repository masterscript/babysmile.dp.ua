<?php

$logger = fopen('/var/www/babysmile/babysmile.dp.ua/shoplist.log','a+');
fwrite($logger,date('Y-m-d H:i:s').' - start'.PHP_EOL);


error_reporting(E_ALL);
ini_set('display_errors','On');

require_once 'libs/DbSimple/Generic.php';
$db = DbSimple_Generic::connect('mysql://u_babysmile:mWQbGNMu@localhost/babysmile');
//$db = DbSimple_Generic::connect('mysql://root@localhost/babysmile');
$db->query('SET NAMES CP1251');

$goods = $db->select('
      SELECT i.id,i.pid,i.name,i.url,cat.name AS cat_name,img.filename,i.description,c.words,g.price,i_biz.name AS biz_name
      FROM items i
      JOIN items cat ON cat.id = i.pid
      JOIN goods g ON g.id = i.id
      LEFT JOIN biz b ON b.id = g.biz_id
      LEFT JOIN items i_biz ON i_biz.id = b.id
      LEFT JOIN top_images img ON img.id = i.id
      LEFT JOIN content c ON i.id = c.id
      WHERE i.type = ? AND i.protected=0 AND g.price>0 ORDER BY i.pid,i.sort','good');

$count = $db->selectCell('
      SELECT COUNT(i.id) FROM items i
      JOIN goods g ON g.id = i.id
      WHERE i.type = ? AND i.protected=0 AND g.price>0','good');

$f = fopen('/var/www/babysmile/babysmile.dp.ua/shoplist.csv', 'w+');
for ($i=2;$i<=8;$i++) {
    $cols[] = 'col'.$i;
}
fwrite($f,$count.','.implode(',',$cols).PHP_EOL);
fseek($f,0,SEEK_END);

foreach ($goods as $item) {
    
    $props = array();
    $props[] = $item['id'];
    $props[] = '"'.$item['cat_name'].'"';
    $props[] = '"'.$item['name'].'"';
    $props[] = $item['price'];
    $props[] = 'UAH';
    $props[] = 'http://babysmile.dp.ua'.$item['url'];
    $props[] = '"'.$item['biz_name'].'"';
    $props[] = 'http://babysmile.dp.ua/i/top/'.$item['id'].'/'.$item['filename'];

    fwrite($f, implode(',', $props).PHP_EOL);

    fseek($f,0,SEEK_END);

}

fclose($f);

// write to log
$f = fopen('/var/www/babysmile/cron.log', 'a');
fwrite($f,date('Y-m-d H:i:s').' - Job for "shoplist" export finished. '.$count.' goods were added'.PHP_EOL);
fclose($f);

fwrite($logger,date('Y-m-d H:i:s').' - end'.PHP_EOL);
fclose($logger);

?>
