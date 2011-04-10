<?php

/**
 * Абстрактный класс зоны описания элемента
 *
 */
abstract class Admin_Info_Abstract implements Admin_IController {
	
	const SECTION_INFO = 'INFO';
	
	private $config;
	
	protected $inplace_editor_jscode = '';
	
	protected $placeholders = array();
	
	protected $element_info = array();
	
	/**
	 * Объект модели
	 *
	 * @var Admin_Model_Common
	 */
	protected $objectModel;
	
	/**
	 * Объект источника данных
	 *
	 * @var Admin_Info_Source_Common
	 */
	protected $objectSource;
	
	/**
	 * Конструктор класса
	 *
	 */
	public function __construct () {
		
		$this->objectModel = Admin_Controller_Factory::createObject('Admin_Model',Admin_Core::getItemConfigName(),Admin_Core::getActionClassName());
		$this->objectSource = Admin_Controller_Factory::createObject('Admin_Info_Source',Admin_Core::getItemConfigName(),Admin_Core::getActionClassName());
		
	}
	
	/**
	 * Возвращает результат синтаксического разбора секции файла конфигурации интерфейса
	 *
	 * @param Admin_Template_Config $objectItemConfig
	 *
	 */
	protected function parseConfig ($objectItemConfig) {
	    
	    $config = array();
	    try {
	        $config_section = $objectItemConfig->getConfigSection(self::SECTION_INFO);
	    } catch (Admin_TemplateConfigException $e) {
	        Admin_Errors::add('',$e);
	        // используем секцию из глобальной конфигурации
	        $objectGlobalConfig = Admin_Core::getObjectGlobalConfig();
	        $config_section = $objectGlobalConfig->getConfigSection(self::SECTION_INFO);
	    }
		foreach ($config_section as $key=>$value) {
	    	if (strpos($key,'item->')===0) {
	    		$name = str_replace('item->','',$key);
	    		$config['items'][$name] = $value;
	    	}
	    }
	    // анализ конфигурации
	    $item_config = array();
	    if (!isset($config['items'])) {
	    	$config['items'] = array();
	    }
	    foreach ($config['items'] as $name=>$value) {
	    	$item_config[$name] = Admin_Template_Config::ph_replace($value,$this->placeholders);
	    	// разбиваем в массив сложные параметры
	    	if (strpos($value,':')!==false) {
	    		$item_config[$name] = array();
		    	foreach (Admin_Template_Config::explode(';',$value) as $param_value) {
		    		list($param_name,$param_value) = explode(':',trim($param_value));			    			
		    		$item_config[$name][$param_name] = $param_value;
		    	}
	    	}
	    }
	    $config['items'] = $item_config;
	    return $config;
	    
	}
	
	/**
	 * Устанавливает массив конфигурации
	 *
	 */
	private function setConfig () {
		
		$config_item = $this->parseConfig(Admin_Core::getObjectItemConfig());
		$config_global = $this->parseConfig(Admin_Core::getObjectGlobalConfig());
		
	    foreach ($config_global as $section=>$items) {
			if (isset($config_item[$section])) {
				$this->config[$section] = array_merge($items,$config_item[$section]);
			} else {
				$this->config[$section] = $items;
			}
		}
		// удаляем из конфигурации поля, помеченные "-"
		foreach ($this->config as $section=>$items) {
		    foreach ($items as $name=>$params) {
		    	if (isset($params['show'])) {
		    	    if ($params['show']=='-') unset($this->config[$section][$name]);
		    	}
		    }		    
		}
		
		/*echo '<pre>';
		print_r($this->config);
		echo '</pre>';*/
		
		// дополнительный код к элементу для поддержки опции inplace_editor
		$html = '';
		// проверяем, есть ли у какого-либо элемента опция inplace_editor
		$inplace_editor_init = false;
		foreach ($this->config['items'] as $item_name=>$params) {
			if (key_exists('inplace_editor',$params) && !$inplace_editor_init) {
			    if (!defined('JQUERY_LIBRARY_LOADED')) {
			        $html .= '<script type="text/javascript" src="'.SITE_SUBDIR.'/js/jquery.js"></script>';
			    }
			    $html .= '<script type="text/javascript" src="'.SITE_SUBDIR.'/js/jquery.jeditable.js"></script>';
			    $html .= '<script type="text/javascript"> $(document).ready(function() { [ELEMENTS_JSCODE]  });</script>';
			    $inplace_editor_init = true;
			    $element_jscode = '';
			}
			if (isset($params['inplace_editor'])) {
			    $js_item_name = 'editable_'.str_replace('::','--',$item_name);
			    $this->config['items'][$item_name]['js_name'] = $js_item_name;
			    $callback_js_func = '';
			    if (@$params['refresh_tree']=='yes') {
			        $callback_js_func = ', callback  : function (value,settings) {
                         	$("span.active-node").text(value);
						 }';
			    }
			    $element_jscode .= '
			    	$("#'.$js_item_name.'").editable("'.SITE_SUBDIR.'/ajax/inplace_edit?id='.$this->objectModel->getId().'", { 
                         type      : \''.$params['inplace_editor'].'\',
                         submit    : "OK",
                         tooltip   : "Щелкните для редактирования",
                         rows	   : "5",
                         height	   : "20px"'.$callback_js_func.'                         
                     });
			    ';
			}
		}
		
		if (!empty($html)) {
		    $this->inplace_editor_jscode = str_replace('[ELEMENTS_JSCODE]',$element_jscode,$html);
		}
		
	}
	
	/**
	 * Выполняет сбор информации об элементе
	 *
	 */
	protected function collect () {
		
        $this->setConfig();
        foreach ($this->config['items'] as $name=>$params) {
        	$this->element_info[$name] = $params;
        	if (isset($params['source'])) {
        		// пытаемся выполнить метод
        		$params['source'] = 'info_'.$params['source'];
        		if (!method_exists($this->objectSource,$params['source'])) {
        			throw new Admin_CoreException('Неизвестный источник данных: '.$params['source']);
        		}
        		try {
        		    list($table,$field) = $this->objectModel->parseField($name);
        		    $value = $this->objectModel->getItem($field,$table);
        		} catch (Admin_DbException $e) {
        		    $value = false;
        		}
        		$this->element_info[$name]['value'] = call_user_func_array(array($this->objectSource,$params['source']),array($name,$value));
        	} else {
        		// получаем данные из соответствующего поля в таблице БД
        		list($table,$field) = $this->objectModel->parseField($name);
        		$this->element_info[$name]['value'] = $this->objectModel->getItem($field,$table);
        		$this->element_info[$name]['js_name'] = isset($params['js_name'])?$params['js_name']:false;
        	}
        }
        
	}
	
}

?>
