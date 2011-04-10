<?php

/**
 * Работа с блоками
 *
 */
class Admin_Smarty_Blocks extends Admin_Smarty_Abstract {
	
	/**
	 * Постфиксы имени блока
	 *
	 * @var array
	 */
	private $block_postfix = array();
	
	/**
	 * Конструктор класса
	 *
	 */
	public function __construct () {
		
		$this->template_dir = SYSTEM_PATH.'/templates/admin';
	    $this->compile_dir = SYSTEM_PATH.'/templates_c/admin';
	
	}
	
	/**
	 * Устанавливает постфиксы блока
	 *
	 * @param array $block_postfix
	 */
	public function setBlockPostfix ($block_postfix) {
		
		$this->block_postfix = $block_postfix;
		
	}
	
	/**
	 * Передача массива переменных в шаблон
	 *
	 * @param array $array_vars массив переменных
	 */
	public function assignArray ($array_vars) {
		
		if (count($array_vars)<1) return ;
		foreach ($array_vars as $name=>$value) {
			parent::assign($name,$value); 
		}		
		
	}

	
	/**
	 * @see Smarty::fetch()
	 *
	 * @param string $resource_name
	 * @param string $cache_id
	 * @param string $compile_id
	 * @param boolean $display
	 * @return string
	 */
	public function fetch ($resource_name,$cache_id=null,$compile_id=null,$display=false) {
	
		if ($this->block_postfix) $resource_name = $this->redefineBlock($resource_name);
		return parent::fetch($resource_name,$cache_id,$compile_id,$display);
	
	}	
	
	private function redefineBlock ($block_name) {
		
		$path_parts = pathinfo($block_name);
		$path_parts['filename'] = str_replace('.'.$path_parts['extension'],'',$path_parts['basename']);
		$prev_block_name = $path_parts['filename'];
		// определяем максимально подходящее имя блока по факту существования соответствующего файла
		$i = 0;
		while (file_exists($this->template_dir.'/'.$path_parts['dirname'].'/'.$prev_block_name.'.'.$path_parts['extension'])) {
			$prev_block_name = $prev_block_name.'_'.$this->block_postfix[$i];
			$i++;
		}
        $postfix = implode('_',array_slice($this->block_postfix,0,$i-1));
        if (!empty($postfix)) {
            $postfix = '_'.$postfix;
        } else {
        	// попытка с перевернутым массивом
        	$this->block_postfix = array_reverse($this->block_postfix);
        	$prev_block_name = $path_parts['filename'];
			// определяем максимально подходящее имя блока по факту существования соответствующего файла
			$i = 0;
			while (file_exists($this->template_dir.'/'.$path_parts['dirname'].'/'.$prev_block_name.'.'.$path_parts['extension'])) {
				$prev_block_name = $prev_block_name.'_'.$this->block_postfix[$i];
				$i++;
			}
	        $postfix = implode('_',array_slice($this->block_postfix,0,$i-1));
	        if (!empty($postfix)) {
            	$postfix = '_'.$postfix;
	        }
        }
		return $path_parts['dirname'].'/'.$path_parts['filename'].$postfix.'.'.$path_parts['extension'];
		
	}
	
}

?>
