<?php

require_once 'Admin/Errors.php';

/**
 * Класс ядра системы администрирования
 * 
 */
final class Admin_Core {
	
	/**
	 * Путь к файлам конфигурации
	 *
	 */
	const PATH_TO_CONFIGS = '/configs/';
	const BACKEND_MAIN_CONFIG = 'back.conf';
	const FRONT_MAIN_CONFIG = 'front.conf';
	const LOCAL_CONFIG = 'local.conf';
	
	/**
	 * Путь к файлам конфигурации шаблонов системы администрирования 
	 *
	 */
	const PATH_TO_ADMIN_CONFIGS = '/admin/';
	
	/**
	 * Путь к файлам конфигурации интерфейсов
	 *
	 */
	const PATH_TO_INTERFACE_CONFIGS = '/admin/interfaces/';
	
	/**
	 * Путь к файлам конфигурации элементов в Frontend
	 *
	 */
	const PATH_TO_FRONT_CONFIGS = '/pages/';
	
	/**
	 * Конфгурационный файл по умолчанию
	 *
	 */
	const DEFAULT_CONFIG = 'view';
	
	/**
	 * Рабочая таблица базы данных по умолчанию
	 *
	 */
	const DEFAULT_TABLE = 'items';
	
	/**
	 * Некоторые названия секций и параметров
	 * в конфигурационном файле
	 *
	 */
	const SECTION_LAYOUT = 'LAYOUT';
	const PARAM_FILE = 'file';
	const SECTION_BLOCKS = 'BLOCKS';
	const SECTION_CONTROLLERS = 'CONTROLLERS';
	const SECTION_DEFAULT_TEMPLATES = 'DEFAULT_TEMPLATES';
	
	/**
	 * Завершение работы при возникновении ошибок SQL
	 *
	 */
	const EXIT_ON_SQL_ERROR = true;
    
	/**
	 * Сетевой протокол сервера (http,https)
	 *
	 */
	const PROTOCOL = 'http';
	
    /**
     * Экземляр класса Admin_Core
     *
     * @var Admin_Core
     */
	static private $instance = NULL;
	
    /**
     * Объект smarty
     *
     * @var Admin_Smarty_Blocks
     */
    static private $objectSmarty;
    
    /**
     * Объект шаблонизатора
     *
     * @var Admin_Template_Engine
     */
    static private $objectTemplateEngine;
    
    /**
     * Объект DbSimple
     *
     * @var Admin_Db_Main
     */
    static private $objectDatabase;
    
    /**
     * URI текущей страницы
     *
     * @var string
     */
    static private $uri;
    
    /**
     * Имя шаблона в системе администрирования
     *
     * @var string
     */
    static private $template_name;
    
    /**
     * Имя действия
     *
     * @var string
     */
    static private $action_name;
    
    /**
     * Имя группы для действия
     *
     * @var string|bool
     */
    static private $action_group;
    
    /**
     * Объект действий
     *
     * @var Admin_Actions_Core
     */
    static private $objectActionsCore;
    
    /**
     * Часть uri без имени шаблона
     *
     * @var string
     */
    static private $uri_last_part;
    
    /**
     * item id
     *
     * @var integer
     */
    static private $item_id;
    
    /**
     * Имя файла конфигурации элемента
     *
     * @var string
     */
    static private $item_config_name;
    
    /**
     * Объект файла конфигурации запрошенного шаблона
     *
     * @var Admin_Template_Config
     */
    static private $objectAdminConfig;
    
    /**
     * Объект файла конфигурации элемента в Frontend
     *
     * @var Admin_Template_Config
     */
    static private $objectItemFrontendConfig;
    
    /**
     * Объект файла конфигурации интерфейсов элемента
     *
     * @var Admin_Template_Config
     */
    static private $objectItemConfig;
    
    /**
     * Объект глобального файла конфигурации
     *
     * @var Admin_Template_Config
     */
    static private $objectGlobalConfig;
    
    private $_globalParams = array();
    
    /**
     * @var Admin_Auth_Adapter
     */
    private static $auth;
	
	private function __construct () {

		try {
			// удаляем лишние слеши
			self::stripData();
		    // текущий URI страницы
			self::$uri = $_SERVER['REQUEST_URI'];
		    // настройка Smarty
		    self::setSmartyObject();
		    // получение имени шаблона из URI
			self::parseUri();			
			// получаем объект глобального файла конфигурации
			self::setObjectGlobalConfig();
			// подключение к базе данных
		    self::DBConnect();
		    // получаем id элемента
			self::setItemId();
			// поучаем имя файла конфигурации элемента
			self::setItemConfigName();
			// аутентификация
		    self::auth();
			// получаем объект действий
			self::setObjectActionsCore();
			// определение группы для действия
			self::setActionGroup();
			// получаем объект файла конфигурации страницы системы администрирования
			self::setObjectAdminConfig();
			// получаем объект файла конфигурации интерфейса элемента в Backend
			self::setObjectItemConfig();
			// получаем объект файла конфигурации элемента в Frontend
			self::setObjectItemFrontendConfig();			
			// выдача страницы
		    self::displayPage();

		} catch (Zend_Acl_Exception $acl_error) {
			
		    header("HTTP/1.1 403 Forbidden");
			self::$template_name = 'errors';
			self::$action_name = 'err403';
			self::setObjectActionsCore();
			self::setObjectAdminConfig();			
			self::displayPage();
			
		} catch (Admin_CoreException $core_error) {
			
			$err_msg = "Admin_CoreException -> Фатальное исключение: {$core_error->getMessage()}";
			Admin_Errors::add($err_msg,$core_error);
			
			if ($core_error instanceof Admin_UserException) {
				header("HTTP/1.1 403 Forbidden");
				self::$template_name = 'errors';
				self::$action_name = 'err403';
			} else {
				header("HTTP/1.1 500 Internal Server Error");
				self::$template_name = 'errors';
				self::$action_name = 'err500';
			}
			
			try {
			
				if (!defined('DEBUG_MODE')) {
				
					file_put_contents(SYSTEM_PATH.'/system_errors.log',date('Y-m-d H:i:s').' '.$err_msg.PHP_EOL,FILE_APPEND);
					
					@mail('darkside@i.ua','DneprInfo Backend',iconv('UTF-8','WINDOWS-1251',Admin_Errors::prnt($core_error,true)));
					
					self::setObjectActionsCore();
					self::setObjectAdminConfig();
					self::displayPage();
				} else {
					echo $err_msg;
					Admin_Errors::prnt($core_error);
				}
				
			} catch (Exception $e) {
				
				echo $e->getMessage();
				 
			}
			
		} catch (Exception $common_error) {
			Admin_Errors::add("Exception -> Фатальное исключение: {$common_error->getMessage()}",$common_error);
			Admin_Errors::prnt($common_error);
		}
	    
	}
	
	private function __clone() {
		// запрет клонирования объекта    
	}
	
	/**
	 * Создает новый объект smarty
	 * и производит начальные настройки
	 *
	 */
	private function setSmartyObject () {
		
		self::$objectSmarty = new Admin_Smarty_Blocks();
		
	}
	
	private function auth() {
		
		$auth = Zend_Auth::getInstance();
		
		if (isset($_GET['logout'])) {
			Zend_Auth::getInstance()->clearIdentity();
			Admin_Core::sendLocation('login');
		}
		
		self::$auth = new Admin_Auth_Adapter();
		
		$this->_globalParams['user'] = self::$auth->getData();
		
		if ($auth->hasIdentity()) return ;
		
		if (isset($_POST['doLogin'])) {

			self::$auth->setUsername($_POST['login']);
			self::$auth->setPassword($_POST['password']);
			
			$result = $auth->authenticate(self::$auth);
			
			if ($result->isValid()) {
				// redirect to start
				self::sendLocation('view');
			} else {
				self::$template_name = 'login';
                self::$action_name = 'login';
				$this->_globalParams['messages'] = $result->getMessages();
			}
			
		} else {
			self::$template_name = 'login';
            self::$action_name = 'login';
		}
		
	}
	
	/**
	 * Вывод страницы в поток
	 *
	 */
	private function displayPage () {
		
		// создание объекта обработчика шаблонов
		self::$objectTemplateEngine = new Admin_Template_Engine(
		    new Admin_Smarty_Blocks(),
		    self::$objectAdminConfig->getParsedConfig()
		);
		
		// AJAX запрос
		if (self::$template_name == 'ajax') {
			if (!file_exists(SYSTEM_PATH.'/plugins/admin/ajax/'.self::$action_name.'.php')) {
				throw new Admin_CoreException('Файл '.SYSTEM_PATH.'/plugins/admin/ajax/'.self::$action_name.'.php не найден');
			}
			self::$objectTemplateEngine->processAjax();
			return ;
		}
		
		// вывод служебной страницы
		if (self::$template_name=='auxiliary') {
			self::$objectTemplateEngine->processAuxiliary();
			return ;
		}
		
		// передача глобальных переменных
		self::$objectTemplateEngine->setGlobalTemplateParams($this->_globalParams);
				
		// проверка доступа к действию
		self::checkActionAccess();
		
		// обычный вывод страницы		
		self::$objectTemplateEngine->displayPage();
		
	}
	
	/**
	 * Устанавливает объект файла конфигурации страницы в системе администрирования
	 *
	 */
	private function setObjectAdminConfig () {
		
		$path_to_configs = SYSTEM_PATH.self::PATH_TO_CONFIGS.self::PATH_TO_ADMIN_CONFIGS;
		// определяем имя конфигурационного файла с проверкой существования файла, специфичного для действия 
		$template_name = self::$template_name.'_'.self::$action_name;
		if (self::$template_name!=self::$action_name && file_exists($path_to_configs.$template_name.'.conf')) {
			self::$template_name = $template_name;
		} elseif (self::$action_group!==false) {
		    // проверяем существование файла конфигурации с именем группы действия
		    $template_name = self::$template_name.'_'.self::$action_group;
    		if (self::$template_name!=self::$action_group && file_exists($path_to_configs.$template_name.'.conf')) {
    			self::$template_name = $template_name;
    		}
		}
		// проверяем, существует ли конфигурационный файл, специфичный для типа элемента
		$template_name = self::$template_name.'_'.self::$item_config_name;
		if (file_exists($path_to_configs.$template_name.'.conf')) {
		    self::$template_name = $template_name;
		}		
		// пытаемся получить объект конфигурационного файла
	    try {
			self::$objectAdminConfig = new Admin_Template_Config($path_to_configs.self::$template_name.'.conf');
		} catch (Admin_TemplateConfigException $e) {
			// использование конфигурационного файла по умолчанию в случае неудачи
			self::$action_name = self::$template_name = self::DEFAULT_CONFIG;
			Admin_Errors::add($e->getMessage());
			self::$objectAdminConfig = new Admin_Template_Config($path_to_configs.self::DEFAULT_CONFIG.'.conf');
		}
		
	}
	
	/**
	 * Устанавливает объект файла конфигурации интерфейса элемента в системе администрирования
	 *
	 */
	private function setObjectItemConfig () {
		
		// пытаемся получить объект конфигурационного файла по имени
	    try {
			self::$objectItemConfig = new Admin_Template_Config(SYSTEM_PATH.self::PATH_TO_CONFIGS.self::PATH_TO_INTERFACE_CONFIGS.self::$item_config_name.'.conf');
		} catch (Admin_TemplateConfigException $e) {
			// использование объекта глобального конфигурационного файла
			self::$objectItemConfig = clone self::$objectGlobalConfig;
			Admin_Errors::add($e->getMessage());
			/*throw new Admin_CoreException($e->getMessage());*/
		}
		
	}
	
	/**
	 * Устанавливает объект файла конфигурации элемента в Frontend
	 *
	 */
	private function setObjectItemFrontendConfig () {
		
		// пытаемся получить объект конфигурационного файла по имени
	    try {
			self::$objectItemFrontendConfig = new Admin_Template_Config(SYSTEM_PATH.self::PATH_TO_CONFIGS.self::PATH_TO_FRONT_CONFIGS.self::$item_config_name.'.conf');
		} catch (Admin_TemplateConfigException $e) {
			// использование конфигурационного файла по умолчанию в случае неудачи
			/**
			 * @todo считать настройки по умолчанию
			 */
			self::$objectItemFrontendConfig = NULL;
			Admin_Errors::add($e->getMessage());
			/*throw new Admin_CoreException($e->getMessage());*/
		}
		
	}
	
	/**
	 * Устанавливает объект файла глобального конфигурации
	 *
	 */
	private function setObjectGlobalConfig () {
		
		$configs = array(
			SYSTEM_PATH.self::PATH_TO_CONFIGS.self::BACKEND_MAIN_CONFIG,
			SYSTEM_PATH.self::PATH_TO_CONFIGS.self::LOCAL_CONFIG,
			SYSTEM_PATH.self::PATH_TO_CONFIGS.self::FRONT_MAIN_CONFIG,
		);
		
		self::$objectGlobalConfig = new Admin_Template_Config($configs);
		
	}
	
	/**
	 * Логический разбор запрошенного URI
	 *
	 */
	private function parseUri () {
		
		$uri_parts = preg_replace('#^'.SITE_SUBDIR.'/#is','',self::$uri);
		if (strpos($uri_parts,'?')!==false) {
			$uri_parts = explode('?',$uri_parts);
			$uri_parts = self::$uri_last_part = $uri_parts[0];
		} else {
            self::$uri_last_part = $uri_parts;
        }
		
		if (strpos($uri_parts,'/')!==false) {
			$uri_parts = explode('/',$uri_parts);
		} else {
			// по умолчанию имя действия равно названию шаблона
			$uri_parts = array($uri_parts,$uri_parts);
		}
		
		list(self::$template_name,self::$action_name) = $uri_parts;
		if (empty(self::$template_name)) self::$template_name = self::DEFAULT_CONFIG;
		if (empty(self::$action_name)) self::$action_name = self::DEFAULT_CONFIG;
		self::$uri_last_part = str_replace(self::$template_name.'/','',self::$uri_last_part);
		
	}
	
	/**
	 * Группа для действия (если она одна)
	 *
	 */
	private function setActionGroup () {
	    
	    self::$action_group = self::$objectActionsCore->getActionGroup(self::$action_name);
	    
	}
	
	/**
	 * Устанавливает id элемента в зависимости от переданного параметра
	 *
	 */
	private function setItemId () {
		
		// если id не передан, то устанавливается id = 1
		if (!isset($_GET['id'])) {
			self::$item_id = 1;
		} elseif (self::$objectDatabase->selectCell('SELECT COUNT(id) FROM ?_items WHERE id = ?',$_GET['id'])<1) {
		    throw new Admin_UserException('Запрошенный элемент не найден. URI: '.$_SERVER['REQUEST_URI'].$_SERVER['REQUEST_URI'].'. REFERER: '.@$_SERVER['HTTP_REFERER']);
		} else {
			self::$item_id = $_GET['id'];
		}
		
	}
	
	/**
	 * Устанавливает имя конфигурационного файла элемента 
	 *
	 */
	private function setItemConfigName () {
		
		 $item_params = self::$objectDatabase->selectRow('SELECT `type`,template FROM ?_items WHERE id = ?',self::$item_id);
		 // имя файла конфигурации в базе данных
		 $db_config_name = empty($item_params['template']) ? $item_params['type'] : $item_params['template'];
         /*if (!file_exists(SYSTEM_PATH.'/'.self::PATH_TO_CONFIGS.self::PATH_TO_ADMIN_CONFIGS.$db_config_name.'.conf')) {
            try {
                 $defaultTemplatesSection = self::$objectGlobalConfig->getConfigSection(self::SECTION_DEFAULT_TEMPLATES);
                 if (!isset($defaultTemplatesSection[$db_config_name])) {
                     throw new Admin_TemplateConfigException('В секции '.self::SECTION_DEFAULT_TEMPLATES.' не найдено соответствие типу '.$db_config_name);
                 }
                 self::$item_config_name = $defaultTemplatesSection[$db_config_name];
             // @TODO убрать блок try-catch и не перехватывать это исключение здесь
             } catch (Admin_TemplateConfigException $e) {
                 // код восстановления - используем полученое из базы данных имя файла конфигурации
                 self::$item_config_name = $db_config_name;
                 Admin_Errors::add('',$e);
             }
         } else {
            self::$item_config_name = $db_config_name;
         }*/
         
         self::$item_config_name = $db_config_name;
		
	}
	
	/**
	 * Устанавливает объект действий
	 *
	 */
	private function setObjectActionsCore () {
	    
	    self::$objectActionsCore = new Admin_Actions_Core();
	    
	}
	
	/**
	 * Проверяет возможность доступа к действию над выбранным элементов
	 * 
	 * @return bool
	 */
	private function checkActionAccess () {
	    
	    // пропускаем контроль доступа для глобальных действий
	    /*$action_groups = $objectActionCheckAccess->getActionGroup(self::$action_name);
	    if (in_array('global',$action_groups)) {
	        return ;
	    }*/
	    
	    if (!self::$objectActionsCore->check()) {
	        throw new Zend_Acl_Exception('Действие '.self::$action_name.' не разрешено для текущего элемента. Запрошенный URI: '.$_SERVER['REQUEST_URI'].'. REFERER: '.@$_SERVER['HTTP_REFERER']);
	    }
	    
	}
	
	/**
	 * Подключение к базе данных
	 *
	 */
	private function DBConnect () {
		
		$objectConfig = new Admin_Template_Config(SYSTEM_PATH.self::PATH_TO_CONFIGS.self::LOCAL_CONFIG);
		$db_connect_params = $objectConfig->getConfigSection('DATABASE');
		
		$dsn = "{$db_connect_params['dbtype']}://{$db_connect_params['username']}:{$db_connect_params['password']}@{$db_connect_params['host']}/{$db_connect_params['dbname']}";
		Admin_Db_Main::db_connect($dsn);
		self::$objectDatabase = new Admin_Db_Main();
		self::$objectDatabase->query('SET NAMES UTF8');		
		self::$objectDatabase->setErrorHandler(array(__CLASS__,'databaseErrorHandler'));
		self::$objectDatabase->setIdentPrefix($db_connect_params['table_prefix']);
		// начальная настройка объекта
		self::$objectDatabase->setDefaultTable(self::DEFAULT_TABLE);
		
	}
	
	/**
	 * Удаление лишних слешей
	 *
	 * @param mixed $el
	 */
	private function strips(&$el) {
		 
	    if (is_array($el)) { 
	    	foreach($el as $k=>$v) { 
	    		$this->strips($el[$k]); 
	    	}
	    } else {
	    	$el = stripslashes($el);
	    }
	     
  	}
  	
  	/**
  	 * Удаление лишних слешей
  	 *
  	 */
  	private function stripData () {
  		
  		set_magic_quotes_runtime(0);
	    if (get_magic_quotes_gpc()) {
		    $this->strips($_GET);
		    $this->strips($_POST);
		    $this->strips($_COOKIE);
		    $this->strips($_REQUEST);
		    if (isset($_SERVER['PHP_AUTH_USER'])) $this->strips($_SERVER['PHP_AUTH_USER']);
		    if (isset($_SERVER['PHP_AUTH_PW']))   $this->strips($_SERVER['PHP_AUTH_PW']);
	  	}
			  	
  	}
	
	/**
	 * Перехват ошибок SQL
	 *
	 * @param string $message
	 * @param string $info
	 */
	static public function databaseErrorHandler($message, $info) {
      
		// если ошибки отключены, то на ошибки SQL также нет реакции
		if (!error_reporting()) {
			return;
		}
		
		ob_start();
		echo "<hr>SQL Error: $message<br><pre>"; 
		print_r($info);
		echo "</pre><hr>";
		$message = ob_get_contents();
		ob_end_clean();		
		throw new Admin_DbException('<b>Database Error</b>. Message: '.$message);
		/*if (self::EXIT_ON_SQL_ERROR) {
			die();
		}
		return;*/
      
	}
	
	/**
	 * Проверка на существование файла с классом
	 *
	 * @param имя класса $class_name
	 * @return bool
	 */
	static public function checkClassExists ($class_name) {
	    
		return file_exists(SYSTEM_PATH.'/classes/'.str_replace('_','/',$class_name).'.php');
	    
	}
	
	/**
	 * Возвращает объект Smarty
	 *
	 * @return Admin_Smarty_Blocks
	 */
	static public function getObjectSmarty () {
	    
	    return self::$objectSmarty;
	    
	}
	/**
	 * Возвращает объект шаблонизатора
	 *
	 * @return Admin_Template_Engine
	 */
	static public function getObjectTemplateEngine () {
	    
	    return self::$objectTemplateEngine;
	    
	}
	
	/**
	 * Возвращает экземпляр класса Admin_Core
	 *
	 * @return Admin_Core
	 */
	static public function getInstance () {
	    
	    if (self::$instance == NULL) {
	        self::$instance = new Admin_Core();
	    }
	    
	    return self::$instance;
	    
	}
	
	/**
	 * Возвращает объект для работы с базой данных
	 * 
	 * @return Admin_Db_Main
	 */
	static public function getObjectDatabase () {
		
		return self::$objectDatabase;
		
	}
	
	/**
	 * Возвращает объект конфигурации интерфейса элемента
	 *
	 * @return Admin_Template_Config
	 */
	static public function getObjectItemConfig () {
		
		return self::$objectItemConfig;
		
	}
	
	/**
	 * Возвращает объект конфигурации элемента в Frontend
	 *
	 * @return Admin_Template_Config
	 */
	static public function getObjectFrontendItemConfig () {
		
		return self::$objectItemFrontendConfig;
		
	}
	
	/**
	 * Возвращает объект глобальной конфигурации
	 *
	 * @return Admin_Template_Config
	 */
	static public function getObjectGlobalConfig () {
		
		return self::$objectGlobalConfig;
		
	}
	
	/**
	 * Возвращает объект конфигурации в системе администрирования
	 *
	 * @return Admin_Template_Config
	 */
	static public function getObjectAdminConfig () {
		
		return self::$objectAdminConfig;
		
	}
	
	/**
	 * Возвращает id элемента
	 *
	 * @return integer
	 */
	static function getItemId () {
		
		return self::$item_id;
		
	}

	/**
	 * Возвращает тип элемента
	 *
	 * @return string
	 */
	static function getItemType () {
		
		return self::$objectDatabase->selectCell('SELECT `type` FROM ?_items WHERE id = ?',self::$item_id);
		
	}
	
	/**
	 * Возвращает имя конфигурационного файла элемента
	 *
	 * @return string
	 */
	static function getItemConfigName () {
		
		return self::$item_config_name;
		
	}
	
	/**
	 * Возвращает используемый шаблон системы администрирования
	 *
	 * @return string
	 */
	static function getTemplateName () {
		
		return self::$template_name;
		
	}
	
	/**
	 * Возвращает имя действия
	 *
	 * @return string
	 */
	static function getActionName () {

		return self::$action_name;
		
	}
	
	/**
	 * Возвращает группу для действия
	 *
	 * @return string|bool
	 */
	static function getActionGroup () {
	    
	    return self::$action_group;
	    
	}
	
	static function getOverridenConfig($only_name = false) {		
		$config = self::$objectActionsCore->getOverrideConfigName(self::$action_name);
		if ($only_name===false) return $config;
		$path_parts = pathinfo($config);
		return $path_parts['filename'];
	}
	
	/**
	 * Возвращает имя класса для запрошенного действия
	 *
	 * @return string
	 */
	static function getActionClassName () {
		
		$action_name = preg_replace_callback('#_([a-zA-Z])#',create_function(
              '$matches',
              'return strtoupper($matches[1]);'
          ),self::getActionName());
		return ucfirst($action_name);
				
	}
	
	/**
	 * Возвращает имя класса для запрошенного действия
	 *
	 * @return string|bool
	 */
	static function getActionGroupClassName () {
	    
	    if (!self::$action_group) return false;
	    $action_name = preg_replace_callback('#_([a-zA-Z])#',create_function(
              '$matches',
              'return strtoupper($matches[1]);'
          ),self::$action_group);
		return ucfirst($action_name);
	    
	}
	
	/**
	 * Возвращает uri без имени шаблона
	 *
	 * @return string
	 */
	static function getUriLastPart () {
		
		return self::$uri_last_part;
		
	}
	
	/**
	 * @return Admin_Auth_Adapter
	 */
	static function getAuth() {
		
		return self::$auth;
		
	}
	/**
	 * Выполняет перенаправление на заданную страницу
	 *
	 */
	static function sendLocation () {
		
	    $args = func_get_args();
	    $num = func_num_args();
		$file = $line = null;
		if (headers_sent($file,$line))
			throw new Exception("Headers already sent at $file:$line");
	    if ($num==2) {
	        list($uri,$id) = $args;
	        header('Location:'.self::PROTOCOL.'://'.SITE_URL.'/'.$uri.'?id='.$id);
	    } elseif ($num==1) {
	        $uri = $args[0];
	        header('Location:'.self::PROTOCOL.'://'.SITE_URL.'/'.$uri);
	    } else {
	        header('Location:'.self::PROTOCOL.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
	    }
		die();
		
	}
	
	static function sendNoCache () {
	    
	    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
	    
	}
	
}

?>
