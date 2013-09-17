<?php

require_once __DIR__.'/classes/database.php';
require_once __DIR__.'/classes/TreeNode.php';
require_once __DIR__.'/domains.php';

// Check for APC
define('APC_INSTALLED', isset($_GET['IGNORE_APC']) ? false : function_exists('apc_add'));

gc_disable(); // Disable Garbage Collection (T_T). Prevents Memfaulting apache...

function SetCachedObject($key, $value, $locale) {
	global $subdomain;
	$tmp = $key.'_'.$locale;
	if (APC_INSTALLED) apc_add($tmp, $value);
}

function IsCachedObject($key, $locale) {
	global $subdomain;
	$tmp = $key.'_'.$locale;
	return APC_INSTALLED && apc_exists($tmp);
}

function GetCachedObject($key, $locale) {
	global $subdomain;
	$tmp = $key.'_'.$locale;
	if (APC_INSTALLED && apc_exists($tmp))
		return apc_fetch($tmp);
	return null;
}



function GetMapleStoryString($type, $id, $key, $locale) {
	$db = ConnectCharacterDatabase($locale);

	$id = intval($id);
	
	if (strlen($key) > 5) {
		// Yea...
		$key = substr($key, 0, 5);
	}

	$key_name = 'data_cache_'.$id;

	if (IsCachedObject($key_name, $locale)) {
		$tmp = GetCachedObject($key_name, $locale);
		
		if (isset($tmp[$type]) && isset($tmp[$type][$key]))
			$value = $tmp[$type][$key];
		else
			$value = NULL;
		return $value;
	}

	$q = $db->query("
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


		SetCachedObject($key_name, $buff, $locale);
		
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

function GetItemDefaultStats($id, $locale) {
	$db = ConnectCharacterDatabase($locale);

	$key_name = 'data_iteminfo_cache_'.$id;

	if (IsCachedObject($key_name, $locale)) {
		return GetCachedObject($key_name, $locale);
	}

	$q = $db->query("SELECT * FROM `phpVana_iteminfo` WHERE `itemid` = ".intval($id));
	if ($q->num_rows >= 1) {
		$row = $q->fetch_array();

		SetCachedObject($key_name, $row, $locale);

		$q->free();
		return $row;
	}
	$q->free();

	return NULL;
}

function GetPotentialInfo($id, $locale) {
	$db = ConnectCharacterDatabase($locale);
	
	$key_name = 'data_itemoptions_cache'.$id;
	

	if (IsCachedObject($key_name, $locale)) {
		return GetCachedObject($key_name, $locale);
	}

	$data = array();
	$data['name'] = GetMapleStoryString('item_option', $id, 'desc', $locale);

	$q = $db->query("SELECT level, options FROM `phpVana_itemoptions_levels` WHERE `id` = ".intval($id));
	while ($row = $q->fetch_row()) {
		$data['levels'][$row[0]] = Explode2(';', '=', $row[1]);
	}

	SetCachedObject($key_name, $data, $locale);

	return $data;
}

function GetNebuliteInfo($itemid, $locale) {
	$db = ConnectCharacterDatabase($locale);
	
	$key_name = 'data_nebulite_cache'.$itemid;
	

	if (IsCachedObject($key_name, $locale)) {
		return GetCachedObject($key_name, $locale);
	}
	
	$itemid += 3060000;

	$data = array();

	$q = $db->query("SELECT description, options FROM `phpVana_socket_info` WHERE `itemid` = ".intval($itemid));
	$row = $q->fetch_row();
	$data['description'] = $row[0];
	$data['info'] = Explode2(';', '=', $row[1]);
	

	SetCachedObject($key_name, $data, $locale);

	return $data;
}


// Only for X Y and some special stuff!!!
function GetItemWZInfo($itemid, $locale) {
	$db = ConnectCharacterDatabase($locale);
	$key_name = 'data_characterwz_cache'.$itemid;
	

	if (IsCachedObject($key_name, $locale)) {
		return GetCachedObject($key_name, $locale);
	}
	
	$q = $db->query("
SELECT
	`key`,
	`value`
FROM
	`phpVana_characterwz`
WHERE
	`itemid` = ".intval($itemid)
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
		$elements = count($split);
		foreach ($split as $idx => $name) {
			try {
				if (!isset($tmp2[$name])) {
					if ($elements - 1 == $idx) { // Is last element
						if (is_string($data[1]) && strpos($data[1], '{VEC}') !== false) { // Is a vector
							$vec = explode(';', substr($data[1], 5));
							$temp = new TreeNode($name, $tmp2);
							$temp['X'] = $vec[0];
							$temp['Y'] = $vec[1];
							$tmp2[$name] = $temp;
						}
						else {
							$tmp2[$name] = $data[1];
						}
						break;
					}
					else {
						$tmp2[$name] = new TreeNode($name, $tmp2);
					}
				}
				if ($tmp2[$name] instanceof TreeNode)
					$tmp2 = &$tmp2[$name];
				else
					$tmp2 = $tmp2[$name];
			}
			catch (Exception $ex) {
				return null;
			}
		}
	}
	$item_info['ITEMID'] = $itemid;

	$q->free();

	SetCachedObject($key_name, $item_info, $locale);
	
	return $item_info;
}


function GetItemPotentialBuffs($internal_id, $locale) {
	$db = ConnectCharacterDatabase($locale);

	$q = $db->query("
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
			$potentialinfo = GetPotentialInfo($row['potential'.$i], $locale);

			$obj[] = $potentialinfo['levels'][$level];
		}
		for ($i = 1; $i <= 3; $i++) {
			if ($row['nebulite'.$i] == -1) continue;
			$nebinfo = GetNebuliteInfo($row['nebulite'.$i], $locale);

			$obj[] = $nebinfo['info'];
		}
		
		if (count($obj) > 0)
			$temp[$row['itemid']] = $obj;
	}
	$q->free();
	return $temp;
}


function GetCharacterOption($id, $key, $locale, $default = null) {
	$db = ConnectCharacterDatabase($locale);

	$q = $db->query("
SELECT
	option_value
FROM
	character_options
WHERE
	character_id = ".intval($id)."
AND
	option_key = '".$db->real_escape_string($key)."'");
	
	if ($q->num_rows == 0) {
		$q->free();
		return $default;
	}
	$row = $q->fetch_row();
	$q->free();
	return $row[0];
}


function SetCharacterOption($id, $key, $locale, $value) {
	$db = ConnectCharacterDatabase($locale);
	
	$q = $db->query("
INSERT INTO
	character_options
VALUES
(
	".intval($id).",
	'".$db->real_escape_string($key)."',
	'".$db->real_escape_string($value)."'
)
ON DUPLICATE KEY UPDATE
	option_value = VALUES(`option_value`)");
}
