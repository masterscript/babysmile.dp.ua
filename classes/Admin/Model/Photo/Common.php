<?php

class Admin_Model_Photo_Common extends Admin_Model_Photogallery {

	public function __construct() {
		
		$this->setGalleryId('photos');
		$this->setPhotoTable('object_photos');
		parent::__construct();
		
	}
	
}
