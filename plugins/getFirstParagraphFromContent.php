<?php
function getFirstParagraphFromContent($obj)
{

		include_once SYSTEM_PATH.'/plugins/getContent.php';

		$content=getContent($obj);
		$a_str=split ("</p>",$content,2);

		if (sizeof($a_str) <> 2 ) {
			$a_str[0]=$content;
			$a_str[1]='';

		}

		return $a_str;
}
?>