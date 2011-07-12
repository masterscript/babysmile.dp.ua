<?php

function ajaxCartDelete()
{        
    if (isset($_POST['key'])) {
		$answer = array(
			'result' => user::getCurrentUser()->deleteFromCart((int) $_POST['key'])
		);
	} else {
		$answer = array('result' => false);
	}
	echo json_encode($answer);
}