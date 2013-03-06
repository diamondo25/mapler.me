<?php


// Check for APC
$apcinstalled = function_exists("apc_add") == 1;



function GetMapleStoryString($type, $id, $key) {
	global $__database, $apcinstalled;

	if (strlen($key) > 5) {
		// Yea...
		$key = substr($key, 0, 5);
	}

	$key_name = "data_cache_".$type.'|'.$id.'|'.$key;
	
	if ($apcinstalled && apc_exists($key_name)) {
		return apc_fetch($key_name);
	}

	$q = $__database->query("
SELECT
	`value`
FROM
	`strings`
WHERE
	`objecttype` = '".$__database->real_escape_string($type)."'
		AND
	`objectid` = ".intval($id)."
		AND
	`key` = '".$__database->real_escape_string($key)."'
");
	if ($q->num_rows >= 1) {
		$row = $q->fetch_array();
		$value = $row[0];

		if ($apcinstalled) {
			apc_add($key_name, $value);
		}

		$q->free();
		return $value;
	}
	$q->free();

	return NULL;
}

function GetItemDefaultStats($id) {
	global $__database, $apcinstalled;

	$key_name = "data_iteminfo_cache_".$id;
	
	if ($apcinstalled && apc_exists($key_name)) {
		return apc_fetch($key_name);
	}
	
	$q = $__database->query("SELECT * FROM `phpVana_iteminfo` WHERE `itemid` = ".$id);
	if ($q->num_rows >= 1) {
		$row = $q->fetch_array();

		if ($apcinstalled) {
			apc_add($key_name, $row);
		}

		$q->free();
		return $row;
	}
	$q->free();

	return NULL;
}


function GetPotentialInfo($id) {
	global $__database, $apcinstalled;
	
	$key_name = "data_itemoptions_cache".$id;
	
	if ($apcinstalled && apc_exists($key_name)) {
		return apc_fetch($key_name);
	}

	$data = array();
	$data['name'] = GetMapleStoryString('item_option', $id, 'desc');

	$q = $__database->query("SELECT level, options FROM `phpVana_itemoptions_levels` WHERE `id` = ".$id);
	while ($row = $q->fetch_row()) {
		$data['levels'][$row[0]] = Explode2(';', '=', $row[1]);
	}
	
	if ($apcinstalled) {
		apc_add($key_name, $data);
	}

	return $data;
}

// Only for X Y and some special stuff!!!
function GetItemWZInfo($itemid) {
	global $__database, $apcinstalled;
	
	$key_name = "data_characterwz_cache".$itemid;
	
	if ($apcinstalled && apc_exists($key_name)) {
		return apc_fetch($key_name);
	}
	
	$q = $__database->query("
SELECT
	`key`,
	`value`
FROM
	`phpVana_characterwz`
WHERE
	`itemid` = ".$itemid
);

	$item_info = array();
	while ($data = $q->fetch_row()) {
		$item_info[$data[0]] = $data[1];
	}
	$item_info['ITEMID'] = $itemid;
	

	$q->free();
	
	if ($apcinstalled) {
		apc_add($key_name, $item_info);
	}
	
	return $item_info;
}


function GetItemPotentialBuffs($internal_id) {
	global $__database;

	$q = $__database->query("
SELECT
	i.itemid,
	i.potential1,
	i.potential2,
	i.potential3,
	i.potential4,
	i.potential5,
	ii.reqlevel
FROM
	`items` i
LEFT JOIN
	`phpVana_iteminfo` ii
		ON
	ii.itemid = i.itemid
WHERE
	i.`character_id` = ".intval($internal_id)."
		AND
	slot < 0
");
	$temp = array();
	while ($row = $q->fetch_assoc()) {
		$level = round($row['reqlevel'] / 10);
		if ($level == 0) $level = 1;
		$temp[$row['itemid']] = array();
		for ($i = 1; $i <= 5; $i++) {
			if ($row['potential'.$i] == 0) continue;
			$potentialinfo = GetPotentialInfo($row['potential'.$i]);

			$temp[$row['itemid']] = array_merge($temp[$row['itemid']], $potentialinfo['levels'][$level]);
		}
	}
	$q->free();
	return $temp;
}