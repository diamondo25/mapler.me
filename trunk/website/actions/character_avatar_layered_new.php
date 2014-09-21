<?php
require_once __DIR__.'/../inc/classes/database.php';
require_once __DIR__.'/../inc/functions.php';
require_once __DIR__.'/../inc/functions.datastorage.php';
require_once __DIR__.'/../inc/avatar_faces.php';
require_once __DIR__.'/../inc/zmap.php';
require_once __DIR__.'/caching.php';


function GetID($row) {
	$itemid = $row['itemid'];
	if ($row['display_id'] > 0) {
		$itemid -= $itemid % 10000;
		$itemid += $row['display_id'];
	}
	return $itemid;
}

set_time_limit(1000);

define("DEBUGGING", isset($_GET['debug']));


if (!DEBUGGING) {
	error_reporting(0);
	ini_set('display_errors', 0);
	header('Content-Type: image/png');
	
	$seconds_to_cache = 0;
	$ts = gmdate('D, d M Y H:i:s', time() + $seconds_to_cache) . ' GMT';
	header('Expires: '.$ts);
	header('Pragma: no-cache');
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
	header('Cache-Control: no-store, no-cache, must-revalidate');
	header('Cache-Control: post-check=0, pre-check=0', false);
}
else {
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	
}

$size = 'normal';
if (isset($_GET['size'])) {
	switch ($_GET['size']) {
		case 'original': 
		case 'normal': 
		case 'big': 
		case 'huge': $size = $_GET['size']; break;
	}
}

$charname = isset($_GET['name']) ? $_GET['name'] : 'RoboticOil';

$tmp_ok = true;
$len = strlen($charname);
if ($len < 4 || $len > 12) {
	$tmp_ok = false;
}
else {
	$__char_db = ConnectCharacterDatabase(CURRENT_LOCALE);
	$q = $__char_db->query("SELECT * FROM characters WHERE name = '".$__char_db->real_escape_string($charname)."'");
	if ($q->num_rows == 0) {
		$tmp_ok = false;
	}
}

if (!$tmp_ok) {
	echo file_get_contents('http://mapler.me/inc/img/no-character.png');
	die();
}

// Get character attributes
$character_data = $q->fetch_array();
$internal_id = $character_data['internal_id'];
$character_id = $character_data['id'];


$skin = $character_data['skin'] + 2000;
$face = $character_data['eyes'];
$hair = $character_data['hair'];
$gender = $character_data['gender'];
$jobid = $character_data['job'];

$is_mercedes = $jobid == 2002 || ($jobid >= 2300 && $jobid <= 2312);

$ds_mark = $character_data['demonmark'];

$q->free();

$show_flipped = isset($_GET['flip']);


$using_face = GetCharacterOption($internal_id, 'avatar_face', CURRENT_LOCALE, 'default');
if (isset($_GET['face']) && !empty($_GET['face']))
	$using_face = $_GET['face'];
if (isset($_GET['madface']))
	$using_face = 'angry';

if (!isset($avatar_faces[$using_face])) $using_face = 'default';

$char_stance = isset($_GET['stance']) ? $_GET['stance'] : GetCharacterOption($internal_id, 'avatar_stance', CURRENT_LOCALE, 'stand');
$char_stance_frame = isset($_GET['stance_frame']) ? $_GET['stance_frame'] : '0';
$stand = 1;

$weapongroup = -1;


$shown_items = array();

// Get character equipment
$character_equipment = $__char_db->query("
SELECT 
	itemid, slot, display_id 
FROM 
	`items` 
WHERE 
	`character_id` = " . $internal_id . "
AND 
	`inventory` = 0 
AND 
	`slot` < 0 
AND 
	`slot` > -200 
/*
# If you want to hide expired items... uncomment
AND
	TO_FILETIME(NOW()) < `expires` 
*/
ORDER BY 
	`slot` DESC
");

while ($row2 = $character_equipment->fetch_assoc()) {
	$slot = abs($row2['slot']) % 100;
	$itemid = GetID($row2);
	$iscash = floor(abs($row2['slot']) / 100) == 1;
	if (DEBUGGING)
		echo 'Slot: '.$row2['slot'].' ('.$slot.') : '.$itemid."\r\n";
	if (!$iscash) {
		if (isset($shown_items[$slot])) continue;
		else $shown_items[$slot] = $itemid;
	}
	else {
		$shown_items[$slot] = $itemid;
	}
	
	if ($row2['slot'] == -11) {
		// Prepare item type for cash item
		$weapongroup = ($itemid / 10000) % 100;
	}
	elseif ($row2['slot'] == -105) { // Cash Shirt
		if (GetItemType($itemid) == 105 && isset($shown_items[6])) // Is a cash overall and has pants
			unset($shown_items[6]); // Unset pants
	}
}

if (!isset($shown_items[5])) {
	if (DEBUGGING)
		echo 'Missing slot 5 equip, setting shirt.'."\r\n";
	$shown_items[5] = $gender == 0 ? 1060026 : 1061039;
}

if (!isset($shown_items[6]) && GetItemType($shown_items[5]) != 105) {
	if (DEBUGGING)
		echo 'Missing slot 6 equip, setting pants.'."\r\n";
	$shown_items[6] = $gender == 0 ? 1040036 : 1041046;
}


$character_equipment->free();

if ($char_stance == 'stand' || $char_stance == 'walk')
	$char_stance .= $stand;


if (DEBUGGING) {
	//print_r($shown_items);
}

$request_info = array();
$request_info['slots'] = array();



$request_info['slots'][] = $skin;
$request_info['slots'][] = $skin + 10000;
$request_info['slots'][] = $hair;
$request_info['slots'][] = $face;
// non-naked clothes
$request_info['slots'][] = $gender == 0 ? 1060026 : 1061039;


if ($ds_mark > 0) {
	$request_info['slots'][] = $ds_mark;
}


foreach ($shown_items as $slot => $itemid)
	$request_info['slots'][] = $itemid;




//RenderCashItem(5010065);
//RenderCashItem(5010073);
// Demonzz
if ($jobid == 6000 || $jobid == 6100 || $jobid == 6110 || $jobid == 6111 || $jobid == 6112) {
	$request_info['slots'][] = 5010087; // wings
	$request_info['slots'][] = 5010090; // tail
	/*
	// Gauge half?
	RenderCashItem(5010088); // wings
	RenderCashItem(5010091); // tail
	// Gauge full?
	RenderCashItem(5010089); // wings
	RenderCashItem(5010092); // tail
	*/
}

function BuildCodeString($slots, $size, $name, $weapongroup, $guildname, $embleminfo, $elf, $face, $flip, $showname, $tamingmob) {
	$tmp = '';
	foreach ($slots AS $key => $value)
		$tmp .= 'slot['.$key.']='.$value.'&';
	
	$tmp .= 'name='.$name;
	$tmp .= '&size='.$size;
	$tmp .= '&guildname='.$guildname;
	$tmp .= '&embleminfo='.join('.', $embleminfo);
	$tmp .= '&weapongroup='.$weapongroup;
	$tmp .= '&face='.$face;
	$tmp .= '&tamingmob='.$tamingmob;
	if ($elf)
		$tmp .= '&elvenears';
	if ($flip)
		$tmp .= '&flip';
	if ($showname)
		$tmp .= '&showname';
	
	return urlencode(base64_encode(gzcompress($tmp)));
}


$url = 'http://'.$domain.'/actions/render_character.php?'.(DEBUGGING ? 'debug&' : '').'code='.
	BuildCodeString(
		$request_info['slots'], $size, $charname, $weapongroup, 
		'', array(0,0,0,0), 
		$is_mercedes, $using_face, $show_flipped, 
		isset($_GET['show_name']), 
		!empty($_GET['tamingmob']) ? intval($_GET['tamingmob']) : ''
	);

if (DEBUGGING)
	echo '-------- REQUESTED FILE ----------------'."\r\n".$url."\r\n";
echo file_get_contents($url);


?>