<?php

class Admin_Plugins_Imgcontent extends Admin_Plugins_Abstract {
	
	/**
	 * Максимальная ширина или высота изображения при выводе для предпросмотра
	 *
	 */
	const IMAGE_PREVIEW_MAX_SIZE = 120;
	
	public function __construct () {

		parent::__construct();
	
	}
	
	/**
	 * Возвращает список изображений, привязанных к запрошенной странице
	 *
	 * @return array
	 */
	public function getImages () {
		
		$images = array();
		$i = 0;
		foreach ($this->objectDb->show('content_images',array('item_id'=>$_REQUEST['content_id'])) as $value) {
			$image_name = FRONT_SITE_PATH.$value['img_path'];
			if (!file_exists($image_name)) continue;
		    $images[$i] = $value;
		    $images[$i]['isset_big'] = false;
		    // определяем размеры изображения
		    list($images[$i]['width'],$images[$i]['height']) = getimagesize($image_name);
			if ($images[$i]['width']>self::IMAGE_PREVIEW_MAX_SIZE) {
		        $delta = $images[$i]['width']/self::IMAGE_PREVIEW_MAX_SIZE;
		        $images[$i]['width'] = self::IMAGE_PREVIEW_MAX_SIZE;
		        $images[$i]['height'] = ceil($images[$i]['height']/$delta);        
		    }
		    if ($images[$i]['height']>self::IMAGE_PREVIEW_MAX_SIZE) {
		        $delta = $images[$i]['height']/self::IMAGE_PREVIEW_MAX_SIZE;
		        $images[$i]['height'] = self::IMAGE_PREVIEW_MAX_SIZE;
		        $images[$i]['width'] = ceil($images[$i]['width']/$delta);        
		    }
		    if (file_exists(FRONT_SITE_PATH.self::$PATH_TO_IMAGES.self::$PATH_TO_BIG_IMAGES.'/'.$_REQUEST['content_id'].'/'.basename($value['img_path']))) {
	        	$images[$i]['isset_big'] = true;
	        	$images[$i]['big_img_path'] = Admin_Plugins_Imgcontent::$PATH_TO_IMAGES.Admin_Plugins_Imgcontent::$PATH_TO_BIG_IMAGES.$_REQUEST['content_id'].'/'.basename($value['img_path']);        
	    	}
	    	$i++;
		}
		return $images;
		
	}
	
	/**
	 * @see Admin_IController::getTemplateValue()
	 *
	 */
	public function getTemplateValue() {
		
		return array('images'=>$this->getImages());
		
	}

	
}

?>
