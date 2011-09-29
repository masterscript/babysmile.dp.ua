<?php
function getFirstParagraphFromContent($obj)
{

		include_once SYSTEM_PATH.'/plugins/getContent.php';

		$content=getContent($obj);
		$a_str = split ("</p>",$content,2);

		if (sizeof($a_str) <> 2 ) {
			$aSplitedText = array ("firstParagraph" => $content, "otherContent" => '');
			return $aSplitedText;

		} else {
			$aSplitedText = array ("firstParagraph" => $a_str[0], "otherContent" => $a_str[1]);
			return $aSplitedText;
		}


}
?>