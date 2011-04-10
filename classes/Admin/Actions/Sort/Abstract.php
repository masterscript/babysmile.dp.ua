<?php

abstract class Admin_Actions_Sort_Abstract implements Admin_IController {
    
    const SECTION_SORT = 'SORT';
	
	/**
	 * Объект модели
	 *
	 * @var Admin_Model_Common
	 */
	protected $objectModel;
	
	/**
	 * Массив результирующей конфигурации
	 *
	 * @var array
	 */
	private $config;
		
	public function __construct () {
		
		$this->objectModel = Admin_Controller_Factory::createObject('Admin_Model',Admin_Core::getItemConfigName(),Admin_Core::getActionClassName());
		$this->setConfig();
	
	}
	
	/**
     * Синтаксический разбор параметра display_types в конфигурации
     * 
     * @return string часть SQL запроса
     */
    protected function getDisplayTypes () {
    	
    	if (!isset($this->config['display_types'])) return false;
    	    	
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
	        $config_section = $objectItemConfig->getConfigSection(self::SECTION_SORT);
	    } catch (Admin_TemplateConfigException $e) {
	        Admin_Errors::add('',$e);
	        return $config;
	    }
		foreach ($config_section as $key=>$value) {
	    	$config[$key] = explode(',',$value);
	    }
	    return $config;
	    
	}
	
	/**
	 * Устанавливает массив конфигурации
	 *
	 */
	private function setConfig () {
		
	    // из глобального файла конфигурации
		$config_global = $this->parseConfig(Admin_Core::getObjectGlobalConfig());
		// из файла конфигурации интерфейса элемента
		$config_item = $this->parseConfig(Admin_Core::getObjectItemConfig());

		$this->config = array_merge($config_global,$config_item);
		
		/*echo '<pre>';
		print_r($this->config);
		echo '</pre>';*/
		
	}
	
}

?>
