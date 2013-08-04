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

$font = "arial.ttf";
$font_size = "9.25";

if (!DEBUGGING) {
	error_reporting(0);
	ini_set('display_errors', 0);
	header('Content-Type: image/png');
	
	$seconds_to_cache = 180;
	$ts = gmdate('D, d M Y H:i:s', time() + $seconds_to_cache) . ' GMT';
	header('Expires: '.$ts);
	header('Pragma: cache');
	header('Cache-Control: max-age='.$seconds_to_cache);
}
else {
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	
}


$image_width = 128;
$image_height = 128;
if (isset($_GET['size'])) {
	$res = 128;
	switch ($_GET['size']) {
		case 'original': $res = 96; break;
		case 'normal': $res = 128; break;
		case 'big': $res = 256; break;
		case 'huge': $res = 384; break; // 128 + 256
	}
	$image_width = $res;
	$image_height = $res;
}

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
$show_flipped = isset($_GET['flip']);


if (!isset($_GET['NO_CACHING']))
	ShowCachedImage($internal_id, $image_mode, $character_data['last_update'], false, '1 MINUTE');

$id = uniqid().rand(0, 999);
if (!isset($_GET['NO_CACHING']))
	AddCacheImage($internal_id, $image_mode, $character_data['last_update'], $id);



// Create blank template
$im = imagecreatetruecolor(max(256, $image_width), max(256, $image_height));
imagesavealpha($im, true);
$trans = imagecolorallocatealpha($im, 0, 0, 0, 127);
imagefill($im, 0, 0, $trans);

// Set everything null for the hash
$skin = $face = $hair = $hat = $mask = $eyes = $ears = $top = $pants = $overall = $shoe = $glove = $cape = $shield = $wep = $nxwep = NULL;

// Coordinates for center
$mainx = (imagesx($im) / 2) + ($show_flipped ? -6 : 6);
$mainy = (imagesy($im) / 2) + 18;

$char_body_position = array();

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
$jobid = $character_data['job'];

$is_mercedes = $jobid == 2002 || ($jobid >= 2300 && $jobid <= 2312);

$ds_mark = $character_data['demonmark'];

$flipped = false; // Does not work correctly


$q->free();

$zlayers = array();
$item_locations = array();

$using_face = GetCharacterOption($internal_id, 'avatar_face', 'default');
if (isset($_GET['madface']))
	$using_face = 'angry';
elseif (isset($_GET['face']) && !empty($_GET['face']))
	$using_face = $_GET['face'];

if (!isset($avatar_faces[$using_face])) $using_face = 'default';

$char_stance = isset($_GET['stance']) ? $_GET['stance'] : GetCharacterOption($internal_id, 'avatar_stance', 'stand');
$char_stance_frame = isset($_GET['stance_frame']) ? $_GET['stance_frame'] : '0';
$stand = 1;

$standardWeapon = -1;
$cashWeapon = -1;
$weaponThingType = -1;

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
				echo 'STANCE ITEM: '.$type.' - '.$data['ITEMID']."\r\n";
			break;
		case 144:	// Pole Arm
			if (isset($data['stand1'])) // Snowboards are stand = 1
				$stand = 1;
			else
				$stand = 2;
			if (DEBUGGING)
				echo 'STANCE ITEM: '.$type.' - '.$data['ITEMID']."\r\n";
			break;
	}
	
	if (isset($data['stand'])) {
		if (DEBUGGING)
			echo 'STANCE PROP ITEM: '.$type.' - '.$data['ITEMID']."\r\n";
		$stand = $data['stand'];
	}
}


$shown_items = array();

// Get character equipment
$character_equipment = $__database->query("
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
		echo 'Slot: '.$row2['slot'].' ('.$slot.')'."\r\n";
	if (!$iscash) {
		if (isset($shown_items[$slot])) continue;
		else $shown_items[$slot] = $itemid;
	}
	else {
		$shown_items[$slot] = $itemid;
	}
	
	if ($row2['slot'] == -11) {
		$standardWeapon = $itemid;
		$weaponThingType = ($standardWeapon / 10000) % 100;
	}
	elseif ($row2['slot'] == -111) { // Cash weapon
		$cashWeapon = $itemid;
	}
}

if (isset($shown_items[11])) {
	CheckStand(GetItemType($standardWeapon), get_data($standardWeapon));
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


$foundHidingCap = false;
function ParseItem($id) {
	global $item_locations, $zlayers, $zmap, $main_dir, $using_face, $foundHidingCap, $char_stance, $char_stance_frame, $char_body_position, $flipped, $cashWeapon, $weaponThingType;
	global $mainx, $mainy;
	global $is_mercedes;
	
	$iteminfo = get_data($id);
	$itemtype = GetItemType($id);
	$item_raw_type = floor($id / 1000);
	$item_section_id = $id % 1000;

	$zvalue = '';
	$foundinfo = false;
	if (DEBUGGING) {
		echo 'Item: '.$id."\r\n";
		echo 'Item type: '.$itemtype."\r\n";
		echo 'Item raw type: '.$item_raw_type."\r\n";
		echo 'Item section id: '.$item_section_id."\r\n";
	}
	
	$nxwep = $id == $cashWeapon;
	if ($nxwep) {
		$iteminfo = $iteminfo[$weaponThingType];
		$iteminfo['ITEMID'] = $id;
		if (DEBUGGING)
			echo 'NX wep Item: '.$weaponThingType."\r\n";
	}
	

	$isface = isset($iteminfo[$using_face]);
	foreach ($iteminfo as $key => $value) {
		if (DEBUGGING)
			echo '> '.$key."\r\n";
		$tmp = null;
		if ($key == 'ITEMID' || $key == 'info' || !($value instanceof TreeNode)) continue;
		if ($isface) {
			if ($key == 'default')
				$tmp = $value;
			else
				$tmp = $value->offsetGet('0');
		}
		else
			$tmp = $value[$char_stance_frame];
		
		if ($tmp === null) {
			if (DEBUGGING) {
				echo 'No info found for '.$key."\r\n";
			}
			continue;
		}
		
		$imgkey = $key;
		
		if (!($tmp instanceof TreeNode)) {
			if (DEBUGGING) {
				echo 'Prolly uol->'.$tmp."\r\n";
			}
			$tmp = $iteminfo[$key];
		}

		if ($tmp === null) {
			if (DEBUGGING) {
				echo 'No info found for '.$key."\r\n";
			}
			continue;
		}

		$tmp->ksort();
		
		foreach ($tmp as $category => $block) {
			if (DEBUGGING) {
				echo '>> '.$category."\r\n";
			}
			if (!($block instanceof TreeNode)) {
				// Prolly UOL
				$block = $tmp[$category];

				if ($block === null) {
					if (DEBUGGING) {
						echo 'UOL not found for '.$key.' -> '.$category."\r\n";
					}
					continue;
				}
			}
			if (!($block instanceof TreeNode)) {
				if (DEBUGGING) {
					echo 'Thats not a TreeNode! '.$key.' -> '.$category."\r\n";
				}
				continue;
			
			}
			if ($block === null) {
				if (DEBUGGING) {
					echo 'thing not found for '.$key.' -> '.$category."\r\n";
				}
				continue;
			}
			
			$tmptmp = $block;
			$imgkey = '';
			$blockname = '';
			while (true) {
				$imgkey = $tmptmp->name.($imgkey == '' ? '' : '.'.$imgkey);
				$blockname = $tmptmp->name;
				if (!isset($tmptmp['..'])) break;
				if ($tmptmp['..']->isroot) break;
				if ($tmptmp['..']->name == 'main') break;
				$tmptmp = $tmptmp['..'];
			}
			
			$imgkey = str_replace('.origin', '', $imgkey);
			
			if (!isset($block['z'], $zmap[$block['z']])) {
				if (DEBUGGING) {
					echo 'No Z key found for '.$key.' -> '.$category.' -> '.$block->name."\r\n";
				}
				continue;
			}
			$zval = $zmap[$block['z']];
			if (DEBUGGING)
				echo $id.' - '.$itemtype.' - '.$key.' - '.$category.' - '.$zval.' - '.$zmap['characterEnd']."\r\n";

			if ($itemtype == 2 && $key != $using_face && $char_stance != 'rope') {
				if (DEBUGGING)
					echo "nope1".$key." : ".$using_face."\r\n";
				
				continue 2;
			}
			if ($itemtype == 1 && $category == 'ear' && !$is_mercedes) { // Android / Merc!
				if (DEBUGGING)
					echo "nope2\r\n";
				
				continue;
			}
			if ($itemtype != 2 && 
				$key != $char_stance && 
				$key != $using_face) {
				if (DEBUGGING)
					echo "nope3:".$key." : ".$char_stance." : ".$using_face."\r\n";
				
				continue 2;
			}
			if ($itemtype == 121 && $category != 'weapon') {
				if (DEBUGGING)
					echo "nope4\r\n";
				
				continue 2;
			}
			if ($itemtype == 190) {
				if (DEBUGGING)
					echo "nope5\r\n";
				
				continue 2;
			}


			$objectdata = array(
				'info' => $block,
				'type' => $itemtype,
				'itemid' => $iteminfo['ITEMID'], 
				'stance' => $blockname,
				'category' => $category,
				'vslot' => isset($iteminfo['info']['vslot']) ? $iteminfo['info']['vslot'] : array(),
				'islot' => isset($iteminfo['info']['islot']) ? $iteminfo['info']['islot'] : 'characterStart'
			);
			
			$x = $mainx;
			$y = $mainy;
			
			$mappings = $objectdata['info']['map'];
			if (!isset($mappings)) {
				continue;
			}
			$copy = $mappings->getArrayCopy();
			krsort($copy);
			foreach ($copy as $mapname => $mapping) {
				if (!isset($char_body_position[$mapname])) {
					if ($flipped)
						$char_body_position[$mapname][0] = $x - $mapping['X'];
					else
						$char_body_position[$mapname][0] = $x + $mapping['X'];
					$char_body_position[$mapname][1] = $y + $mapping['Y'];
				}
				else {
					if ($flipped)
						$x = $char_body_position[$mapname][0] + $mapping['X'];
					else
						$x = $char_body_position[$mapname][0] - $mapping['X'];
					$y = $char_body_position[$mapname][1] - $mapping['Y'];
				}
			}

			if (DEBUGGING)
				echo 'Final map '.$x.', '.$y."\r\n";
			$objectdata['x'] = $x;
			$objectdata['y'] = $y;

			$objectdata['image'] = $imgkey.'.png';
			if (DEBUGGING)
				echo 'Image '.$objectdata['image']."\r\n";
			$foundinfo = true;
			$zlayers[$zval][] = $objectdata;
			
			if (strpos($objectdata['islot'], 'Cp') !== false && in_array('H1', $objectdata['vslot'])) {
				$foundHidingCap = true;
			}
		}
	}
	if ($foundinfo)
		$item_locations[$iteminfo['ITEMID']] = GetItemDataLocation($main_dir, $iteminfo['ITEMID']);
}

if (DEBUGGING) {
	//print_r($shown_items);
}

{
	$iteminfo = get_data($skin);
	//if (DEBUGGING)
	//	print_r($iteminfo);
	// Set global position values
	$map_node = $iteminfo[$char_stance][$char_stance_frame]['body']['map'];

	if ($flipped)
		$char_body_position['navel'][0] = $mainx - $map_node['navel']['X'];
	else
		$char_body_position['navel'][0] = $mainx + $map_node['navel']['X'];
	$char_body_position['navel'][1] = $mainy + $map_node['navel']['Y'];
	if (DEBUGGING)
		echo 'Did find map: navel '.$char_body_position['navel'][0].', '.$char_body_position['navel'][1].' > '.$map_node['navel']['X'].' - '.$map_node['navel']['Y']."\r\n";

}

ParseItem($skin);
ParseItem($skin + 10000);
ParseItem($hair);
ParseItem($face);
// non-naked clothes
ParseItem($gender == 0 ? 1060026 : 1061039);


if ($ds_mark > 0) {
	ParseItem($ds_mark);
}


foreach ($shown_items as $slot => $itemid)
	ParseItem($itemid);


krsort($zlayers);

//if (DEBUGGING)
//	print_r($zlayers);

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



foreach ($zlayers as $zname => $objects) {
	foreach ($objects as $object) {
		$zval = $object['info']['z'];

		if ($object['category'] == 'hairOverHead' && $foundHidingCap) {
			continue;
		}
		// if ($object['stance'] == 'stand'.($stand == 1 ? 2 : 1)) continue;
		$img = $item_locations[$object['itemid']].$object['image'];
		$x = $object['x'];
		$y = $object['y'];
			
			
		if (isset($object['info']['origin'])) {
			$x -= $object['info']['origin']['X'];
			$y -= $object['info']['origin']['Y'];
		}
		
		if (DEBUGGING) {
			echo 'Adding '.$object['itemid'].' -> '.$img.' at X '.$x.', Y '.$y.' --- Zname '.$zname.'  - Zmap value: '.$zval.' - '.implode(';', $object['vslot']).' - '.$object['islot']."\r\n";
		}
		add_image($img, $x, $y, $flipped);
		
	}
}


$extra_layers = array();

function RenderCashItem($itemid) {
	global $char_body_position, $char_stance, $char_stance_frame;
	global $mainx, $mainy, $main_dir;
	global $extra_layers;

	$iteminfo = get_data($itemid);
	
	$item_section_id = $itemid % 1000;
	
	$support_guys_and_gals = $item_section_id >= 73 && $item_section_id <= 74;

	if (isset($iteminfo['effect']['default']) && !isset($iteminfo['effect'][$char_stance]))
		$iteminfo['effect'][$char_stance] = $iteminfo['effect']['default'];
	$iteminfo = $iteminfo['effect'][$char_stance];
	
	$block = isset($iteminfo[$char_stance_frame]) ? $iteminfo[$char_stance_frame] : null;
	
	if ($block === null) {
		if (DEBUGGING)
			echo 'Block not found for: '.$itemid.'!!!'."\r\n";
		return;
	}
	
	$x = $mainx;
	$y = $mainy;
	
	// If no pos, use mainx/y
	// If pos, use 
	$ispos = isset($iteminfo['pos']) && $iteminfo['pos'] != 0;
	
	if ($ispos) {
		$x = $char_body_position['navel'][0];
		$y = $char_body_position['navel'][1] - 30;
	}

	if (DEBUGGING)
		print_r($iteminfo);
	if (isset($block['origin']['X'])) {
		if (DEBUGGING)
			echo 'found xy'."\r\n";
		$x -= $block['origin']['X'];
		$y -= $block['origin']['Y'];
	}
	
	$img_location = GetItemDataLocation($main_dir, $itemid).'effect.'.$block['..']->name.'.'.$block->name.'.png';
	
	$layer = $block['..']['z'];
	
	$extra_layers[$layer][] = array($img_location, $x, $y);
}


//RenderCashItem(5010065);
//RenderCashItem(5010073);
if ($jobid == 6000 || $jobid == 6100 || $jobid == 6110 || $jobid == 6111 || $jobid == 6112) {
	RenderCashItem(5010087); // wings
	RenderCashItem(5010090); // tail
	/*
	// Gauge half?
	RenderCashItem(5010088); // wings
	RenderCashItem(5010091); // tail
	// Gauge full?
	RenderCashItem(5010089); // wings
	RenderCashItem(5010092); // tail
	*/
}


if (DEBUGGING) {
	var_export($extra_layers);
}


if (true || !DEBUGGING) {
	$w = imagesx($im);
	$h = imagesy($im);
	if (count($extra_layers) > 0) {
		$temp_image = imagecreatetruecolor($w, $h);
		imagesavealpha($temp_image, true);
		$trans = imagecolorallocatealpha($temp_image, 0, 0, 0, 127);
		imagefill($temp_image, 0, 0, $trans);
	
		if (isset($extra_layers[-2])) foreach ($extra_layers[-2] as $imginfo) add_image($imginfo[0], $imginfo[1], $imginfo[2], false, $temp_image);
		if (isset($extra_layers[-1])) foreach ($extra_layers[-1] as $imginfo) add_image($imginfo[0], $imginfo[1], $imginfo[2], false, $temp_image);
		
		imagecopy($temp_image, $im, 0, 0, 0, 0, $w, $h);
		
		if (isset($extra_layers[ 1])) foreach ($extra_layers[ 1] as $imginfo) add_image($imginfo[0], $imginfo[1], $imginfo[2], false, $temp_image);
		if (isset($extra_layers[ 2])) foreach ($extra_layers[ 2] as $imginfo) add_image($imginfo[0], $imginfo[1], $imginfo[2], false, $temp_image);
		
		$im = $temp_image;
	}
	$final_image = imagecreatetruecolor($image_width, $image_height);
	imagesavealpha($final_image, true);
	$trans = imagecolorallocatealpha($final_image, 0, 0, 0, 127);
	imagefill($final_image, 0, 0, $trans);
	
	// Copy created avatar onto the plane
	if ($show_flipped) {
		$im = FlipImage($im);
	}
	imagecopy($final_image, $im, 
		0, 0, 
		($w / 2) - ($image_width / 2), ($h / 2) - ($image_height / 2), 
		$image_width, $image_height);
	imagedestroy($im);
	$im = $final_image;
	
	
	
	
	// Change mainx/y
	$mainx = (imagesx($im) / 2) + ($show_flipped ? 6 : 0);
	$mainy = (imagesy($im) / 2) + 18;
	
	// Render name
	if (isset($_GET['show_name']))
		RenderName($character_data['name'], $mainx, $mainy + 20);


	if (!isset($_GET['NO_CACHING']))
		SaveCacheImage($internal_id, $image_mode, $im, $id);

	if (DEBUGGING)
		header('Content-type: text/plain');


	imagepng($im);
}
imagedestroy($im);




// Function to phrase data into an array
function get_data($itemid) {
	return GetItemWZInfo($itemid);
}

// Function to add element to the image
function add_image($img_location, $x, $y, $flipped = false, $to_image = null) {
	global $im;
	if (file_exists($img_location)) {
		if (DEBUGGING) {
			echo "Found ".$img_location."\r\n";
		}
		$image = imagecreatefrompng($img_location);
		if ($flipped) {
			$image = FlipImage($image);
		}
		
		imagecopy($to_image === null ? $im : $to_image, $image, $x, $y, 0, 0, imagesx($image), imagesy($image));
		imagedestroy($image);
	}
	elseif (DEBUGGING) {
		echo "-- Could not find ".$img_location." --\r\n";
	}
}

function FlipImage($sauce) {
	$w = imagesx($sauce);
	$h = imagesy($sauce);
	$output = imagecreatetruecolor($w, $h);
	imagealphablending($output, false);
	imagesavealpha($output, true);
	for ($i = 0; $i < $w; $i++) {
		imagecopy($output, $sauce, 
			$i, 0, 
			$w - $i - 1, 0, 
			1, $h);
	}
	
	return $output;
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