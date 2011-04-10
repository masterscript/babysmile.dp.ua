<?php
try {
	
	$json = array();
	
	$objectDb = Admin_Core::getObjectDatabase();
	$objectImgcontent = new Admin_Plugins_Imgcontent();
    ini_set("display_errors","Off");
    $input_name = 'upl_file';
    
    $content_id = @$_REQUEST['content_id'];
    
    if (!empty($_FILES[$input_name])) {       
        $name = '';
        $type = '';
        $size = '';
        $tmp_name = '';
        $error = '';
        extract($_FILES[$input_name]); // name, type, size, tmp_name, error
        
        $upload_dir = FRONT_SITE_PATH.Admin_Plugins_Imgcontent::$PATH_TO_IMAGES.'/'.$content_id.'/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir);
        }
        $upload_dir .= $name;
        switch ($error) {
            case UPLOAD_ERR_OK:
                if ($size>300000) {
                    $error_msg = 'Вы пытаетесь загрузить слишком большой файл';                                 
                } elseif ($type!='image/jpeg' and $type!='image/gif' and $type!='image/pjpeg') {
                    $error_msg = 'Файл должен быть изображением GIF или JPEG';
                } elseif (file_exists($upload_dir)) {
                    $error_msg = 'Файл с таким именем уже существует. Выберите другое имя';
                } else {
                    // try to add record to table
                    if (!move_uploaded_file($tmp_name, $upload_dir)) {
                        throw new Exception("Файл не был загружен из-за системной ошибки. Не удается выполнить копирование по заданному пути: $upload_dir");
                    }                    
                    
                    if ($_REQUEST['useWatermark']==1) {
	                    // наложение watermark
	                    $watermark = new _Watermark();
	                    $im = $watermark->drawWatermark($upload_dir);
	                    $im->writeImages($upload_dir,true);
                    }
                    // all ok                    
                    $error_msg = 'no error';                
                    list($width,$height) = getimagesize($upload_dir);
                    
                    $big_img = 0;
                    if (isset($_REQUEST['resize'])) {
                        // check for gd extension
                        if (!function_exists('gd_info')) {
                            throw new Exception('Расширение GD не установлено на сервере');
                        }
                        list($bigW,$bigH) = getimagesize($upload_dir);
                        $path = FRONT_SITE_PATH.Admin_Plugins_Imgcontent::$PATH_TO_IMAGES.Admin_Plugins_Imgcontent::$PATH_TO_BIG_IMAGES.$content_id.'/';
                        // создаем директорию для большого изображения
                        if (!file_exists($path)) {
                            mkdir($path);
                        }
                        // устанавливаем права доступа на директорию
                        chmod($path,0775);
                        $file = basename($upload_dir);                        
                        if (!copy($upload_dir,$path.$file)) {
                            throw new Exception("Невозможно скопировать файл. Source: $upload_dir; Destination: $path".Admin_Plugins_Imgcontent::$PATH_TO_BIG_IMAGES."$file");
                        }
                        $thumb = new _Thumbs($upload_dir,$upload_dir);
                        $thumb->setIndicatorImages('black',FRONT_SITE_PATH.'/i/thumbs/plus_b.gif');
                        $thumb->setIndicatorImages('white',FRONT_SITE_PATH.'/i/thumbs/plus.gif');
                        // checking resize params
                        if (isset($_REQUEST['img-width'])) {
                            if ($_REQUEST['img-width']<$bigW) {
                                $thumb->setSmallWidth($_REQUEST['img-width']);
                            } else {
                                throw new Exception('Неверный параметр: ширина');
                            }
                        } elseif (isset($_REQUEST['img-height'])) {
                            if ($_REQUEST['img-height']<$bigH) {
                                $thumb->setSmallHeight($_REQUEST['img-height']);
                            } else {
                                throw new Exception('Неверный параметр: высота');
                            }                            
                        } elseif (isset($_REQUEST['img-percent'])) {
                            if ($_REQUEST['img-percent']<100 && $_REQUEST['img-percent']>1) {
                                $thumb->setCoef(ceil(100/$_REQUEST['img-percent']));
                            } else {
                                throw new Exception('Неверный параметр: процент уменьшения');
                            }
                        } else {
                            throw new Exception('Не переданы параметры для масштабирования изображения');
                        }
                        list($width,$height) = $thumb->getSmallImageSize();
                        $thumb->saveResizedImage();
                        $big_img = 1;
                    }
                    
                    // thumbnail size
                    if ($width>120) {
                        $delta = $width/120;
                        $width = 120;
                        $height = ceil($height/$delta);        
                    }
                    if ($height>120) {
                        $delta = $height/120;
                        $height = 120;
                        $width = ceil($width/$delta);        
                    }
                    
                    // write to database
                    $img_path = Admin_Plugins_Imgcontent::$PATH_TO_IMAGES.'/'.$content_id.'/'.$name;
                    $objectDb->insertRecord(
                    	'content_images',
                    	array('item_id'=>$content_id,'img_path'=>$img_path)
                    );
                    $id = $objectDb->getLastId('content_images');
                }
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $error_msg = 'UPLOAD_ERR_CANT_WRITE';
                break;
            case UPLOAD_ERR_FORM_SIZE:
            case UPLOAD_ERR_INI_SIZE:
                $error_msg = 'UPLOAD_ERR_SIZE';
                break;
            case UPLOAD_ERR_PARTIAL:
                $error_msg = 'UPLOAD_ERR_PARTIAL';
                break;
            case UPLOAD_ERR_NO_FILE:
                $error_msg = 'UPLOAD_ERR_NO_FILE';
                break;
        }
        
    }			
    
    $json = array('msg'=>$error_msg,'img_name'=>$img_path,'id'=>$id,'width'=>$width,'height'=>$height,'big_img'=>$big_img);
    
} catch (Exception $e) {
	
    if (file_exists($upload_dir)) unlink($upload_dir);
    $json['msg'] = $e->getMessage();
    
}

echo json_encode($json);
