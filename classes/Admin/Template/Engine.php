<?php

/**
 * Класс управления шаблонами
 *
 */
class Admin_Template_Engine {
    
    /**
     * Объект Admin_Smarty_Blocks
     *
     * @var Admin_Smarty_Blocks
     */
    private $objectSmartyBlocks;
    
    /**
     * Параметры запрошенного файла конфигурации
     *
     * @var array
     */
    private $configParams;
    
    /**
     * Глобальные переменные страницы
     *
     * @var array ассоциативный массив глобальных переменных
     */
    private $globalTemplateParams = array();
	
	/**
	 * Конструктор класса
	 *
	 * @param Admin_Smarty_Blocks $objectSmartyBlocks
	 */
    public function __construct($objectSmartyBlocks,$configParams) {
	    
	    $this->objectSmartyBlocks = $objectSmartyBlocks;
	    $this->configParams = $configParams;
	
	}
	
	/**
	 * Устанавливает массив переменных для передечи в шаблон как глобальных
	 *
	 * @param array $globalTemplateParams
	 */
	public function setGlobalTemplateParams ($globalTemplateParams) {
		
		$this->globalTemplateParams = $globalTemplateParams;
		
	}
	
	/**
	 * Компиляция блоков и отображение страницы
	 *
	 */
	public function displayPage () {
	    
	    $objectSmarty = Admin_Core::getObjectSmarty();
	    $configParams = $this->configParams;	    
		
	    // проход по всем блокам
	    foreach ($configParams[Admin_Core::SECTION_BLOCKS] as $block_name=>$block_path) {
	    	$this->objectSmartyBlocks->clear_all_assign();
			if (!empty($configParams[Admin_Core::SECTION_CONTROLLERS][$block_name])) {
				// создание необходимого объекта класса в зависимости от типа запрошенной страницы
				$config_name = Admin_Core::getItemConfigName();
				$objectRequestedType = Admin_Controller_Factory::createObject(
					$configParams[Admin_Core::SECTION_CONTROLLERS][$block_name],
					$config_name,
					Admin_Core::getActionClassName()
				);
				// передача переменных контроллера в блок
				$this->objectSmartyBlocks->assignArray($objectRequestedType->getTemplateValue());
			}
			// передача глобальных переменных в блок
			$this->objectSmartyBlocks->assignArray($this->globalTemplateParams);
			// вставка скомпилированного блока в макет
			$this->objectSmartyBlocks->setBlockPostfix(array(Admin_Core::getItemType(),Admin_Core::getActionName()));
			$block_content = $this->objectSmartyBlocks->fetch($block_path);
			$objectSmarty->assign($block_name,$block_content);
		}
		// передача глобальных переменных в макет
		$objectSmarty->assignArray($this->globalTemplateParams);
		// отображение страницы 
	    $objectSmarty->display($configParams[Admin_Core::SECTION_LAYOUT]['file']);
		
	}
	
	/**
	 * Выполняет обработку отображение служебных страниц
	 *
	 */
	public function processAuxiliary () {
		
		$objectSmarty = Admin_Core::getObjectSmarty();
	    $configParams = $this->configParams;
	    
	    foreach ($configParams[Admin_Core::SECTION_CONTROLLERS] as $curr_path=>$controller) {
	    	if (Admin_Core::getUriLastPart()==$curr_path) {
	    		$objectRequestedType = new $controller;
	    		$objectSmarty->assignArray($objectRequestedType->getTemplateValue());
	    		$objectSmarty->display(Admin_Core::getTemplateName().'/'.$curr_path.'.html');
	    		return ;
	    	}
	    }
		
	}
	
	/**
	 * Выполнение запроса AJAX
	 *
	 */
	public function processAjax () {
		
		include_once SYSTEM_PATH.'/plugins/admin/ajax/'.Admin_Core::getActionName().'.php';
		/*$objectSmarty = Admin_Core::getObjectSmarty();
		$objectSmarty->assign('ajax_script',SYSTEM_PATH.'/plugins/admin/ajax/'.Admin_Core::getActionName().'.php');
		$objectSmarty->display($this->configParams[Admin_Core::SECTION_LAYOUT]['file']);*/
		
	}
	
}

?>
