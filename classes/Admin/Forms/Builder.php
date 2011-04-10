<?php

class Admin_Forms_Builder extends HTML_QuickForm {
    
    /**
     * Имя переменной, передаваемой в шаблон Smarty
     *
     */
    const SMARTY_FORM_VARIABLE = 'form';
    
    /**
     * Путь и имя динамического шаблона Smarty
     *
     */
    const SMARTY_FORM_TEMPLATE = 'forms/smarty-dynamic.tpl';
    
    /**
     * Название секции в файле конфигурации
     *
     */
    const SECTION_FORMS = 'FORMS';
    
    /**
     * Данные конфигурации формы
     *
     * @var array
     */
    private $config;
    
    /**
     * Объект модели данных, если необходимо
     *
     * @var Admin_Model_Common
     */
    private $objectModel;
    
    /**
     * Объект источника данных для формы
     *
     * @var Admin_Model_Source_Common
     */
    private $objectModelSource;
    
    /**
     * Коллекция объектов
     *
     * @var Admin_Controller_Collection
     */
    private $objectCollection;
    
    /**
     * Массив плейсхолдеров для параметров формы
     *
     * @var array
     */
    private $placeholders;
    
    /**
     * Имя текущего действия
     *
     * @var string
     */
    private $action_name;
    
    /**
     * Группа текущего действия
     *
     * @var string
     */
    private $action_group;
    
    /**
     * Признак того, что форма успешно построена
     *
     * @var bool
     */
    private $is_form_built = false;
    
    /**
     * Конструктор класса
     *
     * @param Admin_Model_Common $objectModel
     */
	public function __construct ($objectModel=false,$placeholders=array()) {
		
		$this->placeholders = $placeholders;
		$this->objectModel = $objectModel;
		$this->objectCollection = Admin_Controller_Collection::getInstance();
		if (is_subclass_of($objectModel,'Admin_Model_Abstract')) {
		    $this->objectModelSource = $this->objectModel->getObjectModelSource();
		}
		$this->action_name = Admin_Core::getActionName();
		$this->action_group = $this->objectCollection->actions_panel->getActionGroup($this->action_name);
		$this->setFormConfig();
	
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
			foreach ($objectItemConfig->getConfigSection(self::SECTION_FORMS) as $key=>$value) {
		    	if (strpos($key,'form->')===0) {
		    		$name = str_replace('form->','',$key);
		    		$config['form'][$name] = $value;
		    	} elseif (strpos($key,'field->')===0) {
		    		$name = str_replace('field->','',$key);
		    		$config['fields'][$name] = $value;
		    	}
		    }
		    // анализ конфигурации формы
		    $form_config = array();
		    if (!isset($config['form'])) {
		    	$config['form'] = array();
		    }
		    foreach ($config['form'] as $name=>$value) {
		    	$name = $this->isActionParam($name);
		    	if ($name===false) continue;
		    	$form_config[$name] = Admin_Template_Config::ph_replace($value,$this->placeholders);
		    	// разбиваем в массив сложные параметры
		    	if (strpos($value,':')!==false) {
		    		$form_config[$name] = array();
			    	foreach (Admin_Template_Config::explode(';',$value) as $param_value) {
			    		if ($name=='autoset_values' || $name=='link') {
			    			// другой разделитель, поскольку ":" используется для других целей
			    			list($param_name,$param_value) = explode('=',trim($param_value));
			    			// анализ связей
			    			if ($param_name=='fields') {
			    			    $param_value_array = array();
			    			    foreach (Admin_Template_Config::explode(' ',$param_value) as $link) {
			    			        $param_value_array[] = explode('-',$link);
			    			    }
			    			    $param_value = $param_value_array;
			    			}
			    		} else {
			    			list($param_name,$param_value) = explode(':',trim($param_value));			    			
			    		}
			    		$form_config[$name][$param_name] = $param_value;
			    	}
		    	}
		    	// общие валидаторы и постобработчики формы в массив
		    	if ($name=='validator' || $name=='posthandler') {
		    	    $form_config[$name] = Admin_Template_Config::explode(',',$value);
		    	}
		    }
		    
		    // анализ конфигурации полей
		    if (!isset($config['fields'])) {
		    	$config['fields'] = array();
		    }
		    $fields_config = array();
		    foreach ($config['fields'] as $name=>$params) {
		    	$name = $this->isActionParam($name);
		    	if ($name===false) continue;
		    	// если отсутствует имя таблицы в названии поля, добавляем таблицу, установленную по умолчанию
		    	if (strpos($name,'::')===false) {
		    		$name = $this->objectModel->getDefaultTable().'::'.$name;
		    	}
		    	foreach (explode(';',$params) as $key=>$value) {
		    		list($param_name,$param_value) = explode(':',trim($value),2);
		    		$param_name = $this->isActionParam($param_name);
		    		if ($param_name===false) continue;
			    	// дополнительный синтаксический анализ параметра validator
		    		if ($param_name=='validator') {
		    			$validators = array();
		    			foreach (Admin_Template_Config::explode(' ',$param_value) as $v_name) {
		    				$v_name = $this->isActionParam($v_name);
		    				if ($v_name!==false) {
		    					$validators[] = $this->isActionParam($v_name);
		    				}
		    			}
		    			$param_value = implode(',',$validators);
		    		}
		    		$fields_config[$name][$param_name] = str_replace(',',' ',$param_value);		    		
		    	}
		    }
		    
		    $config['fields'] = $fields_config;
		    $config['form'] = $form_config;
	    } catch (Admin_TemplateConfigException $e) {
	    	Admin_Errors::add('',$e);
	    	return array();
	    }
	    return $config;
	    
	}
	
	/**
	 * Синтаксический анализ имени метода и его параметров
	 *
	 * @param string $method
	 * @return array
	 */
	private function parseMethodName ($method) {
		
		$source = array();
		if (!preg_match('#^(\w+)\(?(.*?)\)?$#is',$method,$source)) {
			throw new Admin_FormBuilderException('Ошибка синтаксического анализа имени источника данных: '.$method);
		}		
		list($method,$method_name,$method_params) = $source;
		if (!method_exists($this->objectModelSource,$method_name)) {
			throw new Admin_FormBuilderException("Незарегистрированный источник данных {$method} в классе ".get_class($this->objectModelSource));
		}
		// разбор параметров метода
		if (!empty($method_params)) {
			$method_params = preg_split('#\'\s+#is',$method_params);
			foreach ($method_params as $key=>$value) {
				$param_value = explode('=',$value);
				$method_params[$param_value[0]] = str_replace("'",'',$param_value[1]);
				unset($method_params[$key]);
			}
		} else {
			$method_params = array();

		}
		return array($method_name,$method_params);
		
	}
	
	/**
	 * Проверяет принадлежность текущего действия к списку действий параметра формы
	 *
	 * @param string $name имя параметра
	 * @return mixed
	 */
	private function isActionParam($name) {
		
		$actions_list = preg_replace('#/(.+)/.+#','$1',$name);
	    if ($actions_list==$name) return $name;
    	$name = str_replace('/'.$actions_list.'/','',$name);
    	$actions_list = Admin_Template_Config::explode(',',$actions_list);
    	$state_action = Admin_Template_Config::in_array($this->action_name,$actions_list);
    	if ($state_action===true && $actions_list[0]!=$name) {
    		return $name;
    	}		
    	// проверям группы, к которым относится действие            
        $state_group = 0;
    	foreach ($this->action_group as $group_name) {
            $state_group = Admin_Template_Config::in_array($group_name,$actions_list);
    		if ($state_group===true && $actions_list[0]!=$name) {
    			return $name;
    		}
    	}
        if ($state_action===0 && $state_group===0) return $name;
    	if ($state_action===false && $actions_list[0]!=$name) {
            return false;
        }	        
    	return false;
		
	}
	
	/**
	 * Устанавливает массив конфигурации формы
	 *
	 */
	private function setFormConfig () {
		
		// из глобального файла конфигурации
		$objectGlobalConfig = Admin_Core::getObjectGlobalConfig();
		$config_global = $this->parseConfig($objectGlobalConfig);
		// из файла конфигурации интерфейса элемента
		try {
			$override_config = $this->objectCollection
				->actions_panel
				->getOverrideConfigName($this->action_name);
			// переопределенный конфиг
			$objectItemConfig = new Admin_Template_Config(SYSTEM_PATH.Admin_Core::PATH_TO_CONFIGS.Admin_Core::PATH_TO_INTERFACE_CONFIGS.$override_config);
		} catch (Admin_FormBuilderException $e) {
			if ($this->action_group=='add_child') {
				Admin_Errors::add('',$e);
			}
			$objectItemConfig = Admin_Core::getObjectItemConfig();
		}
		if ($objectItemConfig!=$objectGlobalConfig) {
			$config_item = $this->parseConfig($objectItemConfig);
		}
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
		    	    if ($params['show']=='-') {
		    	    	unset($this->config[$section][$name]);
		    	    }
		    	}
		    }		    
		}
		
		/*echo '<pre>';
		print_r($this->config);
		echo '</pre>';*/
		
	}
	
	/**
	 * Возвращает данные конфигурации формы
	 *
	 * @param string $type возвращаемый тип конфигурационных данных (форма, поля)
	 * @return array
	 */
	private function getFormConfig ($type=false) {
		
	    if (!isset($this->config[$type])) return $this->config;
	    return $this->config[$type];
		
	}
	
	/**
	 * Возвращает значение параметра из конфигурации формы
	 *
	 * @param string $section
	 * @param string $name
	 * @return mixed
	 */
	public function getParam($section,$name) {
		
		if (isset($this->config[$section][$name])) {
			return $this->config[$section][$name];
		}
		return false;
		
	}
	
	public function getFieldParam($table,$field,$param) {
		
		if (isset($this->config['fields']["$table::$field"][$param])) {
			return $this->config['fields']["$table::$field"][$param];
		}
		
		return false;
		
	}
	
	/**
	 * Добавляет параметры рендеринга
	 *
	 * @param array $render_params
	 */
	private function addRenderElementsParams ($render_params) {
	    
	    if (!count($render_params)) {
	        $this->render_elements_params = $render_params;
	    } else {
	        $this->render_elements_params = array_merge($this->render_elements_params,$render_params);
	    }
	    
	}
	
	/**
	 * Рендеринг динамического шаблона формы и возврат ее HTML кода 
	 *
	 * @return string
	 */
	private function processFormTemplate () {
	    
	    $objectSmarty = Admin_Core::getObjectSmarty();
	    $renderer = new HTML_QuickForm_Renderer_Array(true,true);
	    $this->accept($renderer);
	    $array_rendered = $renderer->toArray();
	    
	    $objectSmarty->assign(self::SMARTY_FORM_VARIABLE,$array_rendered);
	    // также передаем ошибки валидации
	    
	    return $objectSmarty->fetch(self::SMARTY_FORM_TEMPLATE);
	    
	}
	
	/**
	 * Создает элемент запрошенного типа
	 *
	 * @param string $field_type тип элемента
	 * @param string $class_name имя класса
	 * @param string $field_name имя элемента
	 * @param string $field_label название элемента
	 * @param mixed $attributes список атрибутов
	 * @param mixed $field_data данные для элемента
	 * @param string $field_text текст для типа static
	 * @return HTML_QuickForm_Element 
	 */
	private function createFormElement ($field_type,$class_name,$field_name,$field_label,$attributes=false,$field_data=false,$field_text='') {
		
		switch ($field_type) {
			case 'multiselect':
			case 'select':
			case 'cityselect':
				$attributes .= ' id="'.$field_name.'" ';
				$element = new $class_name($field_name,$field_label,null,$attributes);
				if ($field_data!==false) $element->loadArray($field_data);
				break;
			case 'radio':
				$radio_attributes = $attributes;
				foreach ($field_data as $key=>$value) {
					$radio_attributes = $attributes.' id="'.$field_name.'_'.$key.'" ';
					$radio_elements[] = new $class_name($field_name,'',$value,$key,$radio_attributes);
				}
				$element = new HTML_QuickForm_group($field_name,$field_label,$radio_elements,'<br />');
				break;
			case 'checkbox':
				$attributes .= ' id="'.$field_name.'" ';
				$element = new $class_name($field_name,$field_label,'',$attributes);
                if ($field_data!==false) $element->setValue($field_data);
				break;
			case 'static':
				if ($field_data!==false) $field_text = $field_data;
				$element = new $class_name($field_name,$field_label,$field_text);
				break;
			default:
				$attributes .= ' id="'.$field_name.'" ';
				$element = new $class_name($field_name,$field_label,$attributes);
				if ($field_data) $element->setValue($field_data);
			break;
		}
		
		return $element;
		
	}
	
	/**
	 * Устанавливает значение по умолчанию для элемента
	 *
	 * @param HTML_QuickForm_Element $element
	 * @param mixed $value
	 */
	private function setDefaultValues (&$element,$value) {
        
        // группа зависимых переключателей (radio)
	    if ($element->getType()=='group') {           
            // снимаем аттрибуты со всех
            foreach ($element->getElements() as $radio) {
                if ($radio->getType()=='radio') {
                    $radio->setChecked(false);
                }
            }
            // отмечаем необходимый
	        foreach ($element->getElements() as $radio) {
	            if ($radio->getType()=='radio' && $value==$radio->getValue()) {
	                $radio->setChecked(true);
	                return ;
	            }
	        }
    	}
    	$element->setValue($value);
		
	}
	
	/**
	 * Выполняет построение формы в соответствии с полученной конфигурацией
	 * 
	 * @return string
	 */
	public function build () {
		
		// заголовок формы
		$form_header = isset($this->config['form']['header']) ? $this->config['form']['header'] : 'Форма';
		// атрибут action формы
		$form_action = isset($this->config['form']['action']) ? $this->config['form']['action'] : $_SERVER['REQUEST_URI'];
		// метод отправки данных
		$form_method = isset($this->config['form']['method']) ? $this->config['form']['method'] : 'post';
		// имя формы
		$form_name = isset($this->config['form']['name']) ? $this->config['form']['name'] : $this->action_name.'_form';
		// дополнительные атрибуты
		$form_attributes = 'enctype="multipart/form-data"';
		// имя кнопки отправки
		$submit_name = isset($this->config['form']['submit_name']) ? $this->config['form']['submit_name'] : 'Выполнить действие';
		
		parent::__construct ($form_name,$form_method,$form_action,'',$form_attributes);
		$this->addElement('header', null, $form_header);
		
		// формируем hidden поля для поддержки общих валидаторов
		if (isset($this->config['form']['validator'])) {
		    foreach ($this->config['form']['validator'] as $name) {
		        $hidden_field = $this->createFormElement('hidden','HTML_QuickForm_hidden',$name,NULL,'meta:validator="manual_common:manual"');
		        $hidden_field->setValue($name);
		        $this->addElement($hidden_field);
		    }
		}
		
		// создаем поля
		foreach ($this->config['fields'] as $field=>$params) {
			$element_class_name = 'Admin_Forms_Elements_'.$params['type'];
			// проверка на существование типа элемента
			if (!@class_exists($element_class_name)) {
				// пытаемся загрузить из другого места
				$element_class_name = 'HTML_QuickForm_'.$params['type'];
				if (!class_exists($element_class_name)) {
					throw new Admin_FormBuilderException("Незарегистрированный тип элемента: {$params['type']}");
				}
				// регистрируем новый тип элемента
			} else {
				self::registerElementType($params['type'],'Admin/Forms/Elements',$element_class_name);
			}
			// установка источника данных для поля
			$field_data = false;
			if (isset($params['source']) && $params['source']!='-') {								
				// выделяем имя метода и параметры
				list($method_name,$method_params) = $this->parseMethodName('source_'.$params['source']);
				// выполнение метода
				$field_data = call_user_func_array(array($this->objectModelSource,$method_name),$method_params);
			}
			
			// передаем валидаторы в атрибут элемента
			$validators = isset($params['validator']) ? 'meta:validator="'.$params['validator'].'" meta:dynamic' : '';
			if (!isset($params['attributes'])) $params['attributes'] = '';
			if (!isset($params['text'])) $params['text']='';
			$attributes = $params['attributes'].' '.$validators;
			 
			// создаем объект, соответствующий типу элемента
			$element = $this->createFormElement(
				$params['type'],$element_class_name,
				$field,$params['label'],$attributes,$field_data,$params['text']);
				
			// добавляем элемент
			$this->addElement($element);
			
			// дополнительные элементы интерфейса для текущего поля
			if (isset($params['add_controls'])) {
				$add_control_class_name = 'Admin_Forms_Elements_AddControls_'.$params['add_controls'];
				if (!class_exists($add_control_class_name)) {
					throw new Admin_FormBuilderException("Незарегистрированный дополнительный элемент: {$params['add_controls']}");
				}
				// создаем объект
				$add_control_element = new $add_control_class_name($this->objectModel,$field);
				// регистрируем дополнительный элемент
				self::registerElementType($params['add_controls'],'Admin/Forms/Elements/AddControls',$add_control_class_name);
				// добавляем на форму
				$this->addElement($add_control_element);
			}
			
            // принудительно устанавливаем переключатель для элемента radio
            if ($params['type']=='radio') {
                $this->setDefaultValues($element,$this->objectModel->getDbValueByField($element->getName()));
            }
            
			// устанавливаем значения по умолчанию
			if (isset($params['default'])) {
				// пытаемся запустить метод
			    try {
			        $params['default'] = str_replace("'",'',$params['default']);
					list($method_name,$method_params) = $this->parseMethodName('autoset_'.$params['default']);
					$value = call_user_func_array(array($this->objectModelSource,$method_name),$method_params);
					$params['default'] = $value;
				} catch (Admin_FormBuilderException $e) {
					$params['default'] = $params['default'];
				}
				$this->setDefaultValues($element,$params['default']);
			}
			
			// элемент обязательный для заполнения
			if (isset($params['validator'])) {
				if (strpos($params['validator'],'filled')!==false) {
					$this->addRule($field,'','required');
				}
			}
		}
		
		// добавляем кнопку отправки формы
		$this->addElement('submit',$this->action_name,$submit_name,'class="submit"');
		// добавляем кнопку отмены
		if (isset($this->config['form']['cancel_button'])) {
			$this->addElement(
				'submit',
				$this->config['form']['cancel_button']['name_prefix'].'_'.$this->action_name,
				$this->config['form']['cancel_button']['label']
			);
		}
		
		$this->is_form_built = true;
		
		// возвращаем заполненный шаблон
		return $this->processFormTemplate();
		
	}
	
	/**
	 * Возвращает список полей, для которых определен источник данных
	 *
	 * @return array
	 */
	public function getFieldsWithSource () {
		
		// если в конфигурации формы указано не заполнять форму из POST, то возвращаем список всех полей
		$form_fill = isset($this->config['form']['fill'])?$this->config['form']['fill']:true;
		if ($form_fill==='no') {
		    return array_keys($this->config['fields']);
		}
		$fields = array();
		foreach ($this->config['fields'] as $key=>$value) {
			$field_fill = isset($value['fill'])?$value['fill']:true;
			if ($field_fill!=='no') {
				if ((isset($value['source']) || isset($value['default'])) && $value['type']!='radio' && $value['type']!='select' && $value['type']!='multiselect' && $value['type']!='cityselect') {
					$fields[] = $key;
				}
			} else {
				$fields[] = $key;
			}
		}
		return $fields;
		
	}
	
	/**
	 * Возвращает массив всех полей формы
	 *
	 * @return array
	 */
	public function getFieldsNames () {
		
		return array_keys($this->config['fields']);
		
	}
	
	/**
	 * Возвращает значения, автоматически попадающие в массив для базы данных
	 *
	 * @return array
	 */
	public function getAutosetValues () {
		
		if (!$this->is_form_built) {
			throw new Admin_FormBuilderException('Необходимо выполнить построение формы перед вызовом этого метода');
		}
		if (isset($this->config['form']['autoset_values'])) {
			foreach ($this->config['form']['autoset_values'] as $key=>$value) {
				try {
					list($method_name,$method_params) = $this->parseMethodName('autoset_'.$value);
					$this->config['form']['autoset_values'][$key] = call_user_func_array(array($this->objectModelSource,$method_name),$method_params);
				} catch (Admin_FormBuilderException $e) {
					// пробуем извлечь данные из полей (если вид table::field)
					if (strpos($value,'::')!==false && isset($_POST[$value])) {
						$this->config['form']['autoset_values'][$key] = $_POST[$value];
					} else {
					    $this->config['form']['autoset_values'][$key] = $value;
                    }
				}
			}
		    return $this->config['form']['autoset_values'];
		}
		return array();
		
	}
	
	/**
	 * Возвращает общие валидаторы формы
	 *
	 * @return array|bool
	 */
	public function getCommonValidators () {
	    
	    if (isset($this->config['form']['validator'])) {
	        return $this->config['form']['validator'];
	    }
	    return false;
	    
	}
	
	/**
	 * Возвращает постобработчики формы
	 *
	 * @return array
	 */
	public function getPosthandlers () {
	    
	    if (!$this->is_form_built) {
			throw new Admin_FormBuilderException('Необходимо выполнить построение формы перед вызовом этого метода');
		}
	    if (isset($this->config['form']['posthandler'])) {
	        return $this->config['form']['posthandler'];
	    }
	    return array();
	    
	}
	
	/**
	 * Возвращает связи для таблиц
	 *
	 * @return array|bool
	 */
	public function getTableLink () {
	    
	    if (!$this->is_form_built) {
			throw new Admin_FormBuilderException('Необходимо выполнить построение формы перед вызовом этого метода');
		}
	    if (!isset($this->config['form']['link'])) {
	        // формируем связь по умолчанию с предположением, что ключевые поля имеют название id
	        // и линкуются к таблице, установленной по умолчанию
	        $this->config['form']['link']['type'] = 'O2O';
	        $i=0;
            $tables = $this->getTables();
	        foreach ($tables as $table) {
	            if ($table!=$this->objectModel->getDefaultTable()) {
                    if (count($tables)>1) {
	        	        $this->config['form']['link']['fields'][$i][0] = $this->objectModel->getDefaultTable().'::id';
	        	        $this->config['form']['link']['fields'][$i][1] = $table.'::id';
	        	        $i++;
                    } else {
                        // одна таблица (не по умолчанию) на форме
                        $this->config['form']['link']['fields'][$i][0] = $table.'::id';
                    }
	            }	            
	        }
	        // одна таблица на форме (по умолчанию)
	        if (!isset($this->config['form']['link']['fields'])) {
	            $this->config['form']['link']['fields'][0][0] = $this->objectModel->getDefaultTable().'::id';
	        }
	    }
	    return $this->config['form']['link'];
	    
	}
	
	/**
	 * Возвращает список таблиц, задействованных на форме
	 *
	 * @return array
	 */
	private function getTables () {
	    
	    if (!$this->is_form_built) {
			throw new Admin_FormBuilderException('Необходимо выполнить построение формы перед вызовом этого метода');
		}
		// список всех полей и autoset значений
		$fields = array_flip(array_keys($this->config['fields']));
		$autoset_values = array();
		if (isset($this->config['form']['autoset_values'])) {
		    $autoset_values = $this->config['form']['autoset_values'];
		}		 
		// разворачиваем в таблицы
		$tables = _Array::expand(array_merge($autoset_values,$fields));
		return array_keys($tables);
	    
	}
	
}

?>