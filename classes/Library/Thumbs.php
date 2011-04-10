<?php

class _Thumbs {

    /**
     * Path to source image
     *
     * @var string
     */
    private $img_src;
    
    /**
     * Path to save modifided image
     *
     * @var string
     */
    private $save_path;
    
    /**
     * Extension of source image
     *
     * @var string
     */
    private $img_extension;
      
    /**
     * Width of source image in pixels
     *
     * @var integer 
     */
    private $bigW;
    
    /**
     * Height of source image in pixels
     *
     * @var integer
     */
    private $bigH;
    
    /**
     * Decreasing coefficient
     *
     * @var float
     */
    private $coef;
    
    /**
     * Width of output small image
     *
     * @var integer
     */
    private $smallW;
    
	/**
     * Height of output small image
     *
     * @var integer
     */
    private $smallH;
    
    /**
     * Params of indicator images
     *
     * @var array
     */
    private $indicatorImages = array('white'=>'./plus.gif','black'=>'./plus_b.gif');
    
    /**
     * List of valid image extensions
     *
     * @var array
     */
    private $valid_extensions = array('jpeg','gif');
    
    public function __construct($img_src,$save_path) {
        
        if (!file_exists($img_src)) {
            throw new Exception("File $img_src does not exist");
        }
        $this->img_src = $img_src;
        $this->save_path = $save_path;
        // detecting image extension
        $path_parts = pathinfo($img_src);
        $this->img_extension = strtolower($path_parts['extension']);
        $this->img_extension = ($this->img_extension=='jpg' || $this->img_extension=='pjpeg') ? 'jpeg' : $this->img_extension;
        
        if (!in_array($this->img_extension,$this->valid_extensions)) {
            throw new Exception("Wrong image type: {$this->img_extension}");
        }
        
        $original_image = $this->getImageResource();
        $this->bigW = imagesx($original_image);
        $this->bigH = imagesy($original_image);        
        
    }
    
    /**
     * Set decrease coefficient
     *
     * @param float $coef
     */
    public function setCoef ($coef) {
        
        $this->coef = $coef;
        
    }
    
    /**
     * Set small image width 
     *
     * @param integer $width
     */
    public function setSmallWidth ($width) {
        
        $this->smallW = $width;
        
    }
    
    /**
     * Set small image height
     *
     * @param integer $height
     */
    public function setSmallHeight ($height) {
        
        $this->smallH = $height;
        
    }
    
    /**
     * Set black and white indicator images pathes
     *
     * @param string $color applies only 'black' and 'white' values
     * @param string $img_src
     * 
     * @return bool
     */
    public function setIndicatorImages ($color,$img_src) {
        
        if (array_key_exists($color,$this->indicatorImages)) {
            $this->indicatorImages[$color] = $img_src;
            if (!file_exists($img_src)) throw new Exception('Indicator image does not exist: '.$img_src);
            return true;
        }
        return false;
        
    }
    
    /**
     * Determination of decreasing method
     *
     * @return array of width and height image
     */
    public function getSmallImageSize () {
        
        if (isset($this->coef) && is_numeric($this->coef)) {            
            $coef = $this->coef;
        } elseif (isset($this->smallW) && is_numeric($this->smallW)) {
            $coef = $this->bigW/$this->smallW;
        } elseif (isset($this->smallH) && is_numeric($this->bigH)) {
            $coef = $this->bigH/$this->smallH;
        } else {
            throw new Exception('Decreasing method not detected');
        }
                
        return array(ceil($this->bigW/$coef),ceil($this->bigH/$coef));
        
    }

    /**
     * Get image resource
     *
     * @return resource
     */
    private function getImageResource ($img_src=false) {
        
        if (!$img_src) {
            $img_src = $this->img_src;
            $type = $this->img_extension;
        } else {
            $type = $this->getImageType($img_src);
        }
        if ($type=='gif') {
            return imagecreatefromgif($img_src);
        } elseif ($type=='jpeg') {
            return imagecreatefromjpeg($img_src);
        }
        
        return false;
        
    }
    
    /**
     * Detecting image extension
     *
     * @param string $image_src
     * @return string image type
     */
    private function getImageType ($image_src) {
        
        $path_parts = pathinfo($image_src);
        $type = $path_parts['extension'];
        $type = ($type=='jpg' || $type=='pjpeg') ? 'jpeg' : $type;
            
        if (!in_array($type,$this->valid_extensions)) {
            throw new Exception("Wrong image type: {$type}");
        }
        
        return $type;
        
    }
    
    /**
     * Creating empty canvas
     * 
     * @param integer $width
     * @param integer $height
     * @return resource
     */
    private function createCanvas ($width,$height) {
        
        $bigImg = false;
        if ($this->img_extension=='gif') {
            $bigImg = imagecreate($width,$height);
            imagecolortransparent($bigImg,imagecolorallocate($bigImg,0,0,0));
        } elseif ($this->img_extension=='jpeg') {
            $bigImg = imagecreatetruecolor($width,$height);
        }
        
        return $bigImg;
        
    }
	
	/**
     * Get resource link of resized image
     *
     * @return resource
     */
    private function getResizeImage () {

        $original_image = $this->getImageResource();
        $canvas = $this->createCanvas($this->bigW,$this->bigH);        
        imagecopy($canvas,$original_image,0,0,0,0,$this->bigW,$this->bigH);
        $small_image_size = $this->getSmallImageSize();
        $small_image = $this->createCanvas($small_image_size[0],$small_image_size[1]);
        imagecopyresized($small_image,$original_image,0,0,0,0,$small_image_size[0],$small_image_size[1],$this->bigW,$this->bigH);
        
        return $small_image;
        
    }
    
    /**
     * Adding indicator on right bottom corner
     *
     * @return resource
     */
    private function addIndicator () {
        
        $colors=array();
        $indSize = getimagesize($this->indicatorImages['white']);
        $indW = $indSize[0];
        $indH = $indSize[1];
        $small_image_size = $this->getSmallImageSize();
        $smallW = $small_image_size[0];
        $smallH = $small_image_size[1];
        $small_image = $this->getResizeImage();
        for ($i=0; $i<$indW; $i++) {
            $line = round($smallW-3-$indW+$i);
            for ($j=0; $j<$indH; $j++) {
                $column = round($smallH-3-$indH+$j);
                $colors[] = (int)round(array_sum(imagecolorsforindex($small_image,imagecolorat($small_image,$line,$column)))/3);
            }
        }

        $averageColor = round(array_sum($colors)/count($colors));
        if ($averageColor>=127) {
            $bgType='white';
        } else {
            $bgType='black';
        }
        
        $indImage = $this->getImageResource($this->indicatorImages[$bgType]);
//        self::debug($indImage,'gif');
        imagecopy($small_image,$indImage,$smallW-3-$indW,$smallH-3-$indH,0,0,$indW,$indH);
        
        return $small_image;
        
    }
    
    /**
     * Saving to file resized image
     *
     * @return bool
     */
    public function saveResizedImage () {
        
       $small_image = $this->addIndicator();
//       self::debug($small_image,'jpeg');
       if ($this->img_extension=='jpeg') {
           imagejpeg($small_image,$this->save_path,80);
           return true; 
       } elseif ($this->img_extension=='gif') {
           imagegif($small_image,$this->save_path);
           return true;
       }
       return false;
        
    }
    
    private static function debug ($image_res,$type) {
        
        if ($type=='jpeg') {
           header("Content-type: image/jpeg");
           imagejpeg($image_res);
           return true; 
        } elseif ($type=='gif') {
           header("Content-type: image/gif");
           imagegif($image_res);
           return true;
        }
        die();        
        
    }
    
}
//testing....
/*try {
    $thumbs = new Thumbs('./white.jpg','./white.jpg');
    $thumbs->setCoef(5);
    $thumbs->saveResizedImage();
    echo 'white: <img src="./white.jpg" />';
    $thumbs = new Thumbs('./black.jpg','./black_small.jpg');
    $thumbs->setCoef(5);
    $thumbs->saveResizedImage();
    echo 'black: <img src="./black_small.jpg" />';
} catch (Exception $e) {
    echo $e->getMessage()." line".$e->getLine(); 
}*/

?>
