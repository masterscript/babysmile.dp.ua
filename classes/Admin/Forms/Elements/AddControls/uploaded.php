<?php

/**
 * Класс, создающий дополнительные элементы для поля загрузки файлов
 *
 */
class Admin_Forms_Elements_AddControls_uploaded extends HTML_QuickForm_element {
	
	/**
	 * Объект модели
	 *
	 * @var Admin_Model_Common
	 */
	private $objectModel;
	
	/**
	 * Название поля, для которого производится добавление новых элементов
	 *
	 * @var string
	 */
	private $field;
	
	/**
	 * Конструктор класса
	 *
	 * @param Admin_Model_Common $objectModel
	 */
	public function __construct ($objectModel,$field) {
		
		$this->objectModel = $objectModel;
		$this->field = $field;
		
	}
	
	/**
	 * @see HTML_Common::toHtml()
	 *
	 * @return string
	 */
	public function toHtml() {
		
		// проверяем, если загруженного файла нет, то возвращаем пустую строку
		list($table,$field) = $this->objectModel->parseField($this->field);
		$file_name = $this->objectModel->getItem($field,$table,false,'id');
		if ($file_name=='') {
			return '';
		}
		
		// создаем checkbox
		$checkbox = new HTML_QuickForm_checkbox('ch_delete_file',' удалить файл',' удалить файл');
		// создаем изображение
		$objectConfig = Admin_Core::getObjectGlobalConfig();
		$image_src = $objectConfig->getConfigSection('FOLDERS','top_images').$this->objectModel->getId().'/'.$file_name;
		$image_html = '<img src="'.$image_src.'" />';
		return  $checkbox->toHtml().'<br />'.$file_name.'<br />'.$image_html; 
		
	}
 
	
}

?>
