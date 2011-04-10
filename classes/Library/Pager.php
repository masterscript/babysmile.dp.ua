<?php

require_once ('Pager/Pager.php');

class _Pager extends Pager {
		
	/**
	 * @see Pager::factory()
	 *
	 * @param array $options
	 * @return object
	 */
	public function factory($options) {
		
		$url_vars = array();
		$options['urlVar'] = 'page';
		$options['append'] = false;
		parse_str($_SERVER['QUERY_STRING'],$url_vars);
		unset($url_vars[$options['urlVar']]);
		$query_string = array();
		foreach ($url_vars as $key=>$value) {
			$query_string[] = "$key=$value";
		}
		if (!empty($query_string)) {
			$query_string = implode('&',$query_string).'&';
		} else {
			$query_string = '';
		}
		$options['fileName'] = $options['fileName'].'?'.$query_string.'page=%d';
		return parent::factory($options);
		
	}

	
}

?>
