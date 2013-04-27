<?php
require_once __DIR__.'/../../inc/functions.ajax.php';
require_once __DIR__.'/../../inc/functions.loginaccount.php';

CheckSupportedTypes('responses', 'list', 'blog');

require_once __DIR__.'/../../inc/database.php';
require_once __DIR__.'/../../inc/classes/statusses.php';

if ($request_type == 'responses') {
	RetrieveInput('statusid');
	
	$q = $__database->query("
SELECT
	*,
	TIMESTAMPDIFF(SECOND, timestamp, NOW()) AS `secs_since`
FROM
	social_statuses
WHERE
	reply_to = ".intval($P['statusid'])."

LIMIT 10");
	
	$statuses = new Statusses();
	$statuses->FeedData($q);
	$q->free();
	
	// Buffer all results
	ob_start();
	foreach ($statuses->data as $status)
		$status->PrintAsHTML();
	
	$data = ob_get_clean();
	
	JSONAnswer(array('result' => $data));
}

elseif ($request_type == 'blog') {
	
	$q = $__database->query("
SELECT
	social_statuses.*,
	TIMESTAMPDIFF(SECOND, timestamp, NOW()) AS `secs_since`
FROM
	social_statuses
WHERE
	blog = '1'
ORDER BY
	id DESC
");
	
	$statuses = new Statusses();
	$statuses->FeedData($q);
	$q->free();
	
	// Buffer all results
	ob_start();
	foreach ($statuses->data as $status) {
		$status->PrintAsHTML('');
	}
	
	$data = ob_get_clean();
	
	JSONAnswer(array('result' => $data, 'amount' => count($statuses->data)));
}

elseif ($request_type == 'list') {
	// Retrieves at max 10 statusses
	if (!$_loggedin) JSONDie('Not loggedin');
	
	RetrieveInput('lastpost', 'mode');
	
	$P['lastpost'] = intval($P['lastpost']);
	
	$q = $__database->query("
SELECT
	social_statuses.*,
	accounts.username,
	TIMESTAMPDIFF(SECOND, timestamp, NOW()) AS `secs_since`
FROM
	social_statuses
LEFT JOIN
	accounts
	ON
		social_statuses.account_id = accounts.id
WHERE
".($P['lastpost'] == -1 ? '' : (" social_statuses.id ".($P['mode'] == 'back' ? '<' : '>')." ".$P['lastpost'])." AND")."
	(
		override = 1 AND blog = 0 OR 
		account_id = ".$_loginaccount->GetID()." AND blog = 0 OR 
		FriendStatus(account_id, ".$_loginaccount->GetID().") = 'FRIENDS' AND blog = 0
	)
ORDER BY
	id DESC
LIMIT 15
");
	
	$statuses = new Statusses();
	$statuses->FeedData($q);
	$q->free();
	$lastid = -1;
	$firstid = -1;
	
	// Buffer all results
	ob_start();
	foreach ($statuses->data as $status) {
		if ($lastid == -1)
			$lastid = $status->id;
		$firstid = $status->id;
		$status->PrintAsHTML(' span12');
	}
	$data = ob_get_clean();
	
	JSONAnswer(array('result' => $data, 'lastid' => $lastid, 'firstid' => $firstid, 'amount' => count($statuses->data)));
}

?>