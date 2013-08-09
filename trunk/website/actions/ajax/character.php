<?php
require_once __DIR__.'/../../inc/functions.ajax.php';
require_once __DIR__.'/../../inc/functions.loginaccount.php';
require_once __DIR__.'/../../inc/classes/database.php';

CheckSupportedTypes('visibility', 'face', 'statistics');

function IsOwnCharacter($charname) {
	global $__database;
	global $_loginaccount;
	$q = $__database->query("
SELECT
	c.internal_id
FROM
	characters c
LEFT JOIN
	users u
	ON
		u.id = c.userid
WHERE
	c.name = '".$__database->real_escape_string($charname)."'
	AND
	u.account_id = ".$_loginaccount->GetID());
	
	if ($q->num_rows > 0) {
		$row = $q->fetch_row();
		$q->free();
		return $row[0];
	}
	return false;
}


if ($request_type == 'visibility') {
	if (!$_loggedin) JSONDie('Not loggedin');
	RetrieveInputGET('name', 'what', 'shown');
	
	$internalid = IsOwnCharacter($P['name']);
	if ($internalid === false) JSONDie('No.');
	
	$q = $__database->query("
INSERT INTO
	character_options
VALUES
	(
		".$internalid.",
		'display_".$__database->real_escape_string($P['what'])."',
		".($P['shown'] == 'false' ? 0 : 1)."
	)
ON DUPLICATE KEY UPDATE
	`option_value` = VALUES(`option_value`)");
	
	if ($__database->affected_rows != 0) {
		JSONAnswer(array('result' => 'okay'));
	}
	else {
		JSONAnswer(array('result' => 'failure'));
	}
}

elseif ($request_type == 'statistics') {
	if (!$_loggedin) JSONDie('Not loggedin');
	RetrieveInputGET('name','code');
	$code = $P['code'];
	if ($code !== 'adminbypass') {
	$internalid = IsOwnCharacter($P['name']);
	if ($internalid === false) JSONDie('You must own this character to request it\'s API', 400);
	}
	
	$q = $__database->query("
SELECT 
	chr.name,
	w.world_name,
	channel_id AS channel,
	level,
	job,
	fame,
	chr.str,
	chr.dex,
	chr.int,
	chr.luk,
	chr.exp,
	map,
	chr.honourlevel AS honorlevel,
	chr.honourexp AS honorexp,
	mesos,
	last_update,
	TIMESTAMPDIFF(SECOND, last_update, NOW()) AS `seconds_since`
FROM
	`characters` chr
LEFT JOIN 
	world_data w
	ON
		w.world_id = chr.world_id
WHERE 
	chr.name = '".$__database->real_escape_string($P['name'])."'");
	
if ($q->num_rows == 0) {
    JSONDie('Character not found', 404);
}

	$character = array();
	
	
	while ($row = $q->fetch_assoc()) {
	   $character = $row;
    }
	JSONAnswer(array('result' => $character));
}

?>