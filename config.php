<?php

// управление отображением ошибок
if (defined('E_DEPRECATED')) {
	error_reporting(E_ALL & ~E_DEPRECATED);
} else {
	error_reporting(E_ALL);
}
define('DEBUG_MODE',1);
ini_set('display_errors','On');

ini_set('magic_quotes_runtime',0);
ini_set('magic_quotes_sybase',0);
ini_set('magic_quotes_gpc',0);

// установка путей подключения библиотек
if (!defined("PATH_SEPARATOR"))
  define("PATH_SEPARATOR", getenv("COMSPEC")? ";" : ":");

define("SITE_SUBDIR","");
define("SITE_URL",$_SERVER['HTTP_HOST'].SITE_SUBDIR);
define("SYSTEM_PATH",dirname(__FILE__));
define("PEAR_PATH",SYSTEM_PATH."/php");
define("SITE_PATH",$_SERVER['DOCUMENT_ROOT']."/".SITE_SUBDIR);
define("FRONT_SITE_PATH",str_replace('admin.babysmile','babysmile',$_SERVER['DOCUMENT_ROOT']));
define("FRONT_SITE_URL","http://babysmile.dp.ua");

ini_set("include_path", ini_get("include_path") . PATH_SEPARATOR . SYSTEM_PATH . 
												  PATH_SEPARATOR . SYSTEM_PATH . '/classes' .
												  PATH_SEPARATOR . SYSTEM_PATH . '/libs' .
												  PATH_SEPARATOR . SYSTEM_PATH . '/smarty/libs' .
                                                  PATH_SEPARATOR . PEAR_PATH 
                                                  );

?>