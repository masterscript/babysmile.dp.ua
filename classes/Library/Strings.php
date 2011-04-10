<?php

/**
 * Библиотека работы со строками
 *
 */
class _Strings {
    
	/**
     * Возвращает последнюю часть в uri (без "/")
     *
     * @param string $uri
     * @param integer $part_number номер возращаемой части в uri
     * @return string
     * @todo реализовать поддержку параметра $part_number
     */
    static function uri_part ($uri,$part_number=NULL) {
    	
        if (!$part_number) {
    	    return substr(strrchr($uri,'/'),1);
        }
        return false;
    	
    }
    
	/**
     * Возвращает последнюю родительский uri
     *
     * @param string $uri
     * @return string
     */
    static function uri_parent ($uri) {
    	
   	    return substr($uri,0,strlen($uri)-strlen(strrchr($uri,'/'))); 
    	
    }

	static function translite($str){
		// (c)Imbolc http://php.imbolc.name
	
		static $tbl= array(
			'а'=>'a', 'б'=>'b', 'в'=>'v', 'г'=>'g', 'д'=>'d', 'е'=>'e', 'ж'=>'g', 'з'=>'z',
			'и'=>'i', 'й'=>'y', 'к'=>'k', 'л'=>'l', 'м'=>'m', 'н'=>'n', 'о'=>'o', 'п'=>'p',
			'р'=>'r', 'с'=>'s', 'т'=>'t', 'у'=>'u', 'ф'=>'f', 'ы'=>'i', 'э'=>'e', 'А'=>'A',
			'Б'=>'B', 'В'=>'V', 'Г'=>'G', 'Д'=>'D', 'Е'=>'E', 'Ж'=>'G', 'З'=>'Z', 'И'=>'I',
			'Й'=>'Y', 'К'=>'K', 'Л'=>'L', 'М'=>'M', 'Н'=>'N', 'О'=>'O', 'П'=>'P', 'Р'=>'R',
			'С'=>'S', 'Т'=>'T', 'У'=>'U', 'Ф'=>'F', 'Ы'=>'I', 'Э'=>'E', 'ё'=>"yo", 'х'=>"h",
			'ц'=>"ts", 'ч'=>"ch", 'ш'=>"sh", 'щ'=>"shch", 'ъ'=>"", 'ь'=>"", 'ю'=>"yu", 'я'=>"ya",
			'Ё'=>"YO", 'Х'=>"H", 'Ц'=>"TS", 'Ч'=>"CH", 'Ш'=>"SH", 'Щ'=>"SHCH", 'Ъ'=>"", 'Ь'=>"",
			'Ю'=>"YU", 'Я'=>"YA"
		);
	
	    return strtr($str, $tbl);
	    
	}
    
}
