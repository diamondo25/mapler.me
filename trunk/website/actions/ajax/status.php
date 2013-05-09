<?php
require_once __DIR__.'/../../inc/functions.ajax.php';
require_once __DIR__.'/../../inc/functions.loginaccount.php';

CheckSupportedTypes('responses', 'list', 'blog', 'post', 'delete');

require_once __DIR__.'/../../inc/database.php';
require_once __DIR__.'/../../inc/classes/statusses.php';

if ($request_type == 'responses') {
	RetrieveInputGET('statusid');
	
	$q = $__database->query("
SELECT
	*,
	TIMESTAMPDIFF(SECOND, timestamp, NOW()) AS `secs_since`
FROM
	social_statuses
WHERE
	reply_to = ".intval($P['statusid'])."
LIMIT 10
");
	
	$statuses = new Statusses();
	$statuses->FeedData($q);
	$q->free();
	
	// Buffer all results
	ob_start();
	foreach ($statuses->data as $status)
		$status->PrintAsHTML();
	
	$data = ob_get_clean();
	if ($data === false) JSONDie('No data returned', 204);
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
	blog = 1
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
	if ($data === false) JSONDie('No data returned', 204);
	JSONAnswer(array('result' => $data, 'amount' => count($statuses->data)));
}

elseif ($request_type == 'list') {
	// Either requires the SESSION to be loggedin OR gives a correct api key (will be worked on).
	if (!$_loggedin) JSONDie('Not loggedin', 401);

	RetrieveInputGET('lastpost', 'mode');
	
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
		blog = 0 OR 
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
	if ($data === false) JSONDie('No data returned', 204);

	JSONAnswer(array('result' => $data, 'lastid' => $lastid, 'firstid' => $firstid, 'amount' => count($statuses->data)));
}

elseif ($request_type == 'delete') {
	RetrieveInputGET('id');
	// Removing status
	$id = intval($P['id']);

	$__database->query("DELETE FROM social_statuses WHERE id = ".$id.
		(
			$_loginaccount->IsRankOrHigher(RANK_MODERATOR) 
			? ' AND account_id = '.$_loginaccount->GetId()
			: ''
		)
	);

	if ($__database->affected_rows == 1) {
		JSONAnswer(array('result' => 'The status was successfully deleted.'));
	}
	else {
		JSONDie('Unable to delete the status.');
	}
}

elseif ($request_type == 'post') {
	if (!$_loggedin) JSONDie('Not loggedin', 401);

	RetrieveInputPOST('content', 'reply-to');

	$content = nl2br(htmlentities(strip_tags(trim($P['content'])), ENT_COMPAT, 'UTF-8'));
	if ($content == '')
		JSONDie('No status contents.', 400);

	$reply_to = intval($P['reply-to']);

	// Check for duplicate
	$q = $__database->query("
SELECT
	1
FROM
	social_statuses
WHERE
	account_id = ".$_loginaccount->GetId()."
	AND
	content = '".$__database->real_escape_string($content)."'
	AND
	DATE_ADD(`timestamp`, INTERVAL 24 HOUR) >= NOW()
");
	if ($q->num_rows != 0) {
		$q->free();
		JSONDie('Duplicate status.', 400);
	}
	$q->free();

	if ($reply_to != -1) {
		// Check if status exists...
		$q = $__database->query("
SELECT
	1
FROM
	social_statuses
WHERE
	id = ".$reply_to);
		if ($q->num_rows == 0) {
			// No status found!
			JSONDie('Reply-to status not found.', 400);
		}
	}

	$blog = $_loginaccount->IsRankOrHigher(RANK_MODERATOR) && isset($_POST['blog']) ? 1 : 0;

	$char_config = $_loginaccount->GetConfigurationOption('character_config', array('characters' => array(), 'main_character' => null));
	$has_characters = !empty($char_config['main_character']);

	// set internally
	$nicknm = $_loginaccount->GetNickname();
	$chr = $has_characters ? $char_config['main_character'] : '';

	$_loginaccount->SetConfigurationOption('last_status_sent', date("Y-m-d H:i:s"));

	$__database->query("
	INSERT INTO
		social_statuses
	VALUES
		(
			NULL,
			".$_loginaccount->GetId().",
			'".$__database->real_escape_string($nicknm)."',
			'".$__database->real_escape_string($chr)."',
			'".$__database->real_escape_string($content)."',
			".$blog.",
			NOW(),
			0,
			".($reply_to == -1 ? 'NULL' : $reply_to)."
		)
	");

	if ($__database->affected_rows == 1) {
		JSONAnswer(array('result' => 'Status successfully posted.'), 200);
	}
	else {
		JSONDie('Unable to post status due to internal error.', 400);
	}
}
?>