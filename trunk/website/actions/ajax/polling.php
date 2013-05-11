<?php
require_once __DIR__.'/../../inc/functions.php';
require_once __DIR__.'/../../inc/functions.ajax.php';

CheckSupportedTypes('info');


if ($request_type == 'info') {
	$res = array();
	$res['time'] = time();
	$res['notifications'] = $_loggedin ? (int)GetNotification() : 0;
	
	$notifications = GetNotification();
	if ($notifications > 0)
		$title = '('.$notifications.') '.$title;

	if ($_loggedin)
		$__database->query("UPDATE accounts SET last_login = NOW(), last_ip = '".$_SERVER['REMOTE_ADDR']."' WHERE id = ".$_loginaccount->GetID());
	
	JSONAnswer($res);
}
?>