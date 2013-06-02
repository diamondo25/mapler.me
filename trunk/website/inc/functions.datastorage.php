<?php

require_once __DIR__.'/classes/database.php';
require_once __DIR__.'/classes/TreeNode.php';

// Check for APC
define('APC_INSTALLED', isset($_GET['IGNORE_APC']) ? false : function_exists('apc_add'));



function GetMapleStoryString($type, $id, $key) {
	global $__database;

	if (strlen($key) > 5) {
		// Yea...
		$key = substr($key, 0, 5);
	}

	$key_name = 'data_cache_'.$id;
	
	if (APC_INSTALLED && apc_exists($key_name)) {
		$tmp = apc_fetch($key_name);
		
		if (isset($tmp[$type]) && isset($tmp[$type][$key]))
			$value = $tmp[$type][$key];
		else
			$value = NULL;
		return $value;
	}

	$q = $__database->query("
SELECT
	`objecttype`,
	`key`,
	`value`
FROM
	`strings`
WHERE
	`objectid` = ".intval($id));

	if ($q->num_rows >= 1) {
		$buff = array();
		while ($row = $q->fetch_array())
			$buff[$row[0]][$row[1]] = $row[2];

		if (APC_INSTALLED) {
			apc_add($key_name, $buff);
		}
		
		if (isset($buff[$type]) && isset($buff[$type][$key]))
			$value = $buff[$type][$key];
		else
			$value = NULL;

		$q->free();
		return $value;
	}
	$q->free();

	return NULL;
}

function GetItemDefaultStats($id) {
	global $__database;

	$key_name = 'data_iteminfo_cache_'.$id;
	
	if (APC_INSTALLED && apc_exists($key_name)) {
		return apc_fetch($key_name);
	}
	
	$q = $__database->query("SELECT * FROM `phpVana_iteminfo` WHERE `itemid` = ".$id);
	if ($q->num_rows >= 1) {
		$row = $q->fetch_array();

		if (APC_INSTALLED) {
			apc_add($key_name, $row);
		}

		$q->free();
		return $row;
	}
	$q->free();

	return NULL;
}

function GetPotentialInfo($id) {
	global $__database;
	
	$key_name = 'data_itemoptions_cache'.$id;
	
	if (APC_INSTALLED && apc_exists($key_name)) {
		return apc_fetch($key_name);
	}

	$data = array();
	$data['name'] = GetMapleStoryString('item_option', $id, 'desc');

	$q = $__database->query("SELECT level, options FROM `phpVana_itemoptions_levels` WHERE `id` = ".$id);
	while ($row = $q->fetch_row()) {
		$data['levels'][$row[0]] = Explode2(';', '=', $row[1]);
	}
	
	if (APC_INSTALLED) {
		apc_add($key_name, $data);
	}

	return $data;
}

function GetNebuliteInfo($itemid) {
	global $__database;
	
	$key_name = 'data_nebulite_cache'.$itemid;
	
	if (APC_INSTALLED && apc_exists($key_name)) {
		return apc_fetch($key_name);
	}
	
	$itemid += 3060000;

	$data = array();

	$q = $__database->query("SELECT description, options FROM `phpVana_socket_info` WHERE `itemid` = ".$itemid);
	$row = $q->fetch_row();
	$data['description'] = $row[0];
	$data['info'] = Explode2(';', '=', $row[1]);
	
	if (APC_INSTALLED) {
		apc_add($key_name, $data);
	}

	return $data;
}


// Only for X Y and some special stuff!!!
function GetItemWZInfo($itemid) {
	global $__database;
	$key_name = 'data_characterwz_cache'.$itemid;
	
	if (APC_INSTALLED && apc_exists($key_name)) {
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
	if ($q->num_rows == 0)
		return null;

	$item_info = new TreeNode('main');
	$temp = false;
	//$item_info['grouped'] = array();
	while ($data = $q->fetch_row()) {
		if ($data[0] == 'info_vslot') {
			preg_match_all('/../i', $data[1], $matches);
			$data[1] = $matches[0];
		}
		//$item_info[$data[0]] = $data[1];
		$split = explode('_', $data[0]);
		$tmp2 = &$item_info;
		foreach ($split as $idx => $name) {
			if (!isset($tmp2[$name])) {
				if (count($split) - 1 == $idx) {
					$tmp2[$name] = $data[1];
					//$tmp2 = $tmp2[$name];
					break;
				}
				else {
					$tmp2[$name] = new TreeNode($name, $tmp2);
				}
			}
			$tmp2 = &$tmp2[$name];
		}
	}
	$item_info['ITEMID'] = $itemid;

	$q->free();
	
	if (APC_INSTALLED) {
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
	i.potential6,
	i.nebulite1,
	i.nebulite2,
	i.nebulite3,
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
		$obj = array();
		for ($i = 1; $i <= 6; $i++) {
			if ($row['potential'.$i] == 0) continue;
			$potentialinfo = GetPotentialInfo($row['potential'.$i]);

			$obj[] = $potentialinfo['levels'][$level];
		}
		for ($i = 1; $i <= 3; $i++) {
			if ($row['nebulite'.$i] == -1) continue;
			$nebinfo = GetNebuliteInfo($row['nebulite'.$i]);

			$obj[] = $nebinfo['info'];
		}
		
		if (count($obj) > 0)
			$temp[$row['itemid']] = $obj;
	}
	$q->free();
	return $temp;
}


function GetCharacterOption($id, $key, $default = null) {
	global $__database;
	$q = $__database->query("
SELECT
	option_value
FROM
	character_options
WHERE
	character_id = ".$id."
AND
	option_key = '".$__database->real_escape_string($key)."'");
	
	if ($q->num_rows == 0) {
		$q->free();
		return $default;
	}
	$row = $q->fetch_row();
	$q->free();
	return $row[0];
}


function SetCharacterOption($id, $key, $value) {
	global $__database;
	$q = $__database->query("
INSERT INTO
	character_options
VALUES
(
	".$id.",
	'".$__database->real_escape_string($key)."',
	'".$__database->real_escape_string($value)."'
)
ON DUPLICATE KEY UPDATE
	option_value = VALUES(`option_value`)");
}
