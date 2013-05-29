<?php
require_once __DIR__.'/../../inc/functions.ajax.php';
require_once __DIR__.'/../../inc/functions.loginaccount.php';

CheckSupportedTypes('responses', 'list', 'blog', 'post', 'delete');

require_once __DIR__.'/../../inc/classes/database.php';
require_once __DIR__.'/../../inc/classes/statusses.php';

if ($request_type == 'responses') {
	RetrieveInputGET('statusid');
	
	$statuses = new Statusses();
	$statuses->Load("s.reply_to = ".intval($P['statusid']), '10');
	
	// Buffer all results
	ob_start();
	foreach ($statuses->data as $status)
		$status->PrintAsHTML();
	
	$data = ob_get_clean();
	if ($data === false) JSONDie('No data returned', 204);
	JSONAnswer(array('result' => $data));
}

elseif ($request_type == 'blog') {
	
	$statuses = new Statusses();
	$statuses->Load("s.blog = 1");
	
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
	
	$statuses = new Statusses();
	$statuses->Load(
	($P['lastpost'] == -1 ? '' : (" s.timestamp ".($P['mode'] == 'back' ? '<' : '>')." FROM_UNIXTIME(".$P['lastpost']).") AND")."
	(
		s.override = 1 AND s.blog = 0 OR 
		s.account_id = ".$_loginaccount->GetID()." AND s.blog = 0 OR 
		FriendStatus(account_id, ".$_loginaccount->GetID().") = 'FRIENDS' AND s.blog = 0
	)", '15');
	
	$lastid = -1;
	$firstid = -1;
	
	// Buffer all results
	ob_start();
	foreach ($statuses->data as $status) {
		if ($lastid == -1)
			$lastid = $__server_time - $status->seconds_since; //$status->id;
		$firstid = $__server_time - $status->seconds_since; //$status->id;
		$status->PrintAsHTML('');
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
			? ''
			: ' AND account_id = '.$_loginaccount->GetId()
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