<?php

/**
 * Абстрактный класс для работы с деревьями
 *
 */
abstract class Admin_Tree_Abstract extends Admin_Db_Abstract {
	
	/**
     * Поля для выбора из таблицы информации об элементе
     * id и pid являются обязательными
     * @todo проверка на обязательные поля
     * @var array
     */
    protected $select_fields;
    
    /**
     * SQL выражение и его псевдоним для формирования заголовка уровня в дереве
     *
     * @var array
     */
    private $level_caption;
    
    /**
     * Путь к скрипту, обрабатывающий AJAX запрос
     *
     * @var string
     */
    protected $ajax_script;
    
    /**
     * id запрошенного уровня
     *
     * @var integer;
     */
    protected $requested_node_id;
    
    /**
     * id элементов, которые не показывать при построении дерева
     *
     * @var array
     */
    private $exclude_ids = array();
    
    /**
     * Путь к элементу с указанием id родителей
     *
     * @var array
     */
    protected $path_to_node;
    
    /**
     * CSS классы дерева
     *
     * @var array
     */
    protected $css_classes = array(
    	'active'=>'active-node',
    	'sub-active'=>'text sub-active-node',
    	'virtual'=>'virtual-node'
    );
    
    /**
     * Объект для получения правил визуализации запрошенного уровня
     *
     * @var Admin_Tree_Rules
     */
    protected $tree_rules;
    
    /**
     * Идентификатор корня дерева.
     * @var integer
     */
    protected $root_id = 1;
    
    /**
     * Настройки уровня
     * 
     * @var array
     */
    protected $default_tree_rules = array (
        'display_types' => '',
    	'split_by'=>'number',
    	'limit'=>30,
    	'split_letter_field'=>'name',
        'sort_field' => 'name',
        'sort_direction'=>'ASC',
    	'caption_sql'=>'name',
    	'caption_alias'=>'name'
    );
    
    /**
     * @var Admin_Model_Common
     */
    private $object_model;
    
    /**
	 * @var Db_Links
	 */
	private $links;
    
    /**
     * Виртуальная функция, принимающая объект класса, реализующего интерфейс ArrayAccess
     *
     * @param ArrayAccess $tree_rules
     */
    abstract protected function setObjectTreeRules (ArrayAccess $tree_rules);
    
    public function __construct () {
        
        $this->setDefaultTable(Admin_Core::DEFAULT_TABLE);
        $this->setId(Admin_Core::getItemId());
        $this->object_model = Admin_Controller_Factory::createObject('Admin_Model',Admin_Core::getItemType());
        
    }
    
    /**
     * Устанавливает id элементов, которые следует исключить при построении дерева
     *
     * @param array|integer $exclude_ids
     */
    public function setExcludeIds ($exclude_ids) {
        
        if (!$exclude_ids) return ;
        $exclude_ids = (array)$exclude_ids;
        $this->exclude_ids += $exclude_ids;
        $this->exclude_ids = array_unique($this->exclude_ids);
        
    }
    
    /**
     * Синтаксический разбор секции display_types в конфиге
     * 
     * @return string часть SQL запроса
     */
    private function getDisplayTypes () {
    	
    	if (!isset($this->tree_rules['display_types'])) return false;
    	
    	if (strpos($this->tree_rules['display_types'],',')!==false) {
    		$display_types = explode(',',$this->tree_rules['display_types']); 
    	} else {
    		$display_types = array($this->tree_rules['display_types']);
    	}
    	
    	$query_parts = array();
    	foreach ($display_types as $type) {
    		if (strpos($type,'-')===0) {    			
    			$query_parts[] =  '(`type`<>'.$this->escape(substr($type,1)).' AND template<>'.$this->escape(substr($type,1)).')';
    		} else {
    			$query_parts[] = '(`type`='.$this->escape($type).' AND template='.$this->escape($type).')';
    		}
    	}
    	$query = '('.implode(' AND ',$query_parts).')';
    	return $query;
    	
    }
    
    /**
     * Возвращает отсортированный массив начальных символов в названии уровней,
     * являющихся потомком запрошенного уровня
     *
     * @param integer $id
     * @return array
     */
    private function getLevelLetters ($id,$virtual_id=-1) {
    	
    	$this->tree_rules->setLevelId($id);
    	$letters = $this->selectCol('
    		SELECT UPPER(LEFT('.$this->tree_rules['split_letter_field'].',1)) letter FROM ?_items WHERE pid = ? {AND ?s}
			GROUP BY letter ORDER BY letter '.$this->tree_rules['sort_direction'],$id,$this->getDisplayTypes()?$this->getDisplayTypes():DBSIMPLE_SKIP);
    	if (key_exists($virtual_id,$letters)) return $letters[$virtual_id];
    	return $letters;
    	
    }
       
    /**
     * Возвращает информацию об уровне
     *
     * @param integer $id
     * @param integer $virtual_id
     * @return array
     */
    private function getLevelById ($id,$virtual_id=-1) {
        
    	if ($virtual_id<0) {
    		// правила дерева берем у родительского уровня
    		$pid = $this->selectCell('SELECT pid FROM ?_items WHERE id = ?',$id);
    		$this->tree_rules->setLevelId($pid);
    	} else {
    		$this->tree_rules->setLevelId($id);
    	}
    	$this->setLevelCaption(array('sql'=>$this->tree_rules['caption_sql'],'alias'=>$this->tree_rules['caption_alias']));
    	
        if ($virtual_id>=0) {
        	$virtual_level['virtual_id'] = $virtual_id;
            $virtual_level['id'] = $id;
            $virtual_level['pid'] = $id;
            if ($this->tree_rules['split_by']=='number') {
            	$level_length = $this->tree_rules['limit'];
            	$virtual_level[$this->level_caption['alias']] = (1+$level_length*$virtual_id).' - '.(($virtual_id+1)*$level_length);
            }
            if ($this->tree_rules['split_by']=='letter') {
            	$virtual_level[$this->level_caption['alias']] = $this->getLevelLetters($id,$virtual_id);
            }
            return $virtual_level;
        }

        $this->links = new Admin_Db_Links($this->tree_rules['link']);
        $join_on = $this->object_model->getJoinOn($this->links);
        return $this->selectRow(
	        'SELECT '.implode(',',$this->select_fields).', -1 AS virtual_id FROM ?_items'.$join_on.'
	        WHERE ?_items.id = ?',$id
	    );
        
    }
    
	/**
     * Возвращает дочерние уровни к запрошенному
     *
     * @param integer $id
     * @return array
     */
    protected function getChildsById ($id, $virtual_id=-1) {
        
        $this->tree_rules->setLevelId($id);
        $this->setLevelCaption(array('sql'=>$this->tree_rules['caption_sql'],'alias'=>$this->tree_rules['caption_alias']));
    	// уровень виртуальный
        if ($virtual_id>=0) {
            if ($this->tree_rules['split_by']=='number') {
            	$level_length = $this->tree_rules['limit'];
                $limit_start = $virtual_id*$level_length;
                $limit_end = $level_length;
				$this->links = new Admin_Db_Links($this->tree_rules['link']);
				$join_on = $this->object_model->getJoinOn($this->links);
                return $this->select(
                	'SELECT '.implode(',',$this->select_fields).', -1 AS virtual_id FROM ?_items '.$join_on.' WHERE pid = ? {AND ?s} {AND id NOT IN (?a)} ORDER BY ?f '.$this->tree_rules['sort_direction'].' LIMIT '.$limit_start.', '.$limit_end, 
                	$id,$this->getDisplayTypes()?$this->getDisplayTypes():DBSIMPLE_SKIP,
                	$this->exclude_ids?$this->exclude_ids:DBSIMPLE_SKIP,$this->tree_rules['sort_field']
                );
            }
            if ($this->tree_rules['split_by']=='letter') {
            	$letter = $this->getLevelLetters($id,$virtual_id);
				$this->links = new Admin_Db_Links($this->tree_rules['link']);
				$join_on = $this->object_model->getJoinOn($this->links);
            	return $this->select(
            		'SELECT '.implode(',',$this->select_fields).', -1 AS virtual_id FROM ?_items '.$join_on.' WHERE pid = ? {AND ?s} {AND id NOT IN (?a)} AND ?# LIKE ? ORDER BY ?f '.$this->tree_rules['sort_direction'], 
                	$id,
                	$this->getDisplayTypes()?$this->getDisplayTypes():DBSIMPLE_SKIP,
                	$this->exclude_ids?$this->exclude_ids:DBSIMPLE_SKIP,
                	$this->tree_rules['split_letter_field'],
                	$letter.'%',
                	$this->tree_rules['sort_field']
            	);
            }
        }
        
        // возвращаем виртуальные подуровни, если есть необходимость
        if ($this->tree_rules['split_by']=='number') {
            if ($this->checkChildsById($id)>$this->tree_rules['limit']) {
                // возвращаем виртуальные уровни
                $virtual_levels = array();
                $level_count = ceil($this->checkChildsById($id)/$this->tree_rules['limit']);
                $level_length = $this->tree_rules['limit'];
                for ($i=0;$i<$level_count;$i++) {
                    $virtual_levels[$i]['virtual_id'] = $i;
                    $virtual_levels[$i]['id'] = $id;
                    $virtual_levels[$i]['pid'] = $id;
                    $virtual_levels[$i][$this->level_caption['alias']] = (1+$level_length*$i).' - '.(($i+1)*$level_length);
                }
                return $virtual_levels;
            }
        }
    	if ($this->tree_rules['split_by']=='letter') {
    		$letter = $this->getLevelLetters($id);
            if (count($letter)>0) {
                // возвращаем виртуальные уровни
                $virtual_levels = array();
                for ($i=0;$i<count($letter);$i++) {
                    $virtual_levels[$i]['virtual_id'] = $i;
                    $virtual_levels[$i]['id'] = $id;
                    $virtual_levels[$i]['pid'] = $id;
                    $virtual_levels[$i][$this->level_caption['alias']] = $letter[$i];
                }
                return $virtual_levels;
            }
        }
        
        $this->links = new Admin_Db_Links($this->tree_rules['link']);
        $join_on = $this->object_model->getJoinOn($this->links);
        
        return $this->select('
        	SELECT '.implode(',',$this->select_fields).',-1 AS virtual_id FROM ?_items'.$join_on.' WHERE pid = ? {AND ?s} {AND id NOT IN (?a)} ORDER BY ?f '.$this->tree_rules['sort_direction'],
        	$id,$this->getDisplayTypes()?$this->getDisplayTypes():DBSIMPLE_SKIP,
        	$this->exclude_ids?$this->exclude_ids:DBSIMPLE_SKIP,
        	$this->tree_rules['sort_field']
        );
        
    }
    
    /**
     * Проверяет наличие потомков у запрошенного узла
     * 0 - если не существует, N - количество потомков
     *
     * @param integer $id
     * @return integer
     */
    private function checkChildsById ($id,$virtual_id=-1) {
        
    	$this->tree_rules->setLevelId($id);
        // количество потомков у виртуального уровня
        if ($virtual_id>=0) {            
            return true;
        }

        return $this->selectCell(
        	'SELECT COUNT(id) FROM ?_items WHERE pid = ? {AND ?s} {AND id NOT IN (?a)}',
            $id,$this->getDisplayTypes()?$this->getDisplayTypes():DBSIMPLE_SKIP,$this->exclude_ids?$this->exclude_ids:DBSIMPLE_SKIP);
        
    }
    
    /**
     * Возвращает номер виртуального уровня
     *
     * @param integer $id
     * @return integer
     */
    private function getVirtualLevelById ($id) {

    	// правила дерева берем у родительского уровня
    	$pid = $this->selectCell('SELECT pid FROM ?_items WHERE id = ?',$id);
    	$this->tree_rules->setLevelId($pid);
    	$this->setLevelCaption(array('sql'=>$this->tree_rules['caption_sql'],'alias'=>$this->tree_rules['caption_alias']));
    	// определяем виртуальный уровень по первой букве узла
    	if ($this->tree_rules['split_by']=='letter') {
    		$first_char = $this->selectCell(
    			'SELECT UPPER(LEFT('.$this->tree_rules['split_letter_field'].',1)) FROM ?_items WHERE id = ?',$id);
    		return array_search($first_char,$this->getLevelLetters($pid)); 
    	}
    	$level_length = $this->tree_rules['limit'];
    	// выбираем все элементы уровня, на котором находится запрошенный узел и определяем виртуальный уровень
    	$i = 0;
    	$level = false;
    	$this->links = new Admin_Db_Links($this->tree_rules['link']);
		$join_on = $this->object_model->getJoinOn($this->links);
    	foreach ($this->select(
    			'SELECT '.implode(',',$this->select_fields).' FROM ?_items '.$join_on.'
    			WHERE pid = (SELECT pid FROM ?_items WHERE id = ?) {AND ?s}
    			ORDER BY ?f '.$this->tree_rules['sort_direction'],
    			$id,
    			$this->getDisplayTypes()?$this->getDisplayTypes():DBSIMPLE_SKIP,
    			$this->tree_rules['sort_field']) as $node) {
   			$level = intval(($i<$level_length) ? 0 : floor($i/$level_length));
   			if ($id == $node['id']) break;  
    		$i++;
    	}
    	return $level;	
    	
    }
    
    /**
     * Обработка AJAX запроса и вывод результата
     *
     * @param integer $id
     * @param integer $virtual_id номер виртуального уровня
     */
    public function processAjaxRequest ($id,$virtual_id) {
        
    	$output = '';
		foreach ($this->getChildsById($id,$virtual_id) as $child) {
			$css_class = 'text';
			if ($child['virtual_id']>=0) {
		        // виртуальный уровень
		        $css_class = $this->css_classes['virtual'];
		    }
		    $output .= '
		    <li>
				<span class="'.$css_class.'" id="'.$child['id'].'">
					'.$child[$this->level_caption['alias']].'			
				</span>';
				if ($this->checkChildsById($child['id'],$child['virtual_id'])) {
				    $output .= '
				<ul class="ajax">
					<li>{url:'.$this->ajax_script.'&amp;id='.$child['id'].'&amp;virtual_id='.$child['virtual_id'].'}</li>
				</ul>';
		        }
		    $output .=
		     '</li>';
		}
		echo $output;
		
    }
	
    public function initTree ($id=1) {
    	
    	if ($this->requested_node_id) {
    	    if ($this->requested_node_id==1) {
    	        $node = $this->getLevelById($id);
    	        $css_class = $this->css_classes['active'];
	            $folder_state = 'folder-close';
    	        $output = 
    		    	'<li class="'.$folder_state.'">
    					<span class="'.$css_class.'" id="'.$id.'">'.$node[$this->level_caption['alias']].'</span>';
    	    	if ($this->checkChildsById($id)) {
    				$output .= 
    					'<ul class="ajax">
    						<li>{url:'.$this->ajax_script.'&amp;id='.$id.'}</li>
    					</ul>';
    			}
    			$output .=
    				'</li>';
    	    } else {
    		    $this->calcPath($this->requested_node_id);
    		    $output = $this->buildToId($this->root_id);
    	    }
    	} else {
	    	$node = $this->getLevelById($id);
	    	$output = 
		    	'<li>
					<span class="text" id="'.$id.'">'.$node[$this->level_caption['alias']].'</span>';
	    	if ($this->checkChildsById($id)) {
				$output .= 
					'<ul class="ajax">
						<li>{url:'.$this->ajax_script.'&amp;id='.$id.'}</li>
					</ul>';
			}
			$output .=
				'</li>';
    	}
    	
    	return $output;
    	
    }
    
    /**
     * Построение открытого до запрошенного узла дерева
     *
     * @param integer $id
     */
    private function buildToId ($id,$virtual_id=-1) {
            	
        $node = $this->getLevelById($id,$virtual_id);
        // CSS класс открытого уровня
        $folder_state = 'folder-open';
	    
	    // ставим CSS класс для определенных уровней
	    if ($node['virtual_id']>=0) {
	        // виртуальный уровень
	        $css_class = $this->css_classes['virtual'];
        } elseif ($id == $this->path_to_node[count($this->path_to_node)-1]) {
            // активный уровень
	        $css_class = $this->css_classes['active'];
	        $folder_state = 'folder-close';
	    } else {
	        // уровень-предок активного уровня
	        $css_class = $this->css_classes['sub-active'];
	    }
	    $output = '
	        <li class="'.$folder_state.'">
				<span class="'.$css_class.'" id="'.$node['id'].'">
					'.$node[$this->level_caption['alias']].'			
				</span>			
			';
	    // child nodes
	    $childs = $this->getChildsById($node['id'],$node['virtual_id']);
	    if ($this->checkChildsById($node['id'],$node['virtual_id'])) $output .= '<ul>';
	    foreach ($childs as $child) {
	        // индекс текущего в пути уровня
	        $node_index = array_search($node['id'],$this->path_to_node);
	        if (isset($this->path_to_node[$node_index+1])) {
	            // если уровень виртуальный, то id дочернего узла будет следующий в пути узел
	            $child_id = $this->path_to_node[$node_index+1];
	        } else {
                $child_id = $child['id'];
	        }
	        if (
	            (in_array($child['id'],$this->path_to_node) && $child['virtual_id']<0 &&
	            array_search($child['id'],$this->path_to_node)!=(count($this->path_to_node)-1)) ||
	            ($child['virtual_id'] === $this->getVirtualLevelById($child_id))
	            ) { 
	            // открываем уровень
                $output .= $this->buildToId($child['id'],$child['virtual_id']);
            } else {
            	$css_class = 'text';
                if ($child['id'] == $this->path_to_node[count($this->path_to_node)-1]) {
                    $css_class = $this->css_classes['active'];
                } elseif ($child['virtual_id']>=0) {
                	$css_class = $this->css_classes['virtual'];
                }
                $output .= '
                <li>
                    <span class="'.$css_class.'" id="'.$child['id'].'">'.
                		$child[$this->level_caption['alias']].
                    '</span>';
                if ($this->checkChildsById($child['id'],$child['virtual_id'])) {
                    $output .= '
                        <ul class="ajax">
                		    <li>{url:'.$this->ajax_script.'&amp;id='.$child['id'].'&amp;virtual_id='.$child['virtual_id'].'}</li>
                	    </ul>';
                }
        	    $output .= '
                 </li>';
            }
	    }
	    if ($this->checkChildsById($node['id'])) $output .= '</ul>';    
	    $output .= '
	      </li>';
	    return $output;
        
    }
    
    /**
     * Вычисление пути к запрошенному узлу
     *
     * @param integer $id
     */
    private function calcPath ($id) {
        
        $this->path_to_node = array($id);
	    $element = $this->getLevelById($id);	    
	    while (count($element)) {
	    	$pid = $element['pid'];
	        array_unshift($this->path_to_node,$pid);
	        $element = $this->getLevelById($pid);
	    }
	    array_shift($this->path_to_node);
	    	
    }
    
    /**
     * Устанавливает правило для выбора заголовков уровней
     *
     */
    protected function setLevelCaption ($level_caption) {
    	
    	$caption = ($level_caption['sql']!=$level_caption['alias']) ?
    				$level_caption['sql'].' AS '.$level_caption['alias'] : $level_caption['alias'];
    	if (!isset($this->level_caption)) {
    		$this->level_caption = $level_caption;
    		$this->select_fields = array_merge($this->select_fields,array($caption));
    	} else {
    		$this->level_caption = $level_caption;
    		array_pop($this->select_fields);
    		array_push($this->select_fields,$caption);
    	}
    	
    }
    
}

?>