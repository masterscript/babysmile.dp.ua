<?php
try {
    ini_set("display_errors","Off");
    $input_name = 'upl_file';
    
    if (!empty($_FILES[$input_name])) {
        $name = '';
        $type = '';
        $size = '';
        $tmp_name = '';
        $error = '';
        extract($_FILES[$input_name]); // name, type, size, tmp_name, error
        
        switch ($error) {
            case UPLOAD_ERR_OK:                
                if ($size>300000) {
                    $error_msg = 'You try to upload too big file';                                 
                } elseif ($type!='application/vnd.ms-excel' and $type!='text/comma-separated-values') {
                    $error_msg = 'File must be a valid excel format';
                } else {
                    $error_msg = '';
                    $f = fopen($tmp_name,'r');                    
                    echo "<table>";                   
                    while (($data = fgetcsv($f,false,';'))!==false) {
                        echo "<tr>";
                        $num = count($data);
                        for ($i=0; $i<$num; $i++) {
                            echo "<td>{$data[$i]}</td>";
                        }
                        echo "</tr>";
                    }
                    echo "</table>";
                    fclose($f);
                    @unlink($tmp_name);
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
    
    echo $error_msg;
    
} catch (Exception $e) {
    echo "{ msg: 'System error: {$e->getMessage()} in {$e->getFile()} at line {$e->getLine()}' }";
}    			
?>