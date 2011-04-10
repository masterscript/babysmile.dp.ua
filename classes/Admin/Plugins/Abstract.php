<?php

abstract class Admin_Plugins_Abstract implements Admin_IController {
	
	/**
	 * Объект для работы с базой данных
	 *
	 * @var Admin_Db_Main
	 */
	protected $objectDb;
	
	static $PATH_TO_IMAGES;	
	static $PATH_TO_BIG_IMAGES;
	
	public function __construct() {
		
		$this->objectDb = Admin_Core::getObjectDatabase();
		$objectConfig = Admin_Core::getObjectGlobalConfig();
		
		self::$PATH_TO_IMAGES = $objectConfig->getConfigSection('FOLDERS','content_images');
		self::$PATH_TO_BIG_IMAGES = $objectConfig->getConfigSection('FOLDERS','content_big_images');
		
		// настраиваем слеши в пути
		if (self::$PATH_TO_IMAGES[0]!='/') self::$PATH_TO_IMAGES = '/'.self::$PATH_TO_IMAGES;
		$length = strlen(self::$PATH_TO_IMAGES);
		if (self::$PATH_TO_IMAGES[$length-1]=='/') self::$PATH_TO_IMAGES = substr(self::$PATH_TO_IMAGES,0,$length-1);
		
		if (self::$PATH_TO_BIG_IMAGES[0]!='/') self::$PATH_TO_BIG_IMAGES = '/'.self::$PATH_TO_BIG_IMAGES;
		$length = strlen(self::$PATH_TO_BIG_IMAGES);
		if (self::$PATH_TO_BIG_IMAGES[$length-1]!='/') self::$PATH_TO_BIG_IMAGES = self::$PATH_TO_BIG_IMAGES.'/';
	
	}
	
}

?>
