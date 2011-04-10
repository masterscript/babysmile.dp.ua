<?php

/**
 * Общий класс модели для действий редактирования (edit)
 * и добавления (add_*) элементов
 *
 */
class Admin_Model_Common extends Admin_Model_Abstract {
    
	public function __construct() {
		
		parent::__construct();
	
	}
	
	private function insert_tags($str_tags,$item_id) {
		
		$tags = array_unique(explode(',',$str_tags));
		
		// delete all item tags
		$this->deleteRecord('tags_items',$item_id,'item_id');
		
		foreach ($tags as $tag) {
			
			$tag = trim($tag);
			
			if (!empty($tag)) {
								
				$tag_id = $this->getItem('id','tags',$tag,'name');
				
				if (!$tag_id) {
					// add new tag
					$this->insertRecord('tags',array('name'=>$tag));
					$tag_id = mysql_insert_id();
				}
				
				$this->insertRecord('tags_items',array('item_id'=>$item_id,'tag_id'=>$tag_id));
				
			}
			
		}
		
		// delete unlinked tags
		$this->query('DELETE FROM tags WHERE id NOT IN (SELECT tag_id FROM tags_items)');
		
	}
	
	/**
	 * @see Admin_Model_Abstract::update()
	 *
	 */
	public function update () {
		
		if (isset($this->db_fields[$this->getDefaultTable()]['url'])
			&& !empty($this->db_fields[$this->getDefaultTable()]['url'])) {
				
			$url_old = $this->getItem('url');
			$url_new = _Strings::uri_parent($url_old).'/'.$this->db_fields[$this->getDefaultTable()]['url'];
	        if ($url_new=='/') $url_new = '';
	        // изменяем url на полный
	        $this->db_fields[$this->getDefaultTable()]['url'] = $url_new;
	        
		}
		
		// для исключения попадания пустых значений в связанную таблицу
		if (isset($this->db_fields['top_images']['filename'])) {			
		    if (empty($this->db_fields['top_images']['filename'])) {
		    	unset($this->db_fields['top_images']);
		    }
		}
		
	    // выполняем обновление
	    parent::update();
		
		// старое имя топового изображения
        $filename_old = basename($this->getItem('filename','top_images'));
	    $this->updateField('top_images','filename',$filename_old);
	    
	    if (isset($this->db_fields[$this->getDefaultTable()]['url'])) {
	    	// проверяем, изменился ли url
			if ($url_new!=$url_old) {
			    // изменяем url у всех потомков
				$this->changeChildsUrl($url_new,$url_old);
			}
		}
		
		// обработка загрузки топового изображения
		if (isset($this->db_fields['top_images']['filename'])) {

	    	// выполняем загрузку файла
			$objectUpload = new _Upload('top_images::filename','ru');
			$objectUpload->setUploadDir('FOLDERS','top_images',$this->getId());

            // удаляем старый файл
            if (!empty($filename_old)) {
            	unlink($objectUpload->getUploadDir().'/'.$filename_old);
            }

	        $objectUpload->upload();

            $filename = $this->db_fields['top_images']['filename'];

            // наложение watermark
            /*$watermark = new _Watermark();
            $im = $watermark->drawWatermark($objectUpload->getUploadDir().'/'.$filename);
            $im->writeImages($objectUpload->getUploadDir().'/'.$filename,true);*/

	        // записываем в таблицу БД		        
	        if ($this->getItem('id','top_images')=='') {
	            $this->insertRecord('top_images',array('id'=>$this->getItem('id'),'filename'=>$filename));
	        } else {
	        	// обновляем запись
	        	$this->updateField('top_images','filename',$filename);
	        }

		}
		
		// обработка загрузки hover изображения
		if (isset($this->db_fields['top_hover_images']['filename'])) {

		    if (!empty($this->db_fields['top_hover_images']['filename'])) {

		    	// выполняем загрузку файла
				$objectUpload = new _Upload('top_hover_images::filename','ru');
				$objectUpload->setUploadDir('FOLDERS','top_hover_images',$this->getId());

                // удаляем старый файл
                if (!empty($filename_old)) {
                    unlink($objectUpload->getUploadDir().'/'.$filename_old);
                }

		        $objectUpload->upload();

                $filename = $this->db_fields['top_hover_images']['filename'];

                // наложение watermark
                /*$watermark = new _Watermark();
                $im = $watermark->drawWatermark($objectUpload->getUploadDir().'/'.$filename);
                $im->writeImages($objectUpload->getUploadDir().'/'.$filename,true);*/

		        // записываем в таблицу БД		        
		        if ($this->getItem('id','top_hover_images')=='') {
		            $this->insertRecord('top_hover_images',array('id'=>$this->getItem('id'),'filename'=>$filename));
		        } else {
		        	// обновляем запись
		        	$this->updateField('top_hover_images','filename',$filename);
		        }

		    }

		}
        
        // tags process
        if (isset($_POST['tags'])) {
            $this->insert_tags($_POST['tags'],$this->getId());
        }
		
	}
	
	/**
	 * @see Admin_Model_Abstract::insert()
	 *
	 */
	public function insert () {
		
		if (isset($this->db_fields[$this->getDefaultTable()]['url'])) {
			// дополняем url до полного
			$this->db_fields[$this->getDefaultTable()]['url'] = $this->getItem('url').'/'.$this->db_fields[$this->getDefaultTable()]['url'];			
		}
		
		// получаем pid родителя
		$this->db_fields[$this->getDefaultTable()]['pid'] = $this->getItem('id');
		
		// вызываем родительский метод
		parent::insert();
		
		// обработка загрузки топового изображения
		if (isset($this->db_fields['top_images']['filename']) && !empty($this->db_fields['top_images']['filename'])) {
		    if (!empty($this->db_fields['top_images']['filename'])) {
		    	// выполняем загрузку файла
				$objectUpload = new _Upload('top_images::filename','ru');
				$objectUpload->setUploadDir('FOLDERS','top_images',$this->getLastId());
		        $objectUpload->upload();
		    }
		}
		
		// обработка загрузки hover изображения
		if (isset($this->db_fields['top_hover_images']['filename']) && !empty($this->db_fields['top_hover_images']['filename'])) {
		    if (!empty($this->db_fields['top_hover_images']['filename'])) {
		    	// выполняем загрузку файла
				$objectUpload = new _Upload('top_hover_images::filename','ru');
				$objectUpload->setUploadDir('FOLDERS','top_hover_images',$this->getLastId());
		        $objectUpload->upload();
		    }
		}
		
 	}
	
 	
	/**
 	 * @see Admin_Model_Abstract::delete()
 	 *
 	 */
 	public function delete () {

 	    // удаляем топовое изображение
 	    $objectConfig = Admin_Core::getObjectGlobalConfig();
 	    $top_img_path = FRONT_SITE_PATH.$objectConfig->getConfigSection('FOLDERS','top_images').'/'.$this->getId().'/'.$this->getItem('filename','top_images');
 	    @unlink($top_img_path);
 	    $hover_img_path = FRONT_SITE_PATH.$objectConfig->getConfigSection('FOLDERS','top_hover_images').'/'.$this->getId().'/'.$this->getItem('filename','top_hover_images');
 	    @unlink($hover_img_path);
 	    // удаляем контент
 	    $content_path = SYSTEM_PATH.'/'.basename($objectConfig->getConfigSection('FOLDERS','content')).'/'.$this->getId().'.html';
 	    @unlink($content_path);
 	    // удаляем связанные с контентом изображения
 	    foreach ($this->show('content_images',array('item_id'=>$this->getId())) as $img) {
 	        @unlink(FRONT_SITE_PATH.$img['img_path']);
 	        @unlink(FRONT_SITE_PATH.$objectConfig->getConfigSection('FOLDERS','content_images').'/'.$objectConfig->getConfigSection('FOLDERS','content_big_images').'/'.$this->getId().'/'.basename($img['img_path']));
 	    }
 	    // вызываем родительский метод
 	    parent::delete();
 	    
    }

	/**
     * Updates url field on autogenerated value.
     * Impements to exist record in default table.
     * @param integer $id
     */
    protected function _autoUrl($id) {
    	
    	// parent url + id
		$url = $this->getItem('url','items',$this->getItem('pid','items',$id)).'/'.$id;
		$this->updateField($this->getDefaultTable(),'url',$url,array('id'=>$id));
    	
    }
 	
}
