<?php
set_time_limit(1000);
define('DEBUGGING', isset($_GET['debug']));

/*
	Build up a character image, using either a full request URI, or a Base64'd GZIPped value containing the request..!
	
	Examples:
		- render_character.php?slot[0]=12312&slot[1]=123132
		- render_character.php?code==eJwrzskviTaItTU0UMtLzE219U0syEl1Kc3NrQQAeVoJSA==

*/

require_once __DIR__.'/../inc/functions.php';
require_once __DIR__.'/../inc/functions.datastorage.php';
require_once __DIR__.'/../inc/avatar_faces.php';
require_once __DIR__.'/../inc/zmap.php';

// Set headers
if (!DEBUGGING) {
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
	header('Content-Type: text/plain');
}


// Functions

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

function BuildCodeString($slots, $name) {
	$tmp = '';
	foreach ($slots AS $key => $value)
		$tmp .= 'slot['.$key.']='.$value.'&';
	
	$tmp .= 'name='.$name;
	
	return base64_encode(gzcompress($tmp));
}


$foundHidingCap = false;
function ParseItem($id) {
	global $options, $imageoptions, $data_buffer, $foundHidingCap, $zmap;

	$iteminfo = GetItemWZInfo($id, CURRENT_LOCALE);
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
		echo 'Item image at: '.GetItemDataLocation($data_buffer['main-dir'], $id)."\r\n";
	}

	$isface = isset($iteminfo[$options['face']]);
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
			$tmp = $value[$options['stance_frame']];
		
		if ($tmp === null) {
			if (DEBUGGING) {
				echo 'No info found for '.$key."\r\n";
				//var_export($iteminfo);
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
			if ($category == 'delay') continue;
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
					echo 'No Z key found for '.$key.' -> '.$category.' -> '.$block->name.' ('.$item_raw_type.') -- '.$block->ToAbsoluteURI().' - '."\r\n";
				}
				if ($item_raw_type == 21) {
				
					if (DEBUGGING) {
						echo 'Fixing Z index for face. Its face.'."\r\n";
					}
					$zval = $zmap['face'];
				}
				else
					continue;
			}
			else {
				$zval = $zmap[$block['z']];
			}
			if (DEBUGGING)
				echo $id.' - '.$itemtype.' - '.$key.' - '.$category.' - '.$zval."\r\n";

			if ($itemtype == 2 && $key != $options['face'] && $options['stance'] != 'rope') {
				if (DEBUGGING)
					echo "nope1".$key." : ".$options['face']."\r\n";
				
				continue 2;
			}
			if ($itemtype == 1 && $category == 'ear' && !$options['elven_ears']) { // Android / Merc!
				if (DEBUGGING)
					echo "nope2\r\n";
				
				continue;
			}
			if ($itemtype != 2 && 
				$key != $options['stance'] && 
				$key != $options['face']) {
				if (DEBUGGING)
					echo "nope3:".$key." : ".$options['stance']." : ".$options['face']."\r\n";
				
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
			
			$x = $imageoptions['mainx'];
			$y = $imageoptions['mainy'];
			
			$mappings = $objectdata['info']['map'];
			if (!isset($mappings)) {
				continue;
			}
			$copy = $mappings->getArrayCopy();
			krsort($copy);
			foreach ($copy as $mapname => $mapping) {
				if (!isset($data_buffer['body_map'][$mapname])) {
					$data_buffer['body_map'][$mapname][0] = $x + $mapping['X'];
					$data_buffer['body_map'][$mapname][1] = $y + $mapping['Y'];
				}
				else {
					$x = $data_buffer['body_map'][$mapname][0] - $mapping['X'];
					$y = $data_buffer['body_map'][$mapname][1] - $mapping['Y'];
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
			$data_buffer['zlayers'][$zval][] = $objectdata;
			
			if (strpos($objectdata['islot'], 'Cp') !== false && in_array('H1', $objectdata['vslot'])) {
				$foundHidingCap = true;
			}
		}
	}
	if ($foundinfo) {
		$data_buffer['item_locations'][$id] = GetItemDataLocation($data_buffer['main-dir'], $id);
	}
}

function RenderCashItem($itemid) {
	global $options;
	global $imageoptions;
	global $data_buffer;

	$iteminfo = get_data($itemid);
	
	if (isset($iteminfo['effect']['default']) && !isset($iteminfo['effect'][$char_stance]))
		$iteminfo['effect'][$char_stance] = $iteminfo['effect']['default'];
	$iteminfo = $iteminfo['effect'][$char_stance];
	
	$block = isset($iteminfo[$char_stance_frame]) ? $iteminfo[$char_stance_frame] : null;
	
	if ($block === null) {
		if (DEBUGGING)
			echo 'Block not found for: '.$itemid.'!!!'."\r\n";
		return;
	}
	
	$x = $imageoptions['mainx'];
	$y = $imageoptions['mainy'];
	
	// If no pos, use mainx/y
	// If pos, use it
	$ispos = isset($iteminfo['pos']) && $iteminfo['pos'] != 0;
	
	if ($ispos) {
		$x = $data_buffer['body_map']['navel'][0];
		$y = $data_buffer['body_map']['navel'][1] - 30;
	}

	if (DEBUGGING)
		print_r($iteminfo);
	if (isset($block['origin']['X'])) {
		if (DEBUGGING)
			echo 'found xy'."\r\n";
		$x -= $block['origin']['X'];
		$y -= $block['origin']['Y'];
	}
	
	$img_location = GetItemDataLocation($data_buffer['main-dir'], $itemid).'effect.'.$block['..']->name.'.'.$block->name.'.png';
	
	$layer = $block['..']['z'];
	
	$data_buffer['extra_layers'][$layer][] = array($img_location, $x, $y);
}

function DrawImage($img_location, $x, $y, $flipped = false, $to_image = null) {
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
	global $imageoptions;
	global $options, $data_buffer;

	$background = imagecolorallocatealpha($im, 0, 0, 0, 33);
	$fontcolor = imagecolorallocate($im, 255, 255, 255);
	
	$startWidth = $x - CalculateStringWidth($name)/2;
	$endWidth = $x + CalculateStringWidth($name)/2;
	DrawNameBox($im, $startWidth, $y - 17, $endWidth - 1, $y - 2, $background);
	ImageTTFText($im, $imageoptions['font_size'], 0, $startWidth + 3, $y - 5, $fontcolor, $imageoptions['font'], $name);
	
	if ($options['guild_name'] != '') {
		$name = $options['guild_name'];
		$hasemblem = ($options['guild_emblem_bg'] != 0 || $options['guild_emblem_bgc'] != 0 || $options['guild_emblem_fg'] != 0 || $options['guild_emblem_fgc'] != 0) ? true : false;
		$startWidth = $x - CalculateStringWidth($name) / 2;
		$endWidth = $x + CalculateStringWidth($name) / 2;
		
		DrawNameBox($im, $startWidth, $y, $endWidth - 1, $y + 15, $background);
		ImageTTFText($im, $imageoptions['font_size'], 0, $startWidth + 2, $y + 12, $fontcolor, $imageoptions['font'], $name);
		ImageTTFText($im, $imageoptions['font_size'], 0, $startWidth + 3, $y + 12, $fontcolor, $imageoptions['font'], $name); // Boldness
		
		if ($hasemblem) {
			if ($options['guild_emblem_bg'] != 0 || $options['guild_emblem_bgc'] != 0) {
				DrawImage($data_buffer['main-dir-guildemblem'].'/0000'.$options['guild_emblem_bg'].'/'.$options['guild_emblem_bgc'].'.png', $startWidth - 18, $y + 0);
			}
			if ($options['guild_emblem_fg'] != 0 || $options['guild_emblem_fgc'] != 0) {
				DrawImage($data_buffer['main-dir-guildemblem'].'/0000'.$options['guild_emblem_fg'].'/'.$options['guild_emblem_fgc'].'.png', $startWidth - 17, $y + 1);
			}
		}
		
	}
}

function CalculateStringWidth($name) {
	global $imageoptions;
	global $font_size;
	$width = 7;
	$bbox = imagettfbbox($imageoptions['font_size'], 0, $imageoptions['font'], $name);
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

function FindAndAddSetEffect() {
	global $options;
	global $data_buffer, $imageoptions;
	
	$itemids = array_flip($options['slots']);
	
	$setitemdata = GetItemWZInfo(90000000, CURRENT_LOCALE);

	$seturi = null;
	foreach ($setitemdata as $setid => $moredata) {
		if (isset($moredata['parts']) && $moredata['parts'] == 1) continue;
		if (!isset($moredata['ItemID'], $moredata['effectLink'])) continue;
		foreach ($moredata['ItemID'] as $idx => $itemid) {
			if (!isset($itemids[$itemid])) {
				break; // Try next set. lol
			}
			$seturi = $moredata['effectLink']; // Need full set
		}
	}
	
	if ($seturi !== null && strpos($seturi, 'SetItemInfoEff.img') !== false) {
		// Boom chakalacka...ka?
		
		// Get set ID
		
		$id = substr($seturi, strrpos($seturi, '/') + 1);
		
		$effectitemdata = GetItemWZInfo(94000000, CURRENT_LOCALE);
		
		if (!isset($effectitemdata[''.$id])) return;
		$block = $effectitemdata[''.$id]['0'];
		
		$x = $imageoptions['mainx'];
		$y = $imageoptions['mainy'];
		

		if (isset($block['origin']['X'])) {
			$x -= $block['origin']['X'];
			$y -= $block['origin']['Y'];
		}
		
		$img_location = $data_buffer['main-dir'].'Effects/SetItemInfoEff.img/'.$id.'/0.0.png';
		
		$layer = -2;
		
		$data_buffer['extra_layers'][$layer][] = array($img_location, $x, $y);
		
	}
}

function FindAndAddItemEffect() {
	global $options;
	global $data_buffer, $imageoptions;
	
	$itemids = array_flip($options['slots']);
	
	$itemeffdata = GetItemWZInfo(92000000, CURRENT_LOCALE);

	$effectid = null;
	foreach ($itemeffdata as $setid => $moredata) {
		if (!isset($moredata['effect'], $moredata['info'])) continue;
		foreach ($moredata['info'] as $slotid => $items) {
			foreach ($items as $idx => $itemid) {
				if (isset($itemids[$itemid])) {
					$effectid = $setid;
					break; // Try next set. lol
				}
			}
		}
	}
	
	if ($effectid !== null) {
		$block = $itemeffdata[$effectid]['effect']['0'];
		
		$x = $imageoptions['mainx'];
		$y = $imageoptions['mainy'];
		

		if (isset($block['origin']['X'])) {
			$x -= $block['origin']['X'];
			$y -= $block['origin']['Y'];
		}
		
		$img_location = $data_buffer['main-dir'].'Effects/SetEff.img/'.$effectid.'/effect.0.png';
		
		$layer = -2;
		
		$data_buffer['extra_layers'][$layer][] = array($img_location, $x, $y);
		
	}
}

function RenderTamedMob() {
	global $options;
	global $data_buffer, $imageoptions, $zmap;
	
	$itemid = $options['tamingmob'];
	
	// TamingMob manipulates body X/Y
	$info = GetItemWZInfo($itemid, CURRENT_LOCALE);
	
	$mainx = $imageoptions['mainx'];
	$mainy = $imageoptions['mainy'];
	
	if ($info !== null) {
		$block = $info[$options['stance']][$options['stance_frame']];
		
		foreach ($block as $innerid => $innerblock) {
			if (!($innerblock instanceof TreeNode)) {
				continue;
			}
			$zval = $innerblock['z'];
			if ($zval == 'tamingMobBack') {
				// Nexon'd
				$zval = 'tamingMobRear';
			}
			
			if (is_string($zval))
				$zval = $zmap[$zval];
			
			$objectdata = array(
				'info' => $innerblock,
				'itemid' => $itemid,
				'category' => 'tamingmob',
				'vslot' => array(),
				'islot' => 'Tm',
			);
			
			$x = $mainx;
			$y = $mainy;
			
			$mappings = $objectdata['info']['map'];
			if (!isset($mappings)) {
				continue;
			}
			else {
				
				$imageoptions['mainx'] = $mainx + $mappings['navel']['X'];
				$imageoptions['mainy'] = $mainy + $mappings['navel']['Y'] + 20;
			}
			
			$copy = $mappings->getArrayCopy();
			krsort($copy);
			foreach ($copy as $mapname => $mapping) {
				if (!isset($data_buffer['body_map'][$mapname])) {
					$data_buffer['body_map'][$mapname][0] = $x + $mapping['X'];
					$data_buffer['body_map'][$mapname][1] = $y + $mapping['Y'];
				}
				else {
					$x = $data_buffer['body_map'][$mapname][0] - $mapping['X'];
					$y = $data_buffer['body_map'][$mapname][1] - $mapping['Y'];
				}
			}

			if (DEBUGGING)
				echo 'Final map '.$x.', '.$y."\r\n";
			$objectdata['x'] = $x;
			$objectdata['y'] = $y;

			$objectdata['image'] = $options['stance'].'.'.$options['stance_frame'].'.'.$innerid.'.png';
			if (DEBUGGING)
				echo 'Image '.$objectdata['image']."\r\n";
			$foundinfo = true;
			$data_buffer['zlayers'][$zval][] = $objectdata;
		}
		
		$data_buffer['item_locations'][$itemid] = GetItemDataLocation($data_buffer['main-dir'], $itemid);
		$options['stance'] = 'sit';
	}
}




// --------------------------------------------------------------------------------------------------------------------------------




// Build up info

$options['slots'] = array();
$options['name'] = 'MapleDummy';
$options['face'] = 'default';
$options['stance'] = 'stand';
$options['stance_frame'] = '0';
$options['stand_type'] = 1;
$options['tamingmob'] = 0; // 1932016 - Mechanic
$options['elven_ears'] = false;
$options['guild_name'] = 'Mapler.me';
$options['guild_emblem_fg'] = 400;
$options['guild_emblem_fgc'] = 16;
$options['guild_emblem_bg'] = 1028;
$options['guild_emblem_bgc'] = 16;




$imageoptions['width'] = 128;
$imageoptions['height'] = 128;
$imageoptions['flipped'] = false;
$imageoptions['show-name'] = false;
$imageoptions['font'] = 'arial.ttf';
$imageoptions['font_size'] = 9.25;

$data_buffer['extra_layers'] = array();
$data_buffer['body_map'] = array();
$data_buffer['zlayers'] = array();
$data_buffer['item_locations'] = array();
$data_buffer['main-dir'] = '/var/www/maplestory_images/';
if (!is_dir($data_buffer['main-dir'])) {
	$data_buffer['main-dir'] = 'P:/Result/';
}

$data_buffer['main-dir-character'] = $data_buffer['main-dir'].'Character';
$data_buffer['main-dir-guildemblem'] = $data_buffer['main-dir'].'GuildEmblem';





// Load properties

{
	$slots_input = array();
	
	// Parse code
	if (isset($_GET['code'])) {
		if (DEBUGGING) echo "Got code request!!!! > ".$_GET['code']."\r\n";
		$tmp = $_GET['code'];
		$tmp = base64_decode($tmp);
		$tmp = gzuncompress($tmp);
		
		parse_str($tmp, $tmp);
		
		$_GET = array_merge($_GET, $tmp);
	}
	
	

	if (isset($_GET['slot'])) {
		$slots_input = $_GET['slot'];
		if (DEBUGGING) echo "Got slots from URL!!!\r\n";
	}
	else {
		// Default items
		
		$slots_input[] = 1002140;
		$slots_input[] = 1042003;
		$slots_input[] = 1062007;
		$slots_input[] = 1322013;
		if (DEBUGGING) echo "Using default slots...!!!\r\n";
		
	}

	if (isset($_GET['name'])) {
		$options['name'] = substr($_GET['name'], 0, 12); // max 12 characters
	}
	if (isset($_GET['guildname'])) {
		$options['guild_name'] = substr($_GET['guildname'], 0, 12); // max 12 characters
	}
	if (isset($_GET['embleminfo'])) {
		$embleminfo = explode('.', $_GET['embleminfo']);
		if (count($embleminfo) == 4) {
			$options['guild_emblem_bg'] = intval($embleminfo[0]);
			$options['guild_emblem_bgc'] = intval($embleminfo[1]);
			$options['guild_emblem_fg'] = intval($embleminfo[2]);
			$options['guild_emblem_fgc'] = intval($embleminfo[3]);
		}
	}
	if (isset($_GET['elf']))
		$options['elven_ears'] = true;
	if (isset($_GET['tamingmob']))
		$options['tamingmob'] = intval($_GET['tamingmob']);
	if (isset($_GET['flip']))
		$imageoptions['flipped'] = true;
	if (isset($_GET['showname']))
		$imageoptions['show-name'] = true;

	foreach ($slots_input as $id => &$value) {
		$value = intval($value);
		if ($value != 0)
			$options['slots'][] = $value;
	}

	if (isset($_GET['size'])) {
		$res = 128;
		switch ($_GET['size']) {
			case 'original': $res = 96; break;
			case 'normal': $res = 128; break;
			case 'big': $res = 256; break;
			case 'huge': $res = 384; break; // 128 + 256
		}
		$imageoptions['width'] = $res;
		$imageoptions['height'] = $res;
	}
}



// Finalize properties

if ($options['stance'] == 'stand' || $options['stance'] == 'walk')
	$options['stance'] .= $options['stand_type'];

$imageoptions['mainx'] = ($imageoptions['width'] / 2) + 3;
$imageoptions['mainy'] = ($imageoptions['height'] / 2) + 18;


// Load data for items...

$used_wz_dirs = array();
$skin = 0;
$face = 0;
$hair = 0;

$prerender = array();

foreach ($options['slots'] as $slot => $itemid) {
	
	$absgroup = floor($itemid / 1000);
	if ($absgroup == 2) {
		$skin = $itemid % 1000;
		if (DEBUGGING)
			echo "Found body: ".$itemid." > ".$skin."\r\n";
		unset($options['slots'][$slot]);
	}
	elseif ($absgroup == 12) {
		$skin = $itemid % 1000;
		if (DEBUGGING)
			echo "Found head: ".$itemid." > ".$skin."\r\n";
		unset($options['slots'][$slot]);
	}
	elseif ($absgroup == 20 || $absgroup == 21) {
		$face = $itemid % 10000;
		if (DEBUGGING)
			echo "Found face: ".$itemid." > ".$face."\r\n";
		unset($options['slots'][$slot]);
	}
	elseif ($absgroup >= 30 && $absgroup < 40) {
		$hair = $itemid % 10000;
		if (DEBUGGING)
			echo "Found hair: ".$itemid." > ".$hair."\r\n";
		unset($options['slots'][$slot]);
	}
	else {
		$data = GetItemWZInfo($itemid, CURRENT_LOCALE);
		if (!isset($data['info'])) {
			if (DEBUGGING)
				echo "Skipping item ".$itemid."\r\n";
			unset($options['slots'][$slot]);
			continue;
		}
	}
	$used_wz_dirs[GetWZItemTypeName($itemid)] = $itemid;
}
// Fix up base position

$skin = 2000 + ($skin % 1000);

if ($options['tamingmob'] > 0)
	RenderTamedMob();


{
	
	// Set global position values
	
	$iteminfo = GetItemWZInfo($skin, CURRENT_LOCALE);
	$map_node = $iteminfo[$options['stance']][$options['stance_frame']]['body']['map'];

	$data_buffer['body_map']['navel'][0] = $imageoptions['mainx'] + $map_node['navel']['X'];
	$data_buffer['body_map']['navel'][1] = $imageoptions['mainy'] + $map_node['navel']['Y'];
	if (DEBUGGING)
		echo 'Did find map: navel '.$data_buffer['body_map']['navel'][0].', '.$data_buffer['body_map']['navel'][1].' > '.$map_node['navel']['X'].' - '.$map_node['navel']['Y']."\r\n";
}

// Check if main slots are used

ParseItem($skin);
ParseItem(10000 + $skin);
	
ParseItem(20000 + $face);
ParseItem(30000 + $hair);

	
// Sort equipment by ID

asort($options['slots']);


// Fix stand

foreach ($options['slots'] as $slot => $itemid) {
	$data = GetItemWZInfo($itemid, CURRENT_LOCALE);
	CheckStand(GetItemType($itemid), $data);
}


// Parse items..

foreach ($options['slots'] as $slot => $itemid) {
	if (floor($itemid / 100000) == 5)
		RenderCashItem($itemid);
	else
		ParseItem($itemid);
}

FindAndAddSetEffect();
FindAndAddItemEffect();




// --------------------------------------------------------------------------------------------------------------------------------





// Render items

krsort($data_buffer['zlayers']);

$im = imagecreatetruecolor($imageoptions['width'], $imageoptions['height']);
imagesavealpha($im, true);
$trans = imagecolorallocatealpha($im, 0, 0, 0, 127);
imagefill($im, 0, 0, $trans);

foreach ($data_buffer['zlayers'] as $zname => $objects) {
	foreach ($objects as $object) {
		$zval = $object['info']['z'];

		if ($object['category'] == 'hairOverHead' && $foundHidingCap) {
			continue;
		}
		// if ($object['stance'] == 'stand'.($stand == 1 ? 2 : 1)) continue;
		$img = $data_buffer['item_locations'][$object['itemid']].$object['image'];
		$x = $object['x'];
		$y = $object['y'];
			
			
		if (isset($object['info']['origin'])) {
			$x -= $object['info']['origin']['X'];
			$y -= $object['info']['origin']['Y'];
		}
		
		if (DEBUGGING) {
			echo 'Adding '.$object['itemid'].' -> '.$img.' at X '.$x.', Y '.$y.' --- Zname '.$zname.'  - Zmap value: '.$zval.' - '.implode(';', $object['vslot']).' - '.$object['islot']."\r\n";
		}
		DrawImage($img, $x, $y, false);
		
	}
}



// Finalize render
// Center image and render cash items, if needed

$image_width = imagesx($im);
$image_height = imagesy($im);
$result_width = $imageoptions['width'];
$result_height = $imageoptions['height'];

if (true || !DEBUGGING) {
	// Build final image
	
	$final_image = imagecreatetruecolor($result_width, $result_height);
	imagesavealpha($final_image, true);
	$trans = imagecolorallocatealpha($final_image, 0, 0, 0, 127);
	imagefill($final_image, 0, 0, $trans);
	
	// Copy created avatar onto the plane
	if ($imageoptions['flipped']) {
		$im = FlipImage($im);
	}

	$offsetx = ($result_width / 2) - ($image_width / 2);
	$offsety = ($result_height / 2) - ($image_height / 2);
	
	imagecopy($final_image, $im, 
		$offsetx, $offsety, 
		0, 0, 
		$image_width, $image_height);
	imagedestroy($im);
	$im = $final_image;
	
	if (count($data_buffer['extra_layers']) > 0) {
		$temp_image = imagecreatetruecolor($result_width, $result_height);
		imagesavealpha($temp_image, true);
		$trans = imagecolorallocatealpha($temp_image, 0, 0, 0, 127);
		imagefill($temp_image, 0, 0, $trans);
	
		if (isset($data_buffer['extra_layers'][-2])) foreach ($data_buffer['extra_layers'][-2] as $imginfo) DrawImage($imginfo[0], $imginfo[1], $imginfo[2], false, $temp_image);
		if (isset($data_buffer['extra_layers'][-1])) foreach ($data_buffer['extra_layers'][-1] as $imginfo) DrawImage($imginfo[0], $imginfo[1], $imginfo[2], false, $temp_image);
		
		imagecopy($temp_image, $im, 0, 0, 0, 0, $image_width, $result_height);
		
		if (isset($data_buffer['extra_layers'][ 1])) foreach ($data_buffer['extra_layers'][ 1] as $imginfo) DrawImage($imginfo[0], $imginfo[1], $imginfo[2], false, $temp_image);
		if (isset($data_buffer['extra_layers'][ 2])) foreach ($data_buffer['extra_layers'][ 2] as $imginfo) DrawImage($imginfo[0], $imginfo[1], $imginfo[2], false, $temp_image);
		
		$im = $temp_image;
	}
	
	
	
	// Change mainx/y
	$imageoptions['mainx'] = (imagesx($im) / 2);
	$imageoptions['mainy'] = (imagesy($im) / 2) + 18;
	
	// Render name
	if ($imageoptions['show-name'])
		RenderName($options['name'], $imageoptions['mainx'], $imageoptions['mainy'] + 20);

	if (!DEBUGGING) {
		imagepng($im);
	}
}

imagedestroy($im);