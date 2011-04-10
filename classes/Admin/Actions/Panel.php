<?php

/**
 * Класс панели действий
 *
 */
abstract class Admin_Actions_Panel implements Admin_IController {
	
	const PATH_TO_ACTIONS_PANEL_CONFIGS = 'actions_panel/';
	
	const SECTION_ACTIONS = 'ACTIONS';
	
	/**
	 * Результирующий список действий для текущего элемента
	 *
	 * @var array
	 */
	protected $actions_list;
	
	/**
	 * Список иденификаторов действий для текущего элемента
	 *
	 * @var array
	 */
	private $actions_ident;
	
	/**
	 * Группы (join) действий
	 *
	 * @var array
	 */
	private $actions_groups;
	
	/**
	 * Общая конфигурация действий
	 *
	 * @var array
	 */
	private $common_config;
	
	/**
	 * Типы действий (действия с элементом и глобальные действия)
	 *
	 * @var array
	 */
	protected $actions_types=array('item','global');
	
	/**
	 * id элемента
	 *
	 * @var integer
	 */
	protected $item_id;
	
	/**
	 * Массив плейсхолдеров
	 *
	 * @var array
	 */
	protected $placeholders;
	
	/**
	 * @var Zend_Acl
	 */
	private $_acl;
	
	public function __construct() {

		$this->_acl = new Admin_Auth_Acl();
		foreach (Admin_Core::getObjectDatabase()->selectCol('SELECT group_name FROM ?_groups') as $role) {
			$this->_acl->addRole(new Zend_Acl_Role($role));
		}
		
		if (Zend_Auth::getInstance()->hasIdentity()) {
			// наследование роли пользователя от роли группы
			$this->_acl->addRole(new Zend_Acl_Role(Admin_Core::getAuth()->login),array(Admin_Core::getAuth()->group_name));
		}
		
	}
	
	/**
	 * Переопределение названий действий в зависимости от определенных условий
	 *
	 * @param string $action_ident идентификатор действия
	 * @param string $action_name название действия
	 */
	abstract protected function redefiningNames ($action_ident,$action_name);
	
	/**
	 * Устанавливет id элемента
	 *
	 */
	protected function setItemId () {
		
		$this->item_id = Admin_Core::getItemId();
	
	}
	
	/**
	 * Callback функция для сортировки действий
	 *
	 * @param array $a
	 * @param array $b
	 * @return integer
	 */
	private function fCmp ($a,$b) {
	    
	    $a['order'] = isset($a['order'])?$a['order']:0;
	    $b['order'] = isset($b['order'])?$b['order']:0;
	    if ($a['order']==$b['order']) {
            return 0;
        }
        return ($a['order']>$b['order'])?-1:1;
	    
	}
	
	private function sortByOrder (&$array) {
	    
	    uasort($array,array($this,'fCmp'));
	    
	}
	
	/**
	 * Синтаксический разбор секции в файле конфигурации
	 *
	 * @param Admin_Template_Config $objectGlobalConfig
	 * @param Admin_Template_Config $objectItemConfig
	 * @return array список действий
	 */
	private function parseConfig ($objectGlobalConfig,$objectItemConfig=NULL) {
		
		$actions_list = array();
		// объединяем общую конфигурацию и конфигурацию элемента
		if ($objectItemConfig!=NULL) {
			$commonConfig = array_merge($objectGlobalConfig->getParsedConfig(),$objectItemConfig->getParsedConfig());
		} else {
			$commonConfig = $objectGlobalConfig->getParsedConfig();
		}
		foreach ($commonConfig as $action_ident=>$params) {
			
			// настройка досупа к ресурсам
			$this->_acl->add(new Zend_Acl_Resource($action_ident));			
			
		    // пропускаем действия, запрещенные для показа
		    if (isset($params['show'])) {
		        if ($params['show']=='-') {
		        	$this->_acl->deny(null,$action_ident);
		        	continue;
		        }
		    }

		    
		    if (!isset($params['allow']) && !isset($params['deny'])) {
		    	// по умолчанию доступ закрыт всем аутентифицированным пользователям
    			$this->_acl->deny(null,$action_ident);
		    } else {
		    
			    foreach ($params as $param_name=>$param_value) {
			    	
			    	switch ($param_name) {
			    		case 'allow':
			    			$this->_acl->allow($param_value,$action_ident);
			    		break;
			    		case 'deny':
			    			$this->_acl->deny($param_value,$action_ident);
			    		break;
			    	}
			    	
			    }
			    
		    }
		    
		    // порядок вывода по умолчанию
		    if (!isset($params['order'])) {
		        $params['order'] = 0;
		    }
		    if (!isset($params['add_id'])) {
		        $params['add_id'] = 1;
		    }
		    // скрытие в меню
			if (!isset($params['hidden'])) {
		        $params['hidden'] = 0;
		    }
		    // пропускаем группировки действий
		    if (strpos($action_ident,'join_')!==false) {
		        if (!isset($params['actions']) || !isset($params['name'])) {
		            throw new Admin_ActionsPanelException('Не заданы обязательные атрибуты actions и name для группы действий '.$action_ident);
		        }
		        $action_group_ident = str_replace('join_','',$action_ident);
		        $this->actions_groups[$action_group_ident]['actions'] = Admin_Template_Config::explode(',',$params['actions']);
		        $this->actions_groups[$action_group_ident]['name'] = $params['name'];
		        $this->actions_groups[$action_group_ident]['order'] = $params['order'];
		        $this->actions_groups[$action_group_ident]['type'] = $params['type'];
		        $this->actions_groups[$action_group_ident]['hidden'] = $params['hidden'];
		        continue;
		    }
		    // группа для действия
		    if ($params['type']=='global') {
		        $group = $params['type'];
		    } else {
		        $group = 'item';
		    }
			$actions_list[$group][$action_ident] = $params;
			// определяем, нужно ли передавать id через параметр GET
			$id_part = '';
			if ($params['add_id']==1) {
				$id_part = '?id='.$this->item_id;
			}
			// определяем url
		    if (!isset($params['type']) || $params['type']==$action_ident) {
				$actions_list[$group][$action_ident]['url'] = SITE_SUBDIR."/$action_ident$id_part";
			} else {
				$actions_list[$group][$action_ident]['url'] = SITE_SUBDIR."/{$params['type']}/$action_ident$id_part";
			}
			// переопределяем название действия
            $actions_list[$group][$action_ident]['name'] = Admin_Template_Config::ph_replace($this->redefiningNames($action_ident,$params['name']),$this->placeholders);
            $actions_list[$group][$action_ident]['order'] = $params['order'];
            $actions_list[$group][$action_ident]['hidden'] = $params['hidden'];
            // определяем, является ли действие текущим
			$actions_list[$group][$action_ident]['is_current'] = Admin_Core::getActionName()==$action_ident;
			$actions_list[$group][$action_ident]['display'] = !$this->getAcl()->isAllowed(Zend_Auth::getInstance()->getIdentity(),$action_ident) ? 0 :
				(isset($params['display'])?$params['display']:1);
			// добавляем идентификатор действия
			$this->actions_ident[] = $action_ident;
			
		}
		
		// группируем действия
		$actions_grouped = $this->joinToGroups($actions_list);
		// объединяем с несгруппированными действиями
		$actions_no_grouped = $this->getActionsWithoutGroup($actions_list);
		$actions_list = array();
		foreach ($this->actions_types as $type) {
			if (isset($actions_grouped[$type]) && isset($actions_no_grouped[$type])) {
				$actions_list[$type] = array_merge($actions_grouped[$type],$actions_no_grouped[$type]);
			} elseif (isset($actions_grouped[$type]) && !isset($actions_no_grouped[$type])) {
				$actions_list[$type] = $actions_grouped[$type];
			} elseif (isset($actions_no_grouped[$type])) {
				$actions_list[$type] = $actions_no_grouped[$type];
			} else {
				$actions_list[$type] = array();
			}
		}
		
		// сортировка по параметру order
		foreach ($this->actions_types as $type) {
		    foreach ($actions_list[$type] as $action_ident=>$action) {
		        // проверяем, является ли действие группой
		    	if (isset($action['actions'])) {
		    	    // сортировка действий внутри группы
		    	    $this->sortByOrder($actions_list[$type][$action_ident]['actions']);
		    	}
		    }
		    // сортировка действий вне групп
		    $this->sortByOrder($actions_list[$type]);
		}
//		Admin_Errors::prnt_array($actions_list);
		return $actions_list;
		
	}
	
	/**
	 * Объединяет в действия в группы
	 *
	 * @param array $actions_list
	 */
	private function joinToGroups ($actions_list) {
	    
	    if (!$this->actions_groups) return NULL;
	    $actions_grouped_list = array();
	    foreach ($this->actions_groups as $group_ident=>$params) {
	        // проходим по типам действий
	        foreach ($this->actions_types as $type) {
	        	if (isset($actions_list[$type])) {
	            	$actions[$type] = $this->getActionsInGroup($group_ident,$actions_list[$type]);
	        	} else {
	        		$actions[$type] = array();
	        	}
	            if (count($actions[$type])) {
	                $actions_grouped_list[$type][$group_ident]['actions'] = $actions[$type];
	                $actions_grouped_list[$type][$group_ident]['name'] = $params['name'];
	                $actions_grouped_list[$type][$group_ident]['order'] = $params['order'];
	                $actions_grouped_list[$type][$group_ident]['type'] = $params['type'];
	                $actions_grouped_list[$type][$group_ident]['hidden'] = $params['hidden'];
	                $actions_grouped_list[$type][$group_ident]['display'] = isset($params['display'])?$params['display']:1;	                
	                if (isset($params['url'])) {
	                	$actions_grouped_list[$type][$group_ident]['url'] = $params['url'];
	                }
	            }
	        }
	        
	    }
	    return $actions_grouped_list;
	    
	}
	
	/**
	 * Возвращает действия, относящиеся к определенной группе
	 *
	 * @param string $group_ident
	 * @param array $actions_list
	 */
	private function getActionsInGroup ($group_ident,$actions_list) {
	    
	    if (!isset($actions_list)) return false;
	    $actions_grouped = array();
	    $actions_in_group = $this->actions_groups[$group_ident]['actions'];
        foreach ($actions_list as $action_ident=>$params) {
        	if (in_array($action_ident,$actions_in_group)) {
        	    $actions_grouped[$action_ident] = $params;
        	}
        }
        return $actions_grouped;
	    
	}
	
	/**
	 * Возвращает действия без определенной группы
	 *
	 * @param string $group_ident
	 * @param array $actions_list
	 */
	private function getActionsWithoutGroup ($actions_list) {
	    
	    $actions_no_grouped = array();
	    // получаем все сгруппированные действия
	    $actions_grouped = array();
	    if ($this->actions_groups) {
    	    foreach ($this->actions_groups as $params) {
    	    	$actions_grouped = array_merge($params['actions'],$actions_grouped);
    	    }
	    }
	    
	    // получаем все действия
	    $actions_all = array();
	    foreach ($this->actions_types as $type) {
	    	if (isset($actions_list[$type])) {
	        	$actions_all = array_merge(array_keys($actions_list[$type]),$actions_all);
	    	}
	    }
	    
	    foreach ($this->actions_types as $type) {
	        foreach (array_diff($actions_all,$actions_grouped) as $action_ident) {
	            if (isset($actions_list[$type][$action_ident])) {
                    $actions_no_grouped[$type][$action_ident] = $actions_list[$type][$action_ident];
	            }
            }
	    }
        return $actions_no_grouped;
	    
	}
	
	/**
	 * Устанавливает массив общей конфигурации действий
	 *
	 */
	public function setCommonConfig () {
		
		$objectGlobalConfig = Admin_Core::getObjectGlobalConfig();
		foreach ($objectGlobalConfig->getConfigSection(self::SECTION_ACTIONS) as $name => $params) {
			$this->common_config[$name] = array();
			foreach (Admin_Template_Config::explode(';',$params) as $param) {
				$param = trim($param);
				list($param_name,$param_value) = Admin_Template_Config::explode(':',$param);
				$this->common_config[$name][$param_name] = $param_value;
			}			
		}
		
	}
	
	/**
	 * Устанавливает результирующий список действий
	 *
	 */
	protected function setActionsList () {

		// из конфигурации действий текущего элемента
		try {
			$objectItemActions = new Admin_Template_Config(
				SYSTEM_PATH.Admin_Core::PATH_TO_CONFIGS.
				Admin_Core::PATH_TO_ADMIN_CONFIGS.
				self::PATH_TO_ACTIONS_PANEL_CONFIGS.
				Admin_Core::getItemConfigName().'.conf');
		} catch (Admin_TemplateConfigException $e) {
			Admin_Errors::add('',$e);
			$objectItemActions = NULL;
		}
		
		// из общей конфигурации действий
		$objectGlobalActions = new Admin_Template_Config(
				SYSTEM_PATH.Admin_Core::PATH_TO_CONFIGS.
				Admin_Core::PATH_TO_ADMIN_CONFIGS.
				self::PATH_TO_ACTIONS_PANEL_CONFIGS.
				'_main.conf');

		$this->actions_list = $this->parseConfig($objectGlobalActions,$objectItemActions);
		
	}
	
	/**
	 * Возвращает идентификаторы групп, к которым принадлежит действие
	 *
	 * @param string $action_ident
	 * @return array
	 */
	public function getActionGroup ($action_ident) {
		
		$groups = array();
		if (!$this->actions_groups) return $groups;
		foreach ($this->actions_groups as $ident=>$params) {
			if (in_array($action_ident,$params['actions'])) {
				$groups[] = $ident;
			}
		}
		return $groups;
		
	}
	
	/**
	 * Возвращает объект переопеделенной конфигурации
	 *
	 * @param string $action_ident
	 * @return string
	 */
	public function getOverrideConfigName ($action_ident) {
		
		if (isset($this->common_config['override_conf'][$action_ident])) {
			return $this->common_config['override_conf'][$action_ident].'.conf';
		} else {
			throw new Admin_FormBuilderException('Отсутствует переопределенная конфигурация для действия '.$action_ident);
		}
		
	}
	
	/**
	 * Возвращает список идентификаторов действий
	 *
	 * @return array
	 */
	public function getActionsIdent () {
	    
	    return $this->actions_ident;
	    
	}
	
	/**
	 * @return Zend_Acl
	 */
	public function getAcl() {
		return $this->_acl;
	}

	/**
	 * @return Admin_Actions_Panel
	 */
	public static function getInstance() {
		
		return self();
		
	}
	
}

?>
