<?php

error_reporting(E_ALL);
ini_set('display_errors','On');

require_once 'classes/Library/Watermark.php';

/*$db = DbSimple_Generic::connect('mysql://u_babysmile:mWQbGNMu@localhost/babysmile');
$db->query('SET NAMES UTF-8');*/

$wm = new _Watermark();

if ($handle = opendir('.')) {
    while (false !== ($file = readdir($handle))) { 
        if ($file != "." && $file != "..") { 
            echo "$file\n"; 
        } 
    }
    closedir($handle); 
}

?>
