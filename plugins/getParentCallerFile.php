<?php
function getParentCallerFile($obj)
{
	if ($obj->issetParam('section')&&$obj->issetParam('item'))
	{
		if ($obj->issetConfigParam($obj->getParam('section'),$obj->getParam('item')))
		{
			$params=$obj->getConfigParam($obj->getParam('section'),$obj->getParam('item'));
			if (isset($params['file'])) return $params['file'];
		}
	}
	else throw new Exception('Need \'section\' and \'item\' parameters for call_parent mode in config file: '.$obj->getTemplate());
}
?>