<?php

function cartRecalc()
{    
	if (isset($_POST['count'])) {
		@session_start();
		$counts = (array) $_POST['count'];
		foreach ($counts as $key=>$value) {
			$key = (int) $key;
			$value = (int) $value;
			user::getCurrentUser()->updateCartCount($key, $value);
		}
	}
}