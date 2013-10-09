<?php
header('Access-Control-Allow-Origin: *');

require_once __DIR__.'/../../inc/functions.ajax.php';
require_once __DIR__.'/../../inc/functions.loginaccount.php';
require_once __DIR__.'/../../inc/classes/database.php';
require_once __DIR__.'/../../inc/exp_table.php';
require_once __DIR__.'/../../inc/job_list.php';

CheckSupportedTypes('visibility', 'statistics');
$_char_db = ConnectCharacterDatabase(CURRENT_LOCALE);

function IsOwnCharacter($charname) {
	global $_loginaccount, $_char_db;
	
	$q = $_char_db->query("
SELECT
	c.internal_id
FROM
	characters c
LEFT JOIN
	users u
	ON
		u.id = c.userid
WHERE
	c.name = '".$_char_db->real_escape_string($charname)."'
	AND
	u.account_id = ".$_char_db->GetID());
	
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
	
	$q = $_char_db->query("
INSERT INTO
	character_options
VALUES
	(
		".$internalid.",
		'display_".$_char_db->real_escape_string($P['what'])."',
		".($P['shown'] == 'false' ? 0 : 1)."
	)
ON DUPLICATE KEY UPDATE
	`option_value` = VALUES(`option_value`)");
	
	if ($_char_db->affected_rows != 0) {
		JSONAnswer(array('result' => 'okay'));
	}
	else {
		JSONAnswer(array('result' => 'failure'));
	}
}

elseif ($request_type == 'statistics') {
	RetrieveInputGET('name');
	
	$q = $_char_db->query("
SELECT 
	chr.name,
	w.world_name,
	chr.channel_id AS channel,
	chr.level,
	chr.job,
	chr.fame,
	chr.str,
	chr.dex,
	chr.int,
	chr.luk,
	chr.exp,
	chr.map,
	chr.honourlevel AS honorlevel,
	chr.honourexp AS honorexp,
	mesos,
	TIMESTAMPDIFF(SECOND, last_update, NOW()) AS `seconds_since`
FROM
	`characters` chr
LEFT JOIN 
	world_data w
	ON
		w.world_id = chr.world_id
WHERE 
	chr.name = '".$_char_db->real_escape_string($P['name'])."'");
	
	if ($q->num_rows == 0) {
		JSONDie('Character not found', 404);
	}

	$row = $q->fetch_assoc();
	
	$percenta = GetExpPercentage($row['level'], $row['exp']);
	$percentb = round($percenta * 100) / 100;
	
	$job = GetJobname($row['job']);
	$map = GetMapname($row['map'], CURRENT_LOCALE);
	
	$extra = array('percentage' => "$percentb", 'job_name' => "$job", 'map_name' => "$map");
	
	$answer = $row + $extra;
	
	$q->free();
	
	JSONAnswer(array('result' => $answer));
}

?>