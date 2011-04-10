<?php

/**
 * Класс для работы с загрузкой файлов на сервер
 *
 */
class _Upload extends HTTP_Upload {
	
	/**
	 * Объект для работы с загруженным файлом
	 *
	 * @var HTTP_Upload_File
	 */
	public $objectFiles;
	
	/**
	 * Объект конфигурационного файла
	 *
	 * @var Admin_Template_config
	 */
	private $objectConfig;
	
	/**
	 * Директория для загрузки файла
	 *
	 * @var string
	 */
	protected $upload_dir;
	
	/**
	 * @see HTTP_Upload::HTTP_Upload
	 *
	 */
	public function __construct($file_name,$lang=null) {
		
		parent::__construct($lang);
		$this->objectConfig = Admin_Core::getObjectGlobalConfig();
		$this->objectFiles = $this->getFiles($file_name);
		$this->objectFiles->setName('real');
	
	}
	
	/**
	 * Устанавливает директорию для загрузки файлов в соответствии с глобальной конфигурацией
	 *
	 * @param string $section
	 * @param string $name
	 * @param string $postfix
	 */
	public function setUploadDir ($section,$name,$postfix='') {
		
		$this->upload_dir = FRONT_SITE_PATH.'/'.$this->objectConfig->getConfigSection($section,$name);
		
		if ($postfix) {
			self::create_dirs($postfix,$this->upload_dir);
			$this->upload_dir = $this->upload_dir.$postfix;
		}
		
	}
	
	public static function create_dirs($path,$start_dir) {
		
		$path = str_replace(array('\\\\','\\'),'/',$path);
		$parts = explode('/',$path);
		$cumulate_path = '';
		foreach ($parts as $part) {
			if (!$part)
				continue;
			$cumulate_path = $cumulate_path.'/'.$part;
			if (!file_exists($start_dir.'/'.$cumulate_path)) {
				// try to create
				if (!mkdir($start_dir.'/'.$cumulate_path,0775)) {
					throw new Admin_FileException('Невозможно создать каталог: '.$start_dir.'/'.$cumulate_path);
				}
			}
		}
		
	}
	
	/**
	 * Возвращает директорию для загрузки файлов
	 * 
	 * @return string
	 */
	public function getUploadDir () {
		
		return $this->upload_dir;
		
	}

	/**
	 * Возвращает имя временного файла
	 *
	 * @return string
	 */
	public function getTmpName () {
	    
	    return $this->objectFiles->getProp('tmp_name');
	    
	}
	
	/**
	 * Выполняет загрузку файла
	 */
	public function upload () {
		
		$this->objectFiles->_chmod = 0777;
		$this->objectFiles->moveTo($this->upload_dir);
		if ($this->objectFiles->isError()) {
			throw new Admin_FileException($this->objectFiles->getMessage());
		}
		
	}
	
	/**
	 * Возвращает параметр секции конфигурационного файла
	 *
	 * @param string $section
	 * @param string $name
	 * @return mixed
	 */
	public function getConfigParam ($section,$name) {
		
		return $this->objectConfig->getConfigSection($section,$name);
		
	}
	
	/**
	 * Проверяет соответствие размеров изображения
	 *
	 * @param integer $need_width
	 * @param integer $need_height
	 * @return bool
	 */
	public function checkImageSize ($need_width,$need_height) {
		
		list($width,$height) = getimagesize($this->objectFiles->getProp('tmp_name'));
		return ($width==$need_width && $height==$need_height);
		
	}
	
	public function getImageWidth() {
		
		$size = getimagesize($this->objectFiles->getProp('tmp_name'));
		return $size[0];
		
	}

	public function checkMaxImageSize ($max_width,$max_height) {
		
		list($width,$height) = getimagesize($this->objectFiles->getProp('tmp_name'));
		return ($width<=$max_width && $height<=$max_height);
		
	}
	
	public function getImageHeight() {
		
		$size = getimagesize($this->objectFiles->getProp('tmp_name'));
		return $size[1];
		
	}
	
	/**
	 * Проверяет соответствие размеру
	 *
	 * @param integer $size
	 * @return bool
	 */
	public function checkSize ($size) {
		
		return $this->objectFiles->getProp('size')<=$size;
		
	}
	
	/**
	 * Проверяет соответствие MIME типу
	 *
	 * @param string $allowed_types
	 * @return bool
	 */
	public function checkType ($allowed_types) {
		
		$allowed_types = Admin_Template_Config::explode(',',$allowed_types);
		return in_array($this->objectFiles->getProp('type'),$allowed_types);
		
	}
	
}

?>
