<?php

/**
 * @param current_page $page
 */
function ajaxUserAuth($page)
{
	$response = array();
	if (isset($_POST['login']) && isset($_POST['pass'])) {
		$user = user::init($_POST['login'], $_POST['pass']);
		if (user::getUserGroup()) {
			session_name('SID');
			$_SESSION['user'] = $user;
		}
		if (user::getCurrentUser()->getState() == 'login_error') {
			$response['error'] = 'Неправильные имя пользователя или пароль';
		} else {
			$response['error'] = false;
			$response['returnUrl'] = $page->getURL();
		}
	}
	echo json_encode($response);
}