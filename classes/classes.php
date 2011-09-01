<?php
require_once "../libs/DbSimple/Generic.php";

class Engine_Core_Exception extends Exception  { }

class config
{
	static private $values=null;

	/**
	 * Загружает файл конфигурации в формате:
	 * <code>
	 * sitemap = host:localhost, method:get, global:yes
	 * </code>
	 *
	 * @param string $filename имя файла конфигурации.
	 * @return array многомерный ассоциативный массив.
	 */
	
	static function getConfigFile( $filename, $subfolder='' )
	{
		// проверяем, существует ли файл конфигурации
		if( !file_exists( CONFIG_PATH.$subfolder.$filename ) )
			throw new Exception( 'Config file '.CONFIG_PATH.$subfolder.$filename.' not found.' );
		// получаем массив строк в формате INI
		$admin_sections=array('TREE_RULES','FORMS','INFO');
		$arrConfigFile = parse_ini_file( CONFIG_PATH.$subfolder.$filename, true );
		// разбираем каждую строку на массив
		foreach ($arrConfigFile as $strSectionName=>$arrParams) {
			if (!in_array($strSectionName,$admin_sections))//костыль для отсечения бэк-энд секций конфига
			{
				// составляем список параметров
				$arrConfigVars = array();
				foreach ($arrParams as $strLineName=>$strLine) {
					// строка представлена в простом формате
					if( strpos( $strLine, ':' ) === false ) {
						$arrConfigVars[ $strLineName ] = $strLine;
					}
					// строка представлена в формате "переменная1:значение1, переменная2:значение2"
					else {
						$arrLineVars = preg_split('" *, *"', $strLine);
						foreach ($arrLineVars as $arrLineVar) {
							// разбиваем строку на части
							list($strVarName, $strVarValue) = preg_split('" *: *"', $arrLineVar);
							$arrConfigVars[ $strLineName ][ $strVarName ] = trim($strVarValue);
						}
					}
				}
				// сохраняем список параметров в переменной
				$values[ $strSectionName ] = $arrConfigVars;
			}
		}
		// возвращает разобранный файл конфигурации
		return $values;
	}
	
	function getConfigFiles()
	{
		return array_merge(self::getConfigFile(MAIN_CONFIG_FILE),self::getConfigFile(LOCAL_CONFIG_FILE));
	}
	
	function getConfigValue($section,$name=null)
	{
		if (self::$values==null) self::$values=self::getConfigFiles();
		if (isset(self::$values[$section]))
		{
			if ($name==null) return self::$values[$section];
			else
				if (isset(self::$values[$section][$name])) return self::$values[$section][$name];
				else throw new Exception('Undefined config valueName in '.$section.' config section: '.$name.'.');
		}
		else throw new Exception('Undefined config section '.$section.'.');
	}
	
	//return DATABASE config section value
	function getDBValue($name)
	{
		return self::getConfigValue('DATABASE',$name);		
	}
	
	function getTTMValue($name)
	{
		return self::getConfigValue('TABLE_TYPE_MATCHING',$name);	
	}
	
	function getProjectName()
	{
		return self::getConfigValue('PROJECT','name');
	}
	
	function getTemplatesValue($name)
	{
		return self::getConfigValue('DEFAULT_TEMPLATES',$name);
	}
	
	function getDefaultLang()
	{
		return trim(self::getConfigValue('LANGUAGES','default'));
	}
	
	function getLangs($full_info=false)
	{
		//если не указан default язык, то система моноязычная и вызов этого метода не имеет смысла
		if (self::getDefaultLang()=='') throw new Exception("Can't call languages list for a monolanguage system (need set default language in the main config file)");
		$langs=self::getConfigValue('LANGUAGES');
		unset ($langs['default']); //ну и других записей подобного рода пока добавлять в LANGUAGES не будем...
		if (!$full_info) return array_keys($langs);
		else return $langs;
	}
	
	function getLangLocale($lang)
	{
		$langs=self::getLangs('full');
		if (isset($langs[$lang]['locale'])) return $langs[$lang]['locale'];
		else throw new Exception('Undefined language "'.$lang.'" or not defined locale parameter for this language');
	}
	
	function getDefaultLangByDomain($domain)
	{
		if (@substr($domain,0,4)=='www.') $domain=substr($domain,4);
		$langs=self::getLangs('full');
		foreach ($langs as $lang_name=>$lang)
		if (isset($lang['domain']) and $lang['domain']==$domain) return $lang_name;
		return self::getDefaultLang();
	}
	
	function getLangDomain($lang)
	{
		$langs=self::getLangs('full');
		if (isset($langs[$lang]))
			if (isset($langs[$lang]['domain'])) return 'http://'.$langs[$lang]['domain'];
			else return '/';
		else throw new Exception('Undefined language "'.$lang.'".');
	}
	
	function getAjaxAllowTemplates()
	{
		return preg_split('" *, *"',self::getConfigValue('AJAX_ALLOW_TEMPLATES','allow'));
	}
	
	function getAjaxAllowPlugins()
	{
		return preg_split('" *, *"',self::getConfigValue('AJAX_ALLOW_PLUGINS','allow'));
	}
}

class current_user
{
	private $id;
	private $nickname;
	private $group_name;
	private $access_level;
	private $login_state;
	private $ip;
	private $pageUrl;
	private $cart, $cart_sum = 0, $cart_count = 0;	
	
	function __construct($login,$pass,$initType)
	{
		$this->ip=$_SERVER['REMOTE_ADDR'];
		if ($initType=='form')
		{
			if ($login!='')
			{
				$user_attr=db::getDB()->selectRow(
					'SELECT url,users.id,group_name,name,access_level,email
					FROM ?_items as items, ?_users as users, ?_groups as groups
					WHERE items.id=users.id and users.group_id=groups.id and (url=? OR email = ? ) and pass=?',
					config::getConfigValue('URLS','userfolder').$login, $login, md5($pass));
			}
		}
		elseif ($initType=='id')
		{
			$user_attr=db::getDB()->selectRow('SELECT users.id,group_name,name,access_level,email FROM ?_items as items, ?_users as users, ?_groups as groups WHERE items.id=users.id and users.group_id=groups.id and users.id=?d', $login);
		}
		if (isset($user_attr))
		{
			if ($user_attr)
			{
				$this->group_name=$user_attr['group_name'];
				$this->nickname=$user_attr['name'];
				$this->login_state='login';
				$this->access_level=$user_attr['access_level'];
				$this->email=$user_attr['email'];
				$this->id=$user_attr['id'];
				$this->pageUrl = $user_attr['url'];
				//db::getDB()->query('INSERT into ?_userlog(user_id,action_type,ip,viewed_url) values(?,?,?,?)',$this->id,'login',$this->ip,$_SERVER['REQUEST_URI']);//пришем вход в логи
				db::getDB()->query('UPDATE ?_users SET lastvisit=NOW() WHERE id=?',$this->id);
			}
			else 
			{
				$this->nickname='Гость';
				$this->group_name='guests';
				$this->login_state='login_error';
				$this->access_level=0;
				$this->email=null;
				$this->id=null;
			}
		}
		else 
		{
			$this->nickname='Гость';
			$this->group_name='guests';
			$this->login_state='not_login';
			$this->access_level=0;
			$this->email=null;
			$this->id=null;
		}
		$this->calcCart();
	}
	
	public function calcCart()
	{
		@session_start();
		$this->cart = array();
		if (isset($_SESSION['cart'])) {
			foreach ($_SESSION['cart'] as $key=>$cart) {
				$props = db::getDB()->selectRow('
					SELECT
					  i.description, i.id, i.name, i.url, i.title, filename AS img_src,
					  (IFNULL(SUM(gc.price),0) + IFNULL(g.price, 0)) price, g.availability,
					  g.delivery_period, g.is_build, i_biz.name AS biz_name,
					  i_biz.url AS biz_url					  
					FROM
					  `items` i
					  JOIN goods g ON i.id = g.id
					  LEFT JOIN items c ON c.pid = i.id
					  LEFT JOIN goods gc ON c.id = gc.id
					  LEFT JOIN biz b ON g.biz_id = b.id
					  LEFT JOIN items i_biz ON g.biz_id = i_biz.id
					  LEFT JOIN top_images ti ON ti.id = i.id
					WHERE
					  i.id = ?d
					GROUP BY
					  i.id',$cart['good_id']);
				$this->cart[$key]['good'] = new page($props);
				$this->cart[$key]['count'] = $cart['count'];
				$this->cart_count += $this->cart[$key]['count'];
				$this->cart[$key]['price_sum'] = $this->cart[$key]['good']['price']*$this->cart[$key]['count'];
				$this->cart_sum += $this->cart[$key]['price_sum'];
			}
			require_once 'smarty/libs/plugins/modifier.convert_currency.php';
			$this->cart_sum = smarty_modifier_convert_currency($this->cart_sum);			
		}
	}
	
	public function updateCartCount($key, $value)
	{
		if (isset($this->cart[$key])) {
			@session_start();
			$oldCount = $this->cart[$key]['count'];
			$this->cart[$key]['count'] = $_SESSION['cart'][$key]['count'] = $value;
			$this->cart_count += ($value - $oldCount);
			$goodPrice = $this->cart[$key]['good']['price'];
			$this->cart[$key]['price_sum'] = $value * $goodPrice;
			$this->cart_sum += ($value*$goodPrice - $oldCount*$goodPrice);
		}		
	}
	
	public function deleteFromCart($key)
	{
		if (isset($this->cart[$key])) {
			@session_start();
			$count = $this->cart[$key]['count'];
			$sum = $this->cart[$key]['price_sum'];
			unset($this->cart[$key]);
			unset($_SESSION['cart'][$key]);
			$this->cart_count -= $count;
			$this->cart_sum -= $sum;
			if (!count($_SESSION['cart'])) {
				unset($_SESSION['cart']);
			}
			return true;
		} else {
			return false;
		}
	}
	
	function getState()
	{
		return $this->login_state;
	}
	function getUserGroup()
	{
		return $this->group_name;
	}
	function getAccessLevel()
	{
		return $this->access_level;
	}
	function getNickName()
	{
		return $this->nickname;
	}
	function getEmail()
	{
		return $this->email!=null?$this->email:'';
	}
	function getId()
	{
		return $this->id?$this->id:0;
	}
	function getPageUrl()
	{
		return $this->pageUrl;
	}
	function getIp()
	{
		return $this->ip;
	}
	
	public function getCart()
	{		
		return $this->cart;
	}
	
	public function getCartCount()
	{
		return $this->cart_count;
	}
	
	public function getCartSum()
	{				
		return $this->cart_sum;
	}
}

//статический класс для хранения текущего юзера (заодно и автоматом инициализирует его как гостя в случае чего...)
class user
{
	static private $current=null;
	
	function init($login=null,$pass=null)
	{
		self::$current=new current_user($login,$pass,'form');
		return self::$current;
	}
	function retrieve($user)
	{
		if (self::$current!=null) throw new Exception('Какая-то фигня... нельзя во время работы сценария перезаписать уже инициализированного юзера...');
		self::$current=$user;
		//логи - можно еще сравнить REMOTE_ADDR с тем ip, что у нас в сессии хранится... будет прикольно если он вдруг поменяется.
		db::getDB()->query('INSERT into ?_userlog(user_id,action_type,ip,viewed_url) values(?,?,?,?)',$user->getId(),'view',$_SERVER['REMOTE_ADDR'],$_SERVER['REQUEST_URI']);//пришем вход в логи
		db::getDB()->query('UPDATE ?_users SET lastvisit=NOW() WHERE id=?',$user->getId());
	}
	function initById($id)
	{
		if (self::$current!=null) throw new Exception('Какая-то фигня... юзер уже есть, а идет попытка перегрузить его по id...');
		self::$current=new current_user($id,'','id');
		return self::$current;
	}
	function getCurrentUser()
	{
		if (self::$current==null) self::init();
		return self::$current;
	}
	function getLogState()
	{
		if (self::$current==null) self::init();
		return self::$current->getState();
	}
	function getUserGroup()
	{
		if (self::$current==null) self::init();
		return self::$current->getUserGroup();
	}
	function getAccessLevel()
	{
		if (self::$current==null) self::init();
		return self::$current->getAccessLevel();
	}
	function getNickName()
	{
		if (self::$current==null) self::init();
		return self::$current->getNickName();
	}
	function getEmail()
	{
		if (self::$current==null) self::init();
		return self::$current->getEmail();
	}
	function getId()
	{
		if (self::$current==null) self::init();
		return self::$current->getId();
	}
	function getIp()
	{
		if (self::$current==null) self::init();
		return self::$current->getIp();
	}
}

class Sources {
	
	private $_page;
	private static $_instance;
	
	private function __construct(current_page $page = null) {
		
		$this->_page = $page;
				
	}
	
	public function getInstance(current_page $page = null) {
		
		if (!self::$_instance) {
			self::$_instance = new self($page);
		}
		
		return self::$_instance;
		
	}
		
	public function __get($name) {
		
		return $this->__call($name,array());
		
	}
	
	public function __call($name,$args) {
		
		$f_name = isset($args[0]) ? $args[0] : 'name';
		$f_id = isset($args[1]) ? $args[1] : 'id';
		
		return db::getDB()->selectCol("SELECT $f_id AS ARRAY_KEYS, $f_name FROM ?_$name ORDER BY $f_name");
		
	}
	
	public function getRegions() {
		
		return db::getDB()->selectCol('SELECT id ARRAY_KEYS,name FROM ?_items WHERE template = ? ORDER BY name','region');
		
	}
	
	public function getCities($region) {
		
		return db::getDB()->selectCol('SELECT id ARRAY_KEYS,name FROM ?_items WHERE pid = ?d AND template = ? ORDER BY name',$region,'city');
		
	}
	
	public function getCarriers($region) {
		
	    $carriers = db::getDB()->select('
	    	SELECT
			  carriers.id,carriers.name
			FROM
			  items carriers
			  JOIN items offices ON offices.pid = carriers.id
			  JOIN carrier_offices co ON co.id = offices.id
			  WHERE co.city_id = ?d
			  GROUP BY carriers.id',$region);
	    
	    return $carriers;
		
	}
	
	public function getCarrierOffices($carrier,$city) {
		
	    $offices = db::getDB()->select('
	    	SELECT i.id,i.name FROM items i
	    	JOIN carrier_offices co ON co.id = i.id
			WHERE i.pid = ?d AND co.city_id = ?d AND i.template = ?',$carrier,$city,'carrier_office');
		
		return $offices;
		
	}
	
}

class db //тут коннектимся к БД и храним наш объект для доступа к ней
{
	static private $db=null;

	static function getDB()
	{
		if (self::$db==null)
		{
			self::$db=DbSimple_Generic::connect(config::getDBValue('dbtype').'://'.config::getDBValue('username').':'.config::getDBValue('password').'@'.config::getDBValue('host').'/'.config::getDBValue('dbname'));
			self::$db->setErrorHandler('databaseErrorHandler');
			self::$db->setIdentPrefix(config::getDBValue('table_prefix')); 
			//self::$db->setLogger('myLogger');
			self::$db->query('SET CHARACTER SET utf8');//кодировочку не забываем...
		}
		return self::$db;
	}
}

class page implements ArrayAccess
{
	private $url;				//адрес
	private $id;				//id
	private $pid;
	private $name;				//название короткое
	private $title;				//название расширенное
	private $description=null;	//описание страницы
	private $date=null;			//дата создания страницы
	private $top_image=null;	//путь к заглавной картинке
	private $parents;			//массив родителей страницы - объектов класса page;
	
	private $top_hover_img;
	
	/**
	 * Переданные из плагина аттрибуты страницы
	 *
	 * @var array
	 */
	private $page_attr;
	
	function __construct($page_attr)
	{
	    $this->page_attr = $page_attr;
		$this->url=$page_attr['url'];
		$this->id=$page_attr['id'];
		$this->name=$page_attr['name'];
		$this->title=$page_attr['title'];
		
		$this->top_hover_img = db::getDB()->selectCell('SELECT filename FROM top_hover_images WHERE id = ?',$this->id);
		
		if (isset($page_attr['description'])) $this->description=$page_attr['description'];
		if (isset($page_attr['create_date'])) $this->date=$page_attr['create_date'];
		if (isset($page_attr['img_src'])) $this->top_image=$page_attr['img_src'];
		if (isset($page_attr['comments_count'])) $this->comments_count=$page_attr['comments_count'];
		if (isset($page_attr['parents'])) $this->parents=$page_attr['parents'];
		// вообще-то эту хрень лучше будет оформить как я погляжу через _get и все принимаемые свойства держать в page_attr и из него и отдавать
		// не, не всю... какие-то свойства должны стандартными методами доставаться, а то поменл имя поля и можно все вызовы в шаблонах переписывать...
	}
	
	public function offsetGet($name)
	{
		return $this->page_attr[$name];
	}
	
	public function offsetExists($name)
	{
		return array_key_exists($name, $this->page_attr);
	}
	
	public function offsetSet($name, $value)
	{
		throw new Exception(__METHOD__ . ' not implemented');
	}
	
	public function offsetUnset($name)
	{
		throw new Exception(__METHOD__ . ' not implemented');
	}
	
	function getId()
	{
		return $this->id;
	}
	
	function getURL($real=false)
	{
		if ($this->url!='' or $real) 
		{
			if ($real==='slashed') return addcslashes($this->url,'_');// для использования в LIKE
			else return $this->url;
		}
		else return '/';
	}
	
	function getName()
	{
		return $this->name;
	}
	
	function getTitle()
	{
		return $this->title;
	}
	
	function getDescription()
	{
		return $this->description;
	}
	
	function getDate($format_type='short')
	{
		switch ($format_type)
		{
			case 'mysql': return $this->date;
			case 'long': return str2templateDate($this->date);
			default: return date('H:i d.m.y',strtotime($this->date));
		}
	}
	
	function getImage($no_default=false)
	{
        if ($this->top_image) {
            return config::getConfigValue('FOLDERS','top_images').$this->id.'/'.$this->top_image;
        } else {
            if ($no_default) return false;
            else return config::getConfigValue('FILES','default_top_image');
        }
	}
	
	public function getHoverImage() {
		
		if (!$this->top_hover_img) return false;
		return config::getConfigValue('FOLDERS','top_hover_images').$this->id.'/'.$this->top_hover_img;
		
	}
	
	function getHTMLlink($type='short')
	{
		if ($type=='long') return '<a href=\''.$this->getUrl().'\' title=\''.htmlspecialchars($this->name).'\'>'.htmlspecialchars($this->title).'</a>';
		elseif ($type=='article') return '<a class=\'article\' href=\''.$this->getUrl().'\' title=\''.htmlspecialchars($this->name).'\'>'.htmlspecialchars($this->title).'</a>';
		else return '<a href=\''.$this->getUrl().'\' title=\''.htmlspecialchars($this->title).'\'>'.htmlspecialchars($this->name).'</a>';
	}
	
	function getParentName()// этим кривым методом мы запрашиваем значение parents в тех случаях когда вместо массива в нем содержится просто имя родительского узла
	{
		return $this->parents;
	}
	
	function getParents($from_root=false)
	{
		if (!is_array($this->parents))		//в данном случае значением этого свойства будет массив (хотябы и пустой) если был вызов findParents()
					$this->findParents(); 
		if ($from_root) return $this->parents;
		else
		{
			$par=$this->parents;
			array_shift($par);
			return $par;
		}
	}
	
	//получение родителей страницы
	private function findParents()
	{
		$this->parents=array();
		$url_parts=explode('/',$this->url);
		$urls=array();
		while (count($url_parts)>1)		//если мы хотим чтобы главная страница со своим "пустым" url-ом тоже фигурировала в списке родителей - нужно писать >1
		{
			array_pop($url_parts);
			$urls[]=implode('/',$url_parts);
		}
		if (count($urls)) 
		{
			$page_attr=db::getDB()->select('SELECT id,url,name,title,template,type,pid from ?_items where url in (?a) order by url',$urls);//order by url обеспечит нам правильный порядок следования родителей
			// а такую версию можно заюзать чтобы не выводить в дорожке недоступные текущему пользователю элементы.
			//$page_attr=db::getDB()->select('SELECT id,url,name,title from ?_items where url in (?a) and protected<=? order by url',$urls,user::getAccessLevel());
			foreach ($page_attr as $page_a)
				$this->parents[]=new parent_page($page_a);
		}
		else $this->parents=array();
		
		//$this->parents=array_reverse($this->parents);//разворачиваем массив родителей чтобы он шел в прямом порядке
	}
	
	function getChildren($filter='all')//для того чтобы получить только дочерние элементы с установленным флагом menu_item $filter='menu_item'
	{
		$children=array();
		foreach (db::getDB()->select('SELECT id,url,name,title,pid from ?_items where pid=?d and protected<=? {and menu_item=?} order by sort',
										$this->getId(),user::getAccessLevel(),$filter=='menu_item'?1:DBSIMPLE_SKIP) as $child)
		{
			$children[]=new page($child);
		}
		return $children;
	}
	
	public function __get($name) {
	    
	    if (array_key_exists($name,$this->page_attr)) {
	        return $this->page_attr[$name];
	    }
	    throw new Exception("Page parameter $name does not exist");
	    
	}
	
	public function __isset($name)
	{
		return array_key_exists($name,$this->page_attr);
	}
}

class parent_page extends page 
{
	private $template; //темплейт страницы, ради этого и выделен в отдельный класс (юзается при поиске темплейта во from вызовах в конфиге)
	
	function __construct($page_attr)
	{
		parent::__construct($page_attr);
		if ($page_attr['template']!='') $this->template=$page_attr['template'];
		else $this->template=config::getTemplatesValue($page_attr['type']);
	}
	
	function getTemplate()
	{
		return $this->template;
	}
}

class page_ext extends parent_page //еще более развернутая версия page, умеющая оперировать доп. свойствами (помимо стандартных)
// achtung!!! походу неплохо было бы всю эту кашу привести впорядок и понаследовать current_page уже от кого-то из этих расширенных а не от page, покастрировав в ней дублирующиеся свойства и методы
{
	private $properties=array();
	private $std_prop=array('id','url','name','title','description','create_date','image_filename','comments_count','parents');
	function __construct($page_attr)
	{
		parent::__construct($page_attr);
		foreach ($page_attr as $pname=>$pval)
		{
			if (!in_array($pname,$this->std_prop)) $this->properties[$pname]=$pval;
		}
	}
	
	function __get($name)
	{
		if (array_key_exists($name,$this->properties)) return $this->properties[$name];
		else throw new Exception('Get not exist additional page parameter '.$name);
	}
	
}

class menu_element extends page
{
	private $is_parent;
	private $current;
	
	function __construct($page_attr)
	{
		parent::__construct($page_attr);
		$this->is_parent=isset($page_attr['is_parent']);
		$this->current=isset($page_attr['current']); 
	}
	
	function is_parent()
	{
		return $this->is_parent;
	}
	function current()
	{
		return $this->current;
	}
}

class current_page extends page 
{
	private $lang;
	public $sources;
	private $access;		//необходимый уровень доступа (целое число)
	private $type;
	private $template;
	private $config=null;
	private $additional_attr = array(); //доп. параметры из расширяющих связных с items таблиц, хранятся в массиве 
	private $module_params;	//сюда пихаются параметры вызовов для текущего модуля
	private $numerator_count; //всего страниц нумератора с именем 'блаблабла' (т.е. это массив)
	private $numerator_current; //текущая страница нумератора с именем 'блаблабла' (и это тоже)
	private $numerator_anchor; //якорь, добавляемый к ссылкам нумератора
	private $cache;
	private $pluginResults=array();
	//private $left_menu_start=null; //для формирования "продолжения" меню в вертикальной боковой колонке
									//необходимо знать на каком из родителей текущей страницы
									//мы столкнулись с ситуацией, когда среди его детей нет элементов
									//меню (т.е. имеет место пустой уровень)
//	private $numerator_count_per_page;
	//private $modules_result;		
	
	function __construct($params)
	{
		$this->sources = Sources::getInstance($this);								
		if (config::getDefaultLang()=='') $this->lang='';// моноязычная версия
		else
		{
			if ($params=='') $this->lang=config::getDefaultLangByDomain($_SERVER['HTTP_HOST']);
			else
			{
				$this->lang=substr($params,1,strcspn($params,'/',1));
				if (!in_array($this->lang,config::getLangs())) $this->lang=config::getDefaultLangByDomain($_SERVER['HTTP_HOST']);		
			}
			//--------------------- интернационализация статики
			putenv("LANGUAGE=".config::getLangLocale($this->lang));
			setlocale (LC_ALL,config::getLangLocale($this->lang));
			bind_textdomain_codeset(config::getProjectName(), "UTF8");
			bindtextdomain (config::getProjectName(), config::getConfigValue('FOLDERS','locale'));
			textdomain (config::getProjectName());
			//die ('"'.getenv('LANGUAGE').'" '.config::getProjectName().' '.config::getConfigValue('FOLDERS','locale').' ->'.setlocale(LC_ALL,config::getLangLocale($this->lang)));
			//---------------------- конец интернационализации статики
		}
		
		if (substr($params,0,3)=='/_/' or substr($params,0,7)=='/_mark/') //теперь в таблице для mark в мультиязычной версии нужно будет указывать несколько вариантов: например для /_mark/news  в таблице будет ru:news, en:news
		{
			if (substr($params,0,3)=='/_/') $marklength=3; else $marklength=7;
			if (config::getDefaultLang()!='') $mark_prefix=$this->lang.':'; else $mark_prefix='';
			$get=$_GET;
			unset($get['params']); //лень думать о том как оно сюда попадает и как его вообще убрать нафик
			$uri_parts = array();
  			foreach ($get as $k=>$v) $uri_parts[] = $k.'='.$v;
			$forward_url=db::getDB()->selectCell('SELECT items.url from ?_items as items, ?_marks as marks where marks.item_id=items.id and mark=?',$mark_prefix.substr($params,$marklength));
			if ($forward_url) {header('Location:/'.$forward_url.'?'.implode('&',$uri_parts)); die();}//нехорошо, нужно снабдить такой заголовок вменяемым контентом на тему состояния перенаправления, вдруг браузер не схавает location...
			else throw new Exception('Page not found (invalid mark:'.substr($params,$marklength).')',404);//а в мультиязычной версии может еще и марка с нужным префиксом не оказаться...
		}
		//!!! такие ajax - запросы на данный момент моноязычны и должны идти в базе БЕЗ ведущего слэша в мультиязычной системе и С НИМ в моноязычной... вот такой косяк пока...
		if (substr($params,0,7)=='/_ajax/') //проверка того что ajax вызван именно как ajax (предполагается, что ajax запросы идут с урлов начинающихся с _ajax хотя если не соблюдать это соглашение - то просто не будет соответствующей проверки)
		{
			if(!(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) throw new Exception('Incorrect call format for:'.$params.'. This block may call only from ajax requests.',404);
		}
		
		if ($params=='') $params=config::getDefaultLang(); //для мультизычного проекта этого можно и не делать... но и лишний if можно не писать...
		elseif (config::getDefaultLang()!='') $params=substr($params,1); //удаляем ведущий слэш в мультиязычной версии в некорневой странице (там его нет...)
		
		$page_attr=db::getDB()->selectRow('SELECT ?_items.id,pid,url,name,title,description,create_date,protected,type,template,comments_count,filename as image_filename from ?_items left join ?_top_images on ?_top_images.id=?_items.id where url=?',$params);
		//вернет пустой массив если строка не найдена
		
		if (count($page_attr)==0) throw new Exception('Page not found: '.$params,404);

		$this->access=$page_attr['protected'];
		//если уровень доступа текущего пользователя ниже требуемого для просмотра страницы -> forbidden

		if ($this->access > user::getAccessLevel())	throw new Exception($this->access.':'.user::getAccessLevel().' Forbidden',403);

		//остальные атрибуты страницы
		parent::__construct($page_attr);
		$this->type=$page_attr['type'];	
		
			
		//получаем дополнительные параметры страницы из связных с items таблиц
		if (config::getTTMValue($this->type)!=null) $this->additional_attr=db::getDB()->selectRow('SELECT * from ?_'.config::getTTMValue($this->type).' as add_tab where add_tab.id=?d',$this->getId());//ну что поделать если все плэйсхолдеры обрамляются кавычками...
		
		if ($page_attr['template']!='') $this->template=$page_attr['template'];
		else $this->template=config::getTemplatesValue($this->type); //если поле шаблона пустое - берем из конфига стандартный шаблон для данного типа страниц
	}
	
	private function _implodeQueryParams($params, $name=null)
	{
		$ret = ''; 
		foreach($params as $key=>$val) {
			if(is_array($val)) {
				if($name==null) $ret .= $this->_implodeQueryParams($val, $key);
				else $ret .= $this->_implodeQueryParams($val, $name."[$key]");
		    } else {
				if($name!=null) $ret.=$name."[$key]"."=$val&";
				else $ret.= "$key=$val&";
		    }
		}
		return $ret;
	}
	
	public function getQueryString()
	{
		$excludeParams = func_get_args();
		if (!$excludeParams) {
			return $_SERVER['QUERY_STRING'];
		}
		$queryParams = array();
		parse_str($_SERVER['QUERY_STRING'], $queryParams);		
		foreach ($excludeParams as $param) {
			if (isset($queryParams[$param])) {
				unset($queryParams[$param]);								
			}
		}
		return $this->_implodeQueryParams($queryParams);
	}
    
    protected function get_simple_construct($page_attr) //этот метод юзается в потомке 404 для вызова конструктора от класса page
    {
        if (get_class($this)=='page_404' or get_class($this)=='page_403')
        {
            parent::__construct($page_attr);
            $this->access=$page_attr['access'];        //необходимый уровень доступа (целое число)
            $this->type=$page_attr['type'];
            $this->template=$page_attr['template'];
            $this->config=null;
        }
    }
	
	function getLang()
	{
		//if ($this->lang=='adm') return 'rus';
		return $this->lang;
	}
	
	function getTemplate()
	{
		return $this->template;
	}
	
	function getType()
	{
		return $this->type;
	}
	
	function getPluginResult($plugin_name)
	{
		if (array_key_exists($plugin_name, $this->pluginResults)) return $this->pluginResults[$plugin_name];
		else throw new Exception('Plugin '.$plugin_name.' is not run in current config file.');
	}
	
	function getAccess()//а нужна ли она будет?...
	{
		return $this->access;
	}
	
	function issetParam($paramName)
	{
		return isset($this->module_params[$paramName]);
	}
	
	function getParam($paramName)
	{
		if (isset($this->module_params[$paramName])) return $this->module_params[$paramName];
		else throw new Exception('Cannot get undefined module parameter '.$paramName.'. Define this parameted in config file.');
	}
	
	function getParams()//возвращает весь массив (или не массив, если параметр 1...)
	{
		if (isset($this->module_params)) return $this->module_params;
		else throw new Exception('Can not return module parameter because no one parameter is set.');
	}
	
//	function getLimit()
//	{
//		//если есть numerator_name но нет count - будет ошибка, (и правильно...)
//		$from=$this->issetParam('numerator_name')?$this->numerator_current[$this->getParam('numerator_name')]*$this->getParam('count'):0;
//		$count=$this->issetParam('count')?$this->getParam('count'):DBSIMPLE_SKIP;
//		return array($from,$count);
//	}
	
	function getLimitFrom()
	{
		//если есть numerator_name но нет count - будет ошибка, (и правильно...)
		return $this->issetParam('numerator_name')?$this->numerator_current[$this->getParam('numerator_name')]*$this->getParam('count'):0;
	}
	
	function getLimitCount()
	{
		return $this->issetParam('count')?$this->getParam('count'):DBSIMPLE_SKIP;
	}
	
	function getMenu()
	{
		$menu=array();
		foreach($this->getParents(true) as $parent)
		{
			$level=$parent->getChildren('menu_item');
			if (count($level)>0) $menu[]=$level;
			//else if ($this->left_menu_start==null) $this->left_menu_start=$parent->getId(); //запоминаем id первого родителя с нулевым (или очень большим (потом)) количеством элементов меню
		}
		$level=$this->getChildren('menu_item');
		if (count($level)>0) $menu[]=$level;
		//else if ($this->left_menu_start==null) $this->left_menu_start=$this->getId();
		
		return $menu;
	}
	
//	function getLeftMenu()//по идее, если будет вызвано до getMenu() - получится ашипка...
//	{
//		$menu=array();
//		$flag=false;
//		foreach ($this->getParents() as $parent)
//		{
//			if ($parent->getId()==$this->left_menu_start) $flag=true;
//			if ($flag)
//			{
//				$menu[]=$parent->getChildren('containers');
//			}
//		}
//	}
	
	//по идее, если параметры передавать через свойство $params какое-нибудь, то во время вызова setNumerator из внешнего модуля его count и name будут уже лежать в этом свойстве...
	function setNumerator($allCount) //устанавливает количество страниц нумератора
	{
		$page=isset($_GET[$this->getParam('numerator_name')])?$_GET[$this->getParam('numerator_name')]-1:0; //определим номер текущей страницы
		if ($page<0) $page=0;
		$pageCount=ceil($allCount/$this->getParam('count'));//отследить тему когда не дай бог писатели модулей тупанули и count среди параметров нет
		if ($page>=$pageCount) $page=$pageCount==0?0:$pageCount-1;
		$this->numerator_current[$this->getParam('numerator_name')]=$page;
		$this->numerator_count[$this->getParam('numerator_name')]=$pageCount;
		if ($this->issetParam('anchor')) $this->numerator_anchor[$this->getParam('numerator_name')]='#'.$this->getParam('anchor');
		else $this->numerator_anchor[$this->getParam('numerator_name')]='';
//		$this->numerator_count_per_page[$this->getParam('numerator_name')]=$this->getParam('count');
	}
	
	function getNumeratorCount($name)
	{
		if (isset($this->numerator_count[$name])) return $this->numerator_count[$name];
		else throw new Exception('Undefined name of pageNumerator: '.$name);
	}
	
	function getNumeratorCurrent($name)
	{
		if (isset($this->numerator_current[$name])) return $this->numerator_current[$name];
		else throw new Exception('Undefined name of pageNumerator: '.$name);
	}
	
	function getNumeratorAnchor($name)
	{
		if (isset($this->numerator_anchor[$name])) return $this->numerator_anchor[$name];
		else throw new Exception('Undefined name of pageNumerator: '.$name);
	}
	
//	function getNumeratorPerPage($name)//а оно надо?
//	{
//		if (isset($this->numerator_count_per_page[$name])) return $this->numerator_count_per_page[$name];
//		else throw new Exception('Undefined name of pageNumerator:'.$name);
//	}
	
	function getNumerator($name)//неплохо было бы подумать над тем чтобы нумератор не затирал другие параметры в гете (когда таковые появятся): подумано
	{
		$numerator=array();
		$count=$this->getNumeratorCount($name);
		$current=$this->getNumeratorCurrent($name)+1;
		for ($i=1;$i<=$count;$i++)
		{
			if ($i!=$current) 
			{
				$num=array('name'=>$i,'link'=>$this->getUrl().'?'.$name.'='.$i);
				foreach ($_GET as $key=>$value)
				{
					if ($key!=$name && $key!='params') $num['link'].='&'.$key.'='.$value;//params - туда запихивается url
				}
				$numerator[]=$num;
			}
			else $numerator[]=array('name'=>$i,'current'=>true);
		}
		return $numerator;
	}
	
//	function __get($name)
//	{
//		if (isset($this->modules_params[$name])) return $this->modules_params[$name];
//		else throw new Exception('Get parameters for not exist module '.$name);
//	}
	
	function __call($name,$params)
	{
		if (!function_exists($name))
		{
			$module_file=config::getConfigValue('FOLDERS','plugins').$name.'.php';
			if (!file_exists($module_file)) throw new Exception('Not exist module file '.$module_file.' for undefined method '.$name);
			require_once($module_file);
		}
		// вот тут интересное место, попытаемся рыализовать вызов модуля от родительской страницы.
		// какие могут быть варианты: просто from:parent, from:имя_темплейта - поиск ближайшего темплейта среди родителей
		// сделаем так, чтобы если соответсвующий темплейт не найден среди родителей блок вообще не выполнялся
		// хотя в принципе не трудно будет изменить логику чтобы выполнялся для текущей страницы.
		
		//Внимание! Параметры вызова берутся из текущего вызова а не родительского (можно потом доделать чтобы они их переопределяли)
		
		if (isset($params[0]) && is_array($params[0]) && isset($params[0]['from'])) //потому как если прийдет строка, такие чудеса начнутся...
		{
			$target=null;
			if ($params[0]['from']=='parent') $target=array_pop($this->getParents(true)); //// хм... и наверное не стоит писать from:parent в конфиге главной страницы :)
			else foreach (array_reverse($this->getParents(true)) as $parent)
			{
				if ($parent->getTemplate()==$params[0]['from'])
				{
					$target=$parent;
					break;
				}
			}
			if ($target!=null)
			{
				$obj= new current_page($target->getUrl(true));
				$params[0]['called_from']=$this; //модулю не помешает знать откуда на самом деле его вызвали (ну и что что большой объект, все равно по ссылке...)
				$obj->module_params=$params[0];
				return call_user_func($name,$obj);
			}
		}
		else
		{	
			if (isset($params[0])) 
			{
				$this->module_params=$params[0];//используем module_params для передачи вместе с объектом параметров вызова функции
				//isset() т.к. на данный момент из секции PLUGINS все функции вызываются без всяких параметров
			 	$cache_id=md5($name.$params[0].$this->getId());
			}
			else $cache_id=md5($name.$this->getId());
			if (!isset($this->cache[$cache_id]))
				$this->cache[$cache_id]=call_user_func($name,$this);
			return $this->cache[$cache_id];
			//$a=call_user_func($name,$this);
			//echo '<br>result of call <b>'.$name.'</b>:<br>';
			//var_dump ($a); echo "<br><br>";
			//return $a;
		}
	}
	
	function getParentCaller($params)
	{
		if (isset($params['section'])&&isset($params['item'])&&isset($params['from']))
		{
			$obj=null;
			if ($params['from']=='parent')
			{
				$obj=new current_page(array_pop($this->getParents(true))->getUrl(true));
				if (!$obj->issetConfigValue($params['section'],$params['item'])) $obj=null;
			}
			elseif ($params['from']=='parents') foreach (array_reverse($this->getParents(true)) as $parent)
			{
				$obj=new current_page($parent->getUrl(true));
				if ($obj->issetConfigValue($params['section'],$params['item']))
				{
					break;
				}
				else $obj=null;
			}
			if ($obj!=null)
			{
				$parent_params=$obj->getConfigValue($params['section'],$params['item']);
				unset ($params['from']);
				unset ($params['section']);
				unset ($params['item']);
				unset ($params['file']);
				$params=array_merge($parent_params,$params);
				return array ('params'=>$params,'obj'=>$obj);
			}
			else return false;
		}
		else throw new Exception('Need \'section\',  \'item\' and \'from\' parameters for call_parent mode in config file: '.$this->getTemplate());
	}

	
	function getConfigValue($section,$name=null) //получает значение или секцию из файла конфигурации текщей страницы
	{
		if ($this->config==null) $this->config=config::getConfigFile('pages/'.$this->template.'.conf');
		if (isset($this->config[$section]))
			if ($name==null) return $this->config[$section];
			else 
				if (isset($this->config[$section][$name])) return $this->config[$section][$name];
				else throw new Exception('Undefined config value '.$name.' in section '.$section.' in config file '.$this->template);
		else throw new Exception('Undefined config section '.$section.' in config file '.$this->template );
	}
	
	function issetConfigValue($section,$name=null)
	{
		if ($this->config==null) $this->config=config::getConfigFile('pages/'.$this->template.'.conf');
		if ($name!=null) return isset($this->config[$section][$name]);
		else return isset($this->config[$section]);
	}
	
	function getLangDomain($lang=null) // почти проксик к статике в config для смарти
	{
		if ($lang==null) $lang=$this->getLang();
		return config::getLangDomain($lang);
	}

	//как бы не совсем понятно теперь, нужен ли вообще этот метод... т.е. стоит ли прогонять все модули складируя результаты
	//их работы куда-то в объект, который потом отдастся в смарти и там будут юзаться его методы для доступа к предварительно
	//"сложенным" свойствам, или тупо вызывать интересующие методы объекта прямо из смарти... т.е. MVC это классно, но какая 
	//в принципе разница, подготовятся ли данные зарание или будут запрошены у объекта, готового отдать их в любой момент уже
	//в процессе работы шаблона?...
	//таки нужен... причем теперь можно "и так и так" :) (только раздел плагинов пока не умеет принимать параметры из конфига)
	function executeModules()
	{
		foreach ( $this->getConfigValue('PLUGINS') as $plugin)
		{
			//$name=$plugin['file'];
			//array_shift($plugin);
			$this->pluginResults[$plugin]=$this->$plugin();//а вот тут надо как-то будет синхронизироваться с вызовом в обычных шаблонах, т.к. там параметры через запятую перечисляются, а тут ассоциативный массив параметров который запихнется в 0-й элемент
			//а результаты запишем... хотя лучше их хранить не по имени плагина, а по имени ключа в конфиге (оставим на потом)
		}
	}
	
	function display()
	{
		$smarty = new Smarty();
		$smarty->template_dir = config::getConfigValue('FOLDERS','templates');
		$smarty->cache_dir    = '../cache';
		$smarty->compile_dir  = '../templates_c';
		$smarty->config_dir   = '../smarty/vip/config';
		$smarty->debug = false;
		$page_extender_file=config::getConfigValue('FOLDERS','page_extenders').'/'.$this->getTemplate().'.php';
		// эта хрень подключает расширяющий current_page класс, который называется по имени шаблона (или типа если его нет) текущей страницы, если такой файл есть (файл называется также).
		// класс должен экстендиться от абстрактного page_proxy
		if (file_exists($page_extender_file))
		{
			require_once($page_extender_file);
			$class_name = $this->getTemplate();
			$smarty->assign('page',new $class_name($this));
		}// все остальные страницы расширяются классом default_extender, если такой файл есть в extenders
		elseif (file_exists(config::getConfigValue('FOLDERS','page_extenders').'/default.php'))
		{
			require_once(config::getConfigValue('FOLDERS','page_extenders').'/default.php');
			$smarty->assign('page',new default_extender($this));
		}
		else $smarty->assign('page',$this);
		$smarty->assign('parents',$this->getParents());//для отметки активных разделов меню
		$smarty->assign('user',user::getCurrentUser());
		//$smarty->assign('main_config',new main_config());// надоело коряво статический объект из смарти вызывать...
		if (config::getDefaultLang()!='') $smarty->assign('langs',config::getLangs());
		// --------------- обработка ajax вызовов в среде текущей страницы
		if((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) 
		{
			if (isset($_GET['pname'])) //вызов непосредственно плагина по имени (предполагается что плагин будет чето "эхать" потому как другого вывода никакого (если не указан какой-нибудь ajax-вызов темплейта не будет))
			{
				$plugname=$_GET['pname'];
				if (in_array($plugname,config::getAjaxAllowPlugins()))
					$this->$plugname();
				else die ('Not allowed plugin for ajax call.'); // пока вот так.
			}
						
			if (isset($_GET['tname'])) //вызов непосредственно темплейта по имени (таким макаром пока нельзя передать для него доп.параметры)
			{
				$tname=$_GET['tname'];
				if (in_array($tname,config::getAjaxAllowTemplates())) 
					$smarty->display($tname);
				else die ('Not allowed template for ajax call.'); // пока вот так.
			}
			elseif (isset($_GET['section'])) //вызов секции или конкретного модуля
			{
				$section=$_GET['section'];
				if (!isset($_GET['module'])) //вся секция
				{
					if (!$this->issetConfigValue($section)) throw new Exception('Undefined section name "'.$section.'" in ajax call.');
					foreach ($this->getConfigValue($section) as $module)
					{
						// ---- и тута пожалуй стоит системный шаблон какой-нить с тупым форичем вызвать дабы не складывать результаты работы модулей в буфер
					}
				}
				else // модуль
				{
					$module=$_GET['module'];
					if (!$this->issetConfigValue($section,$module)) throw new Exception('Undefined module name "'.$module.'" in section "'.$section.'" in ajax call.');
					$config_row=$this->getConfigValue($section,$module);
					$smarty->display($config_row['file']);
				}
			}
			//else throw new Exception('Undefined type of ajax page call.');// чета я не понял, а как тогда старый метод будет работать?...
			else if (!isset($_GET['pname'])) throw new Exception('Undefined type of ajax page call.'); // if (!isset($_GET['pname'])) гы-гы-гы.... :)
			//else $smarty->display($this->getConfigValue('SETTINGS','html_template')); //так было раньше, хз почему...
		} 
		//---------------- генерация обычной страницы
		else $smarty->display($this->getConfigValue('SETTINGS','html_template'));
	}
	
	/**
	 * Check for child item
	 *
	 * @param string $url url of parent item
	 * @return bool
	 */
	public function isChild($url) {
		
		return strpos($this->getUrl().'/',$url.'/')!==false;
		
	}
	
	protected function setAdditionalParams($params) {
		$this->additional_attr = $params;
	}
	
	public function getLevel() {
		
		$chars = count_chars($this->getURL());		
		return $chars[ord('/')];
		
	}
	
	public function getNearestParent()
	{
		return array_pop($this->getParents());
	}
}

class Insertion extends current_page {
	
	public function __construct($params) {
		
		parent::__construct($params['url']);
		$this->setAdditionalParams($params);
		
	}
	
	public function getPhotos() {
		
		return db::getDB()->selectCol('select filename from insertion_photos where insertion_id = ?d',$this->getId());
		
	}
	
}

class page_404 extends current_page 
{
    function __construct()
    {
        parent::get_simple_construct(array(
            'url'=>'/404',
            'id'=>NULL,
            'name'=>'Ничего не найдено',
            'title'=>'404:Page not found',
            'access'=>0,        //необходимый уровень доступа (целое число)
            'type'=>'_service',
            'template'=>'404',
            ));//все это очень спорно...
    }
}

class page_403 extends current_page 
{
    function __construct($access_level)
    {
        parent::get_simple_construct(array(
            'url'=>'/403',
            'id'=>NULL,
            'name'=>'В доступе отказано',
            'title'=>'403:В доступе отказано',
            'access'=>$access_level,        //необходимый уровень доступа (целое число)
            'type'=>'_service',
            'template'=>'403',
            ));//все это очень спорно...
    }
}

function str2templateDate($strDate)
{
	$months=array(1=>'января',2=>'февраля',3=>'марта',4=>'апреля',5=>'мая',6=>'июня',7=>'июля',8=>'августа',9=>'сентября',10=>'октября',11=>'ноября',12=>'декабря');
	$unix=strtotime($strDate);
	return date('H:i j ',$unix).$months[date('n',$unix)].date(' Y',$unix);
}
?>