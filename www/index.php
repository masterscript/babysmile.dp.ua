<?php
//error_reporting(E_ALL ^ E_NOTICE);

error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);

ini_set('display_errors','On');

ini_set('magic_quotes_runtime',     0);
ini_set('magic_quotes_sybase',      0);
ini_set('magic_quotes_gpc',      0);

date_default_timezone_set('Europe/Kiev');

require_once('../config.php');

define("CONFIG_PATH",'../configs/');
define("MAIN_CONFIG_FILE",'front.conf');
define("LOCAL_CONFIG_FILE","local.conf");

//разбираем параметры от rewrite
$params=@$_GET['params'];
if (isset($params)) {
	$reWriteVars=explode('/',$params);
	if($reWriteVars[count($reWriteVars)-1]=='')
	{
		unset($reWriteVars[count($reWriteVars)-1]);
		$params=implode('/',$reWriteVars);
	}
	$params='/'.$params;
}
else 
{
	$params='';// реврайт не сработал - имеем только имя домена, т.е. мы на главной.
}
//---------------------
require_once('../classes/classes.php');

session_start();
session_name('SID');

require_once('../smarty/libs/Smarty.class.php');

try {
	if (isset($_SESSION['user'])) {
	    if (!isset($_GET['exit'])) {
	    	user::retrieve($_SESSION['user']);
	    } else {
	    	db::getDB()->query('INSERT into ?_userlog(user_id,action_type,ip,viewed_url) values(?,?,?,?)',$_SESSION['user']->getId(),'logout',$_SERVER['REMOTE_ADDR'],$_SERVER['REQUEST_URI']);
			unset ($_SESSION['user']);
			unset($_SESSION['cart']);
	    }
	}
	
	//----------------------
	
	$page=new current_page($params);
	$menu=$page->getMenu();
	$page->executeModules();
	$page->display();
} catch (Exception $e) {
	if ($e->getCode()=='404') {
		header('Status: 404 Not Found');
		header('HTTP/1.1 404 Not Found');
        $page=new page_404();
        $page->executeModules();
        $page->display();
	} elseif ($e->getCode()=='403') {
		header('Status: 403 Forbidden');
		header('HTTP/1.1 403 Forbidden');
        $page=new page_403((int)$e->getMessage());//первым в сообщении будет необходимый уровень доступа (целое число)
        $page->executeModules();
        $page->display();
	} else {
	    header('Status: 500 Internal Server Error');
		header('HTTP/1.1 500 Internal Server Error');
		$errorText = $e->getMessage() . $e->getTraceAsString();
		mail('andrey.garbuz@gmail.com', 'SiteEngine - Babysmile', $errorText);
        $page=new page_500($e->getMessage());
        $page->executeModules();
        $page->display();
	}
}

// Код обработчика ошибок SQL.
function databaseErrorHandler($message, $info)
{
    // Если использовалась @, ничего не делать.
    if (!error_reporting()) return;
    // Выводим подробную информацию об ошибке.
    echo "SQL Error: $message<br><pre>"; 
    print_r($info);
    echo "</pre>";
    exit();
}
?>