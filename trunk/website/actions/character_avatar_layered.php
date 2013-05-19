<?php
require_once __DIR__.'/../inc/classes/database.php';
require_once __DIR__.'/../inc/functions.php';
require_once __DIR__.'/../inc/functions.datastorage.php';
require_once __DIR__.'/../inc/zmap.php';
require_once __DIR__.'/caching.php';

define("DEBUGGING", isset($_GET['debug']));

$font = "arial.ttf";
$font_size = "9.25";

if (!DEBUGGING) {
	error_reporting(0);
	ini_set('display_errors', 0);
	header('Content-Type: image/png');
}
else {
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
}


$image_width = 128;
$image_height = 128;

$charname = isset($_GET['name']) ? $_GET['name'] : 'RoboticOil';

$len = strlen($charname);
if ($len < 4 || $len > 12) {
	$im = imagecreatetruecolor ($image_width, $image_height);
	$bgc = imagecolorallocate ($im, 255, 255, 255);
	$tc = imagecolorallocate ($im, 0, 0, 0);

	imagefilledrectangle ($im, 0, 0, $image_width, $image_height, $bgc);

	/* Output an error message */
	imagestring ($im, 1, 5, 5, 'I AM ERROR', $tc);
	imagestring ($im, 1, 5, 20, 'Incorrect Charname', $tc);
	imagepng($im);
	imagedestroy($im);
	die();
}

$q = $__database->query("SELECT * FROM characters WHERE name = '".$__database->real_escape_string($charname)."'");
if ($q->num_rows == 0) {
	$im = imagecreatetruecolor ($image_width, $image_height);
	$bgc = imagecolorallocate ($im, 255, 255, 255);
	$tc = imagecolorallocate ($im, 0, 0, 0);

	imagefilledrectangle ($im, 0, 0, $image_width, $image_height, $bgc);

	/* Output an error message */
	imagestring ($im, 1, 5, 5, 'I AM ERROR', $tc);
	imagestring ($im, 1, 5, 20, "Not found.", $tc);
	imagepng($im);
	imagedestroy($im);
	die();
}

// Get character attributes
$character_data = $q->fetch_array();
$internal_id = $character_data['internal_id'];
$character_id = $character_data['id'];

$image_mode = isset($_GET['show_name']) ? 'avatar_ingame' : 'avatar';


if (!isset($_GET['NO_CACHING']))
	ShowCachedImage($internal_id, $image_mode, $character_data['last_update'], false, '1 MINUTE');

$id = uniqid().rand(0, 999);
if (!isset($_GET['NO_CACHING']))
	AddCacheImage($internal_id, $image_mode, $character_data['last_update'], $id);



// Create blank template
$im = imagecreatetruecolor($image_width, $image_height);
imagesavealpha($im, true);
$trans = imagecolorallocatealpha($im, 0, 0, 0, 127);
imagefill($im, 0, 0, $trans);

// Set everything null for the hash
$skin = $face = $hair = $hat = $mask = $eyes = $ears = $top = $pants = $overall = $shoe = $glove = $cape = $shield = $wep = $nxwep = NULL;

// Coordinates for center
$mainx = 60;
$mainy = 38;
$necky = $mainy + 31;
$handx = $mainx + 12;
$handy = $mainy + 38;
$hand2x = $handx - 12;
$hand2y = $handy;

// Some other variables

$main_dir = '/var/www/maplestory_images/';
if (!is_dir($main_dir)) {
	$main_dir = 'P:/Result/';
	if (!is_dir($main_dir)) {
		// your call
	}
}

$characterwz = $main_dir.'Character';
$guild_info_location = $main_dir.'GuildEmblem';

$skin = $character_data['skin'] + 2000;
$face = $character_data['eyes'];
$hair = $character_data['hair'];
$gender = $character_data['gender'];

$ds_mark = $character_data['demonmark'];


$q->free();


function GetID($row) {
	$itemid = $row['itemid'];
	if ($row['display_id'] > 0) {
		$itemid -= $itemid % 10000;
		$itemid += $row['display_id'];
	}
	return $itemid;
}

$zlayers = array();
$item_locations = array();

$using_face = GetCharacterOption($internal_id, 'avatar_face', 'default');
if (isset($_GET['madface']))
	$using_face = 'angry';

$stand = 1;

function CheckStand($type, $data) {
	global $stand;
	switch ($type) {
		case 140:	// 2-Handed Sword
		case 141:	// 2-Handed Axe
		case 142:	// 2-Handed BW
		case 143:	// Spear
		case 146:	// Crossbow
			$stand = 2;
			
			if (DEBUGGING)
				echo 'STANCE ITEM: '.$type."\r\n";
			break;
		case 144:	// Pole Arm
			if (isset($data['stand1'])) // Snowboards are stand = 1
				$stand = 1;
			else
				$stand = 2;
			if (DEBUGGING)
				echo 'STANCE ITEM: '.$type."\r\n";
			break;
	}
}


$foundHidingCap = false;
function ParseItem($id) {
	global $item_locations, $zlayers, $zmap, $main_dir, $using_face, $foundHidingCap;
	$iteminfo = get_data($id);
	$itemtype = GetItemType($id);

	$zvalue = '';
	$foundinfo = false;
	if (DEBUGGING)
		echo 'Item: '.$id."\r\n";
	
	$isface = isset($iteminfo['chu']);
	foreach ($iteminfo as $key => $value) {
		if ($key == 'ITEMID') continue;
		$tmp = isset($value[0]) ? $value[0] : ($isface && $key == 'default' ? $value : null);
		if ($tmp == null) continue;

		foreach ($tmp as $category => $block) {
			if (!isset($block['z'], $zmap[$block['z']])) continue;
			$zval = $zmap[$block['z']];
			if (DEBUGGING)
				echo $id.' - '.$itemtype.' - '.$key.' - '.$category.' - '.$zval.' - '.$zmap['characterEnd']."\r\n";

			if ($itemtype == 2 && $key != $using_face) continue;
			if ($itemtype == 1 && $category == 'ear') continue; // Android!
			if ($itemtype != 2 && $key != 'stand1' && $key != 'stand2' && $key != $using_face) continue;
			if ($itemtype == 121 && $category != 'weapon') continue;
			if ($itemtype == 190) continue;
			
			CheckStand($itemtype, $iteminfo);

			$objectdata = array(
				'info' => $block,
				'type' => $itemtype,
				'itemid' => $iteminfo['ITEMID'], 
				'stance' => $key, 
				'category' => $category,
				'vslot' => isset($iteminfo['info']['vslot']) ? $iteminfo['info']['vslot'] : array(),
				'islot' => isset($iteminfo['info']['islot']) ? $iteminfo['info']['islot'] : 'characterStart'
			);
			
			$objectdata['image'] = $objectdata['stance'].($isface && $key == 'default' ? '.' : '.0.').$objectdata['category'].'.png';
			$foundinfo = true;
			//if ($zmap[$objectdata['islot']] > $zmap['characterEnd']) continue;
			$zlayers[$zval][] = $objectdata;
			
			if (strpos($objectdata['islot'], 'Cp') !== false && in_array('H1', $objectdata['vslot'])) {
				$foundHidingCap = true;
			}
		}
	}
	if ($foundinfo)
		$item_locations[$iteminfo['ITEMID']] = GetItemDataLocation($main_dir, $iteminfo['ITEMID']);
}

ParseItem($face);
ParseItem($hair);
ParseItem($skin);
ParseItem($skin + 10000);

ParseItem($gender == 0 ? 1060026 : 1061039);


$cashitems = array();

// Get character equipment
$character_equipment = $__database->query("
SELECT 
	itemid, slot, display_id 
FROM 
	`items` 
WHERE 
	`character_id` = " . $internal_id . " 
AND 
	`slot` < 0 
AND 
	`slot` > -155 
AND 
	`inventory` = 0 
ORDER BY 
	`slot` ASC
"
);

while ($row2 = $character_equipment->fetch_assoc()) {
	$slot = abs($row2['slot']) % 100;
	$iscash = floor(abs($row2['slot']) / 100) == 1;
	if (DEBUGGING)
		echo 'Slot: '.$row2['slot']."\r\n";
	if (!$iscash) {
		if (isset($cashitems[$slot])) continue;
	}
	else {
		$cashitems[$slot] = true;
	}
	ParseItem(GetID($row2));
}
$character_equipment->free();

if ($ds_mark > 0) {
	ParseItem($ds_mark);
}


krsort($zlayers);

if (isset($_GET['use_bg'])) {
	$bg = GetCharacterOption($internal_id, 'avatar_bg');
	if ($bg !== null) {
		$bgname = '';
		switch ($bg) {
			case 0: $bgname = 'fm'; break;
			case 1: $bgname = 'kerning'; break;
			case 2: $bgname = 'kerning_hideout'; break;
			case 3: $bgname = 'monsterpark'; break;
			case 4: $bgname = 'ardentmill'; break;
			default: $bgname = 'fm'; break;
		}
		add_image(__DIR__.'/../inc/img/avatar_backgrounds/'.$bgname.'.png', 0, 0);
	}
}

if (DEBUGGING) {
	print_r($zlayers);
	print_r($item_locations);
}

foreach ($zlayers as $zname => $objects) {
	foreach ($objects as $object) {
		$zval = $object['info']['z'];

		if ($object['category'] == 'hairOverHead' && $foundHidingCap) {
			continue;
		}
		if ($object['stance'] == 'stand'.($stand == 1 ? 2 : 1)) continue;
		$img = $item_locations[$object['itemid']].$object['image'];
		$x = $mainx;
		$y = $mainy;
		if (isset($object['info']['map']['navel'])) {
			$x = $mainx;
			$y = $necky;
			
			$x -= $object['info']['map']['navel']['X'];
			$y -= $object['info']['map']['navel']['Y'];
		}
		elseif (isset($object['info']['map']['brow'])) {
			$x -= $object['info']['map']['brow']['X'];
			$y -= $object['info']['map']['brow']['Y'];
		}
		elseif (isset($object['info']['map']['neck'])) {
			$x = $mainx;
			$y = $necky;
			
			$x -= $object['info']['map']['neck']['X'];
			$y -= $object['info']['map']['neck']['Y'];
		}
		elseif (isset($object['info']['map']['hand'])) {
			$x = $stand == 1 ? $handx : $hand2x;
			$y = $stand == 1 ? $handy : $hand2y;
			$x -= $object['info']['map']['hand']['X'];
			$y -= $object['info']['map']['hand']['Y'];
		}
		
		
		if (isset($object['info']['origin']['X'])) $x -= $object['info']['origin']['X'];
		if (isset($object['info']['origin']['Y'])) $y -= $object['info']['origin']['Y'];
		if (DEBUGGING) {
			echo 'Adding '.$img.' at X '.$x.', Y '.$y.' --- Zname '.$zname.'  - Zmap value: '.$zval.' - '.implode(';', $object['vslot']).' - '.$object['islot']."\r\n";
		}
		add_image($img, $x, $y);
		
	}
}

// Render name
if (isset($_GET['show_name']))
	RenderName($character_data['name'], $image_width/2, $mainy + 71);


if (!isset($_GET['NO_CACHING']))
	SaveCacheImage($internal_id, $image_mode, $im, $id);

imagepng($im);
imagedestroy($im);





// Function to phrase data into an array
function get_data($itemid) {
	return GetItemWZInfo($itemid);
}

// Function to add element to the image
function add_image($location, $x, $y) {
	global $im;
	if (file_exists($location)) {
		if (DEBUGGING) {
			echo "Found ".$location."\r\n";
		}
		$image = imagecreatefrompng($location);
		imagecopy($im, $image, $x, $y, 0, 0, imagesx($image), imagesy($image));
	}
	elseif (DEBUGGING) {
		echo "-- Could not find ".$location." --\r\n";
	}
}


function RenderName($name, $x, $y) {
	global $character_id;
	global $im;
	global $font;
	global $font_size;
	global $__database;
	global $guild_info_location;

	$background = imagecolorallocatealpha($im, 0, 0, 0, 33);
	$fontcolor = imagecolorallocate($im, 255, 255, 255);
	
	$startWidth = $x - calculateWidth($name)/2;
	$endWidth = $x + calculateWidth($name)/2;
	DrawNameBox($im, $startWidth, $y - 17, $endWidth - 1, $y - 2, $background);
	ImageTTFText($im, $font_size, 0, $startWidth + 3, $y - 5, $fontcolor, $font, $name);
	$q = $__database->query("SELECT g.name, g.emblem_bg, g.emblem_bg_color, g.emblem_fg, g.emblem_fg_color FROM guild_members c INNER JOIN guilds g ON g.id = c.guild_id WHERE c.character_id = ".$character_id);
	
	if ($q->num_rows == 1) {
		$res = $q->fetch_array();
		$name = $res[0];
		$hasemblem = ($res[1] != 0 || $res[2] != 0 || $res[3] != 0 || $res[4] != 0) ? true : false;
		$startWidth = $x - calculateWidth($name) / 2;
		$endWidth = $x + calculateWidth($name) / 2;
		
		DrawNameBox($im, $startWidth, $y, $endWidth - 1, $y + 15, $background);
		ImageTTFText($im, $font_size, 0, $startWidth + 2, $y + 12, $fontcolor, $font, $name);
		ImageTTFText($im, $font_size, 0, $startWidth + 3, $y + 12, $fontcolor, $font, $name); // Boldness
		
		if ($hasemblem) {
			if ($res[1] != 0 || $res[2] != 0) {
				add_image($guild_info_location.'/0000'.$res[1].'/'.$res[2].'.png', $startWidth - 18, $y + 0);
			}
			if ($res[3] != 0 || $res[4] != 0) {
				add_image($guild_info_location.'/0000'.$res[3].'/'.$res[4].'.png', $startWidth - 17, $y + 1);
			}
		}
		
	}
}

function calculateWidth($name) {
	global $font;
	global $font_size;
	$width = 7;
	$bbox = imagettfbbox($font_size, 0, $font, $name);
	$width += abs($bbox[4] - $bbox[0]);
	return $width;
}
function DrawNameBox($im, $x, $y, $cx, $cy, $col) {
	// Draw main text box
	imagefilledrectangle($im, $x + 1, $y, $cx - 1, $cy, $col);
	// Draw remaining right and left thingies
	imageline($im, $x, $y + 1, $x, $cy - 1, $col);
	imageline($im, $cx, $y + 1, $cx, $cy - 1, $col);
}

?>