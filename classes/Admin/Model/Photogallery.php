<?php

abstract class Admin_Model_Photogallery extends Admin_Model_Abstract {

	private $_galleryId;
	private $_photoTable;
	
	private $_photoId;
	
	public function __construct() {
		
		$this->_photoId = isset($_GET['photo_id']) ? $_GET['photo_id'] : false;
		parent::__construct();
		
	}
	
	public function getGalleryId() {
		
		return $this->_galleryId;
		
	}
	
	public function setGalleryId($galleryId) {
		
		$this->_galleryId = $galleryId;
		
	}

	
	public function getPhotoTable() {
		
		return $this->_photoTable;
		
	}
	
	public function setPhotoTable($photoTable) {
		
		$this->_photoTable = $photoTable;
		
	}
	
	public function getPhotoFolder($photo_id=false) {
		
		if (!$photo_id)
			$photo_id = $this->_photoId;
		
		return
			Admin_Core::getObjectGlobalConfig()->getConfigSection('FOLDERS',$this->_galleryId).$this->id.'/'.$photo_id.'/';
		
	}
	
	/**
	 * @see Admin_Model_Abstract::insert()
	 */
	public function insert() {
		
		// обработка загрузки топового изображения
		if (!empty($this->db_fields['object_photos']['filename'])) {

			// вставка в таблицу
			$lastId = $this->query(
				'INSERT INTO ?_?s (item_id,filename) VALUES (?d,?)',
				$this->_photoTable,$this->id,$this->db_fields['object_photos']['filename']
			);
			
	    	// выполняем загрузку файла
			$objectUpload = new _Upload('object_photos::filename','ru');
			$objectUpload->setUploadDir('FOLDERS',$this->_galleryId,$this->id.'/'.$lastId);
	        $objectUpload->upload();
	        
	        $filename = $this->db_fields['object_photos']['filename'];
	        
		    // масштабирование
	        $image_src = $objectUpload->getUploadDir().'/'.$filename;
	    	$objectImages = new _Images($image_src);
	    	
	        $objectImages->resize(false,232);
            $objectImages->save('medium_'.$filename);
            
            $objectImages->resize(false,50);
            $objectImages->save('small_'.$filename);
		        
		}
		
	}
	
	public function show() {
		
		$photos = $this->select('SELECT id photo_id,filename small_photo FROM ?_?s WHERE item_id = ?d ORDER BY sort',$this->_photoTable,$this->id);
		
		foreach ($photos as $k=>$photo) {
			$photo['small_photo'] =
				$this->getPhotoFolder($photo['photo_id']).'small_'.$photo['small_photo'];
			$photos[$k] = $photo;
		}
		
		return $photos;
		
	}
	
	public function delete() {

		$filename = $this->getItem('filename',$this->_photoTable,$this->_photoId);
		$this->deleteRecord($this->_photoTable,$this->_photoId);
		
		// delete files
		@unlink(FRONT_SITE_PATH.'/'.$this->getPhotoFolder().$filename);
		@unlink(FRONT_SITE_PATH.'/'.$this->getPhotoFolder().'small_'.$filename);
		@unlink(FRONT_SITE_PATH.'/'.$this->getPhotoFolder().'medium_'.$filename);
		
	}
	
}
