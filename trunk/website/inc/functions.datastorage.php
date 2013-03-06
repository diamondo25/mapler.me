<?php


// Check for APC
$apcinstalled = function_exists("apc_add") == 1;



function GetMapleStoryString($type, $id, $key) {
	global $__database, $apcinstalled;

	if (strlen($key) > 5) {
		// Yea...
		$key = substr($key, 0, 5);
	}

	if ($apcinstalled && !apc_exists("data_cache")) {
		apc_add("data_cache", array());
	}

	$temp = NULL;
	if ($apcinstalled) {
		$temp = apc_fetch("data_cache");
		if ($temp == NULL) {
			$temp = array();
		}
		if (array_key_exists($type.'|'.$id.'|'.$key, $temp)) {
			return $temp[$type.'|'.$id.'|'.$key];
		}
	}
	else {
		$temp = array();
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
		$tmp = $row[0];

		if ($apcinstalled) {
			$temp[$type.'|'.$id.'|'.$key] = $tmp;
			apc_store("data_cache", $temp);
		}

		$q->free();
		return $tmp;
	}
	$q->free();

	if ($apcinstalled) {
		$temp[$type.'|'.$id.'|'.$key] = NULL; // Ai
		apc_store("data_cache", $temp);
	}
	return NULL;
}


function GetItemDefaultStats($id) {
	global $__database, $apcinstalled;

	if ($apcinstalled && !apc_exists("data_iteminfo_cache")) {
		apc_add("data_iteminfo_cache", array());
	}

	$temp = NULL;
	if ($apcinstalled) {
		$temp = apc_fetch("data_iteminfo_cache");
		if ($temp == NULL) {
			$temp = array();
		}
		if (array_key_exists($id, $temp)) {
			return $temp[$id];
		}
	}
	else {
		$temp = array();
	}

	$q = $__database->query("SELECT * FROM `phpVana_iteminfo` WHERE `itemid` = ".$id);
	if ($q->num_rows >= 1) {
		$row = $q->fetch_array();
		$tmp = $row;

		if ($apcinstalled) {
			$temp[$id] = $tmp;
			apc_store("data_iteminfo_cache", $temp);
		}

		$q->free();
		return $tmp;
	}
	$q->free();

	if ($apcinstalled) {
		$temp[$id] = NULL;
		apc_store("data_iteminfo_cache", $temp);
	}
	return NULL;
}


function GetPotentialInfo($id) {
	global $__database, $apcinstalled;

	if ($apcinstalled && !apc_exists("data_itemoptions_cache")) {
		apc_add("data_itemoptions_cache", array());
	}

	$temp = NULL;
	if ($apcinstalled) {
		$temp = apc_fetch("data_itemoptions_cache");
		if ($temp == NULL) {
			$temp = array();
		}
		if (array_key_exists($id, $temp)) {
			return $temp[$id];
		}
	}
	else {
		$temp = array();
	}

	$data = array();

	$data['name'] = GetMapleStoryString('item_option', $id, 'desc');


	$q = $__database->query("SELECT level, options FROM `phpVana_itemoptions_levels` WHERE `id` = ".$id);
	while ($row = $q->fetch_array()) {
		$data['levels'][$row[0]] = Explode2(';', '=', $row[1]);
	}

	if ($apcinstalled) {
		$temp[$id] = $data;
		apc_store("data_itemoptions_cache", $temp);
	}

	return $data;
}

// Only for X Y and some special stuff!!!
function GetItemWZInfo($itemid) {
	global $__database, $apcinstalled;

	if ($apcinstalled && !apc_exists("data_characterwz_cache")) {
		apc_add("data_characterwz_cache", array());
	}

	$temp = NULL;
	if ($apcinstalled) {
		$temp = apc_fetch("data_characterwz_cache");
		if ($temp == NULL) {
			$temp = array();
		}
		if (array_key_exists($itemid, $temp)) {
			return $temp[$itemid];
		}
	}
	else {
		$temp = array();
	}



	$query = $__database->query("
SELECT
	*
FROM
	`phpVana_characterwz`
WHERE
	`itemid` = ".$itemid);
	$item_info = array();
	while ($data = $query->fetch_assoc()) {
		$item_info[$data['key']] = $data['value'];
	}
	$item_info['ITEMID'] = $itemid;
	$query->free();


	if ($apcinstalled) {
		$temp[$itemid] = $item_info;
		apc_store("data_characterwz_cache", $temp);
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