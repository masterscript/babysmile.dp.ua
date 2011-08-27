<?php

/**
 * Класс, содержащий в себе методы получения данных для зоны описания 
 *
 */
class Admin_Info_Source_Common extends Admin_Db_Abstract {
    
    /**
     * Admin_Model_Source_Common
     *
     * @var Admin_Model_Source_Common
     */
    private $objectModelSource;
	
	public function __construct() {
		
		$this->setDefaultTable(Admin_Core::DEFAULT_TABLE);
        $this->setId(Admin_Core::getItemId());
        $this->objectModelSource = Admin_Controller_Factory::createObject('Admin_Model_Source',Admin_Core::getItemType());
		
	}
    
	public function info_comments_count () {
		
		return $this->getCount('comments',array('item_id'=>$this->getId()));
		
	}
	
	/**
	 * Возвращает путь к топовому изображению элемента
	 * 
	 * @return string|bool
	 */
	public function info_top_image () {
	    
	    $top_image = $this->getItem('filename','top_images');
	    if (!empty($top_image)) {
	        $objectConfig = Admin_Core::getObjectGlobalConfig();
	        return $objectConfig->getConfigSection('FOLDERS','top_images').$this->getId().'/'.$top_image;
	    }
	    return false;
	    
	}
	
	public function info_users_sex ($name) {
	    
	    $labels = $this->objectModelSource->source_sex();
	    list($table,$field) = $this->parseField($name);
	    return $labels[$this->getItem($field,$table)];
	    
	}
	
	public function info_users_group ($name) {
	    
	    list($table,$field) = $this->parseField($name);
	    $group_id = $this->getItem($field,$table);
	    return $this->getItem('group_name','groups',$group_id);
	    
	}
	
	/**
	 * Возвращает строку в зависимости от состояния переключателя (0 или 1)
	 *
	 * @param string $name
	 * @param integer $value
	 * @return string
	 */
    public function info_checked ($name,$value) {
	    
	    if ($value==1) return 'да';
	    return 'нет';
	    
	}
	
    public function info_tags($name,$value) {
	    
	    $tags = $this->selectCol('
	    	SELECT name FROM tags_items
	    	JOIN tags t ON t.id = tag_id
	    	WHERE item_id = ?d',$this->getId());
	    
	    if (empty($tags))
	    	return 'не назначены';
	    
	    return implode(', ',$tags);
	    
	}
	
	public function info_childs_count () {
	    
//	    $childs_all = $
	    
	}
	
}

?>