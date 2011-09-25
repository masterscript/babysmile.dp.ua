<?php
function getToggleContent($obj)
{
	$content_file=config::getConfigValue('FOLDERS','content').$obj->getId().'.html';
	if (file_exists($content_file)){
		$htm_blk_1='<div style="display: none;" id="effect">';
		$htm_blk_2='</div><a href="#" onclick="return false;" style="float:right" id="desctoggle">Показать/скрыть полное описание</a>';
		$content=file_get_contents ($content_file);
		$a_str=split ("</p>",$content,2);
		$num=sizeof($a_str);
		if ($num == 2) {
		    if ($a_str[1]=='') return $content;
		    return $a_str[0].$htm_blk_1.$a_str[1].$htm_blk_2;
		} else return $content;
	} else return '';
}
?>