<?php

/**
 * Класс для работы с потомками элемента
 *
 */
abstract class Admin_Childs_Abstract implements Admin_IController {
	
	const SECTION_CHILDS = 'CHILDS';
	
	/**
	 * Объект модели
	 *
	 * @var Admin_Model_Common
	 */
	protected $objectModel;
	
	/**
	 * Массив конфигурации
	 *
	 * @var array
	 */
	protected $config;
	
	public function __construct () {
		
		$this->objectModel = Admin_Controller_Factory::createObject('Admin_Model',Admin_Core::getItemConfigName(),Admin_Core::getActionClassName());
	
	}
	
	/**
     * Синтаксический разбор параметра display_types в конфигурации
     * 
     * @return string часть SQL запроса
     */
    protected function getDisplayTypes () {
    	
    	if (!isset($this->config['display_types'])) return false;
    	
        $this->config['display_types'] = explode(',',$this->config['display_types']);
    	$query_parts = array();
    	foreach ($this->config['display_types'] as $type) {
    		if (strpos($type,'-')===0) {    			
    			$query_parts[] =  '(`type`<>'.$this->objectModel->escape(substr($type,1)).' AND template<>'.$this->objectModel->escape(substr($type,1)).')';
    		} else {
    			$query_parts[] = '(`type`='.$this->objectModel->escape($type).' OR template='.$this->objectModel->escape($type).')';
    		}
    	}
    	$query = '('.implode(' AND ',$query_parts).')';
    	return $query;
    	
    }
	
	/**
	 * Возвращает результат синтаксического разбора секции файла конфигурации интерфейса
	 *
	 * @param Admin_Template_Config $objectItemConfig
	 *
	 */
	private function parseConfig ($objectItemConfig) {
	    
	    $config = array();
	    try {
	        $config_section = $objectItemConfig->getConfigSection(self::SECTION_CHILDS);
	    } catch (Admin_TemplateConfigException $e) {
	        Admin_Errors::add('',$e);
	        // используем секцию из глобальной конфигурации
	        $objectGlobalConfig = Admin_Core::getObjectGlobalConfig();
	        $config_section = $objectGlobalConfig->getConfigSection(self::SECTION_CHILDS);
	    }
		foreach ($config_section as $key=>$value) {
	    	if (strpos($key,'field->')===0) {
	    		$name = str_replace('field->','',$key);
	    		$config['fields'][$name] = $value;
	    	} else {
	    	    if (strpos($value,';')!==false) {
	    	        $config[$key] = array_map('trim',explode(';',$value));
	    	        if ($key=='link') {
	    	            // анализ связей
	    	            $link = $config[$key];
	    	            unset($config[$key]);
	    	            foreach ($link as $param_value) {
    	    	            // другой разделитель, поскольку ":" используется для других целей
		    			    list($param_name,$param_value) = explode('=',trim($param_value));
    		    			if ($param_name=='fields') {
    		    			    $param_value_array = array();
    		    			    foreach (Admin_Template_Config::explode(' ',$param_value) as $link) {
    		    			        $param_value_array[] = explode('-',$link);
    		    			    }
    		    			    $param_value = $param_value_array;
    		    			}
    		    			$config[$key][$param_name] = $param_value;
	    	            }
	    	        }
	    	    } else {
	    	       $config[$key] = $value; 
	    	    }
	    	}
	    }
	    // анализ конфигурации
	    $field_config = array();
	    if (!isset($config['fields'])) {
	    	$config['fields'] = array();
	    }
	    foreach ($config['fields'] as $name=>$value) {
	    	// разбиваем в массив сложные параметры
	    	if (strpos($value,':')!==false) {
	    		$field_config[$name] = array();
		    	foreach (Admin_Template_Config::explode(';',$value) as $param_value) {
                    list($param_name,$param_value) = explode(':',trim($param_value));			    			
                    if (empty($param_value)) $param_value = true;
		    		$field_config[$name][$param_name] = $param_value;
		    	}
	    	}
	    }
	    $config['fields'] = $field_config;
	    return $config;
	    
	}
	
	/**
	 * Устанавливает массив конфигурации
	 *
	 */
	protected function setConfig () {
		
		$config_item = $this->parseConfig(Admin_Core::getObjectItemConfig());
		$config_global = $this->parseConfig(Admin_Core::getObjectGlobalConfig());
		
	    foreach ($config_global as $section=>$items) {
			if (isset($config_item[$section])) {
				if (is_array($items)) {
					$this->config[$section] = array_merge($items,$config_item[$section]);
				} else {
					$this->config[$section] = $config_item[$section];
				}
			} else {
				$this->config[$section] = $items;
			}
		}
		// удаляем из конфигурации поля, помеченные "-"
		foreach ($this->config['fields'] as $section=>$items) {
		    foreach ($items as $name=>$params) {
		    	if (isset($params['show'])) {
		    	    if ($params['show']=='-') unset($this->config[$section][$name]);
		    	}
		    }		    
		}
		
	}
	
}

?>
