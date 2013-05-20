<?php
require_once __DIR__.'/../../inc/functions.php';
require_once __DIR__.'/../../inc/functions.ajax.php';

CheckSupportedTypes('info');


if ($request_type == 'info') {
	$res = array();
	$res['time'] = time();
	$res['notifications'] = $_loggedin ? (int)GetNotification() : 0;
	
	$notifications = GetNotification();

	if ($_loggedin)
		$__database->query("UPDATE accounts SET last_login = NOW(), last_ip = '".$_SERVER['REMOTE_ADDR']."' WHERE id = ".$_loginaccount->GetID());
	
	$status_info = array();
	if (isset($_POST['shown-statuses'])) {
		// Check status info

		foreach ($_POST['shown-statuses'] as $oriid) {
			$id = intval($oriid);
			if ($id == 0) {
				$status_info['deleted'][] = $oriid;
				continue;
			}
			$q = $__database->query("
SELECT
	COUNT(*) AS `reply_count`
FROM
	social_statuses
WHERE
	reply_to = ".$id);
			if ($q->num_rows == 0) {
				$status_info['deleted'][] = $oriid;
			}
			else {
				$row = $q->fetch_row();
				$status_info['reply_count'][$oriid] = $row[0];
			}
			$q->free();
		}
	}
	
	$res['status_info'] = $status_info;
	
	JSONAnswer($res);
}
?>