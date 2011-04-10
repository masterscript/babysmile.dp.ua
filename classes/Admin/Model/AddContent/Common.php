<?php

/**
 * Класс, реализующий добавление контента к странице
 *
 */
class Admin_Model_AddContent_Common extends Admin_Model_Abstract {
    
	public function __construct() {
		
		parent::__construct();
	
	}
	
	private function strip_tags ($string) {
	    
	    $search = array ("'<script[^>]*?>.*?</script>'si", 
                 "'<[\/\!]*?[^<>]*?>'si", 
                 "'([\r\n])[\s]+'", 
                 "'&(quot|#34);'i", 
                 "'&(amp|#38);'i", 
                 "'&(lt|#60);'i", 
                 "'&(gt|#62);'i", 
                 "'&(nbsp|#160);'i", 
                 "'&(iexcl|#161);'i", 
                 "'&(cent|#162);'i", 
                 "'&(pound|#163);'i", 
                 "'&(copy|#169);'i", 
                 "'&#(\d+);'e"); 

        $replace = array ("", 
                  "", 
                  "\\1", 
                  "\"", 
                  "&", 
                  "<", 
                  ">", 
                  " ", 
                  chr(161), 
                  chr(162), 
                  chr(163), 
                  chr(169), 
                  "chr(\\1)"); 

        return preg_replace($search, $replace, $string);
	    
	}
	
	/**
	 * @see Admin_Model_Abstract::insert()
	 *
	 */
	public function insert () {
		
		// создаем файл для сохранения контента
		$content = $this->db_fields['content']['words'];
		$objectConfig = Admin_Core::getObjectGlobalConfig();
		$content_path = basename($objectConfig->getConfigSection('FOLDERS','content'));
		$filename = SYSTEM_PATH.'/'.$content_path.'/'.$this->getItem('id').'.html';
		$f_res = fopen($filename,'w+');
		// контролируем абсолютный путь к изображению и ссылкам
		$matches = array();
		if (preg_match_all('#<img.+?src="([^"].+?)".+?\s/>#',$content,$matches)) {
			$matches = array_unique($matches[1]);
		    foreach ($matches[1] as $img_src) {
		        if ($img_src[0]!='/' && strpos($img_src,'http')===false) {
		            $content = str_replace($img_src,'/'.$img_src,$content);
		        }
		    }
		}
	    $matches = array();
		if (preg_match_all('#<a.+?href="([^"].+?)".+?>#',$content,$matches)) {
            $matches = array_unique($matches[1]);
		    foreach ($matches as $link) {
		    	var_dump();
		        if ($link[0]!='/' && strpos($link,'http')===false && strpos($link,'mailto:')===false) {
		            $content = str_replace($link,'/'.$link,$content);
		        }
		    }
		}
		// заменяем ссылку на служебную страницу фронтэнда для полноразмерного просмотра
		$content = str_replace('href="img/?id=','href="/img/?id=',$content);
		fwrite($f_res,$content);
		fclose($f_res);
		// обрезаем теги для вставки в таблицу БД
		$this->db_fields['content']['words'] = $this->strip_tags($content);
		if ($this->recordExists('content',array('id'=>$this->getItem('id')))) {
		    $this->update();
		} else {
		    $this->db_fields['content']['id'] = $this->getItem('id');
		    parent::insert();
		}
		
 	}

	
}

?>
