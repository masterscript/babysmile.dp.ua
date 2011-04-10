<?php

/**
 * Класс для работы с изображениями
 *
 */
class _Images {
	const ERROR_TYPE_MISMATCH = 2;
    
    /**
     * Путь к изображению и имя файла
     *
     * @var string
     */
    private $filename;
    
    /**
     * Путь для сохранения измененного изображения
     * Если не задан, то заменяется исходное изображение
     *
     * @var string
     */
    private $save_path;
    
    /**
     * Ресурс
     *
     * @var resource
     */
    private $resource;
    
    /**
     * Тип изображения
     *
     * @var string
     */
    private $type;
    
    private $allowed_types = array('jpeg','gif');
    /**
     * Максимальное значение ширины или высоты изображения в пикселях
     *
     * @var integer
     */
    private $limit_pixels = 0;
	
    /**
     * Конструктор класса
     *
     * @param string $filename
     */
	public function __construct($filename) {
	    
	    if (!file_exists($filename))
	    	throw new Admin_ImagesException('Изображение не существует по указанному пути: '.$filename);
	    	
	    $this->filename = $filename;
	    
	    $this->setType();
	    $this->setResource();
	
	}	
	
	/**
	 * Определяет тип изображения
	 *
	 */
	private function setType () {
	    
	    // get actual image type
		$ext = strtolower(str_replace("image/", "", image_type_to_mime_type(exif_imagetype($this->filename))));
		// adjustment for IE mime types
		$ext_adjust = array("pjpeg"=>"jpeg", "x-png"=>"png");		
		$ext_file=(array_key_exists($ext, $ext_adjust)) ? $ext_adjust[$ext] : $ext;
		
		if (!in_array($ext_file,$this->allowed_types)) {
			throw new Admin_ImagesException('Тип изображения не поддерживается',self::ERROR_TYPE_MISMATCH);
		}
		        
        $this->type = $ext;
        
	}
	
	/**
	 * Устанавливает ресурс изображения
	 *
	 */
	private function setResource () {
	    
	    if ($this->type=='jpeg') {
	        $this->resource = imagecreatefromjpeg($this->filename);
	    } elseif ($this->type=='gif') {
	        $this->resource = imagecreatefromgif($this->filename);
	    } elseif ($this->type=='png') {
	        $this->resource = imagecreatefrompng($this->filename);
	    } else {
	        throw new Admin_ImagesException('Тип не поддерживается: '.$this->type,self::ERROR_TYPE_MISMATCH);
	    }
	    
	}
	
	/**
	 * Преобразовывает цвет из шестнадцатеричного формата в RGB
	 *
	 * @param string $hex_color
	 */
	private function hexToRgb ($hex_color) {
	    
	    $red = 0; $green = 0; $blue = 0;
	    sscanf($hex_color,"%2x%2x%2x",$red,$green,$blue);
	    return array($red,$green,$blue);
	    
	}
	
	public function setLimitPixels ($pixels) {
	    
	    $this->limit_pixels = $pixels;
	    
	}
	
	public function resize ($background_color=false,$width=false,$height=false) {
	    
	    $sx = imagesx($this->resource);
	    $sy = imagesy($this->resource);
        
        if (($sx==$this->limit_pixels && $sy==$this->limit_pixels) || ($sx==$width && $sy==$height)) {
            return false;
        }
        
        if ($width!==false && $height===false) {
        	$delta = $sx/$width;
        	$height = ceil($sy/$delta);
        } elseif ($width===false && $height!==false) {
        	$delta = $sy/$height;
        	$width = ceil($sx/$delta);
        } elseif ($this->limit_pixels>0 && ($this->limit_pixels<$sx || $this->limit_pixels<$sy)) {
	        // масштабирование по большей стороне
	        if ($sx>$sy) {
	            $delta = $sx/$this->limit_pixels;
	            $width = $this->limit_pixels;
	            $height = ceil($sy/$delta);
	        } else {
	            $delta = $sy/$this->limit_pixels;
	            $height = $this->limit_pixels;
	            $width = ceil($sx/$delta);
	        }
	    } elseif ($width===false && $height==false) {
	        $width = $sx;
	        $height = $sy;
	        $max = $this->limit_pixels;
	    }
	    
	    if ($background_color) {
	        if (!isset($max)) {
	            $max = max($width,$height);
	        }
	        $bg_image = imagecreatetruecolor($max,$max);
	        // получаем цвет
	        list($r,$g,$b) = $this->hexToRgb($background_color);
	        $color = imagecolorallocate($bg_image,$r,$g,$b);
	        imagefilledrectangle($bg_image,0,0,$max,$max,$color);
	        $dstX = ceil(($max-$width)/2);
	        $dstY = ceil(($max-$height)/2);
	    } else {
	    	$bg_image = imagecreatetruecolor($width,$height);
	        $dstX = $dstY = 0;
	    }
	    
	    // вычисляем координаты, чтобы изображение находилось по центру
	    
	    // выполняем операцию над изображением
	    if ($sx>$width && $sy>$height) {
    		imagecopyresampled($bg_image,$this->resource,$dstX,$dstY,0,0,$width,$height,$sx,$sy);
    		$this->resource = $bg_image;
	    }
        
        return true;
	    
	}
	
	/**
	 * @return string
	 */
	public function getType () { return $this->type; }
	public function save ($new_name=false) {
	    
		if ($new_name===false) {
			$image_name = basename($this->filename);
		} else {
			$image_name = $new_name;
		}
		
	    if ($this->save_path) {
	        $save_path = $this->save_path.'/'.$image_name;
	    } else {
	        $save_path = dirname($this->filename).'/'.$image_name;
	    }
	    
	    if ($this->type=='jpeg') {
	        imagejpeg($this->resource,$save_path,95);
	    } elseif ($this->type=='gif') {
	        imagegif($this->resource,$save_path);
	    } elseif ($this->type=='png') {
	        imagepng($this->resource,$save_path);
	    } else {
	        throw new Admin_ImagesException('Тип не поддерживается: '.$this->type);
	    }
	    
	}
	
	/**
	 * Переобразует изображение в оттенки серого
	 *
	 * @author http://zmei.name/page/modifikacija-image2graycolor
	 */
	public function greyscale(){		
				
		$gd = gd_info();					
		
		if( $this->type == 'png' AND $gd['PNG Support'] == 1 ){
			 					
			imagesavealpha( $this->resource, TRUE );
			imagefilter( $this->resource, IMG_FILTER_GRAYSCALE );
						
		} elseif( $this->type == 'jpeg' AND $gd['JPG Support'] == 1 ) { 

			if ( ($color_total = imagecolorstotal( $this->resource )) == false ) {
		        $color_total = 256;		          
		    }   	          		          
		    imagetruecolortopalette( $this->resource, false, $color_total );    
		    
		    for( $c = 0; $c < $color_total; $c++ ) {    
		         $col = imagecolorsforindex( $this->resource, $c );		        
				 $i   = ( $col['red']+$col['green']+$col['blue'] )/3;
		         imagecolorset( $this->resource, $c, $i, $i, $i );
		    }		    
		    
		} elseif( $this->type == 'gif' AND $gd['GIF Create Support'] == 1  ) { 
		    
			if ( ($color_total = imagecolorstotal( $this->resource )) == false ) {
		        $color_total = 256;		          
		    }
		       
		    imagetruecolortopalette( $this->resource, false, $color_total );
		        
		    for( $c = 0; $c < $color_total; $c++ ) {    
		         $col = imagecolorsforindex( $this->resource, $c );		        
				 $i   = ( $col['red']+$col['green']+$col['blue'] )/3;
		         imagecolorset( $this->resource, $c, $i, $i, $i );
		    }
		    		    
		}
									
	}
	
}

?>
