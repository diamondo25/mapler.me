<?php
require_once __DIR__.'/../inc/classes/database.php';
require_once __DIR__.'/../inc/functions.datastorage.php';
require_once __DIR__.'/caching.php';

$font = "arial.ttf";
$font_size = "9.25";

if (!isset($_GET['debug'])) {
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
$necky = $mainy + 31;
$hairshade = $character_data['skin'];
$gender = $character_data['gender'];

$ds_mark = $character_data['demonmark'];

if ($ds_mark != 0) {
	$mask = $ds_mark;
}

// Determine which items are visible
// Credit goes to zOmgnO1 for improved code

$q->free();

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
	`inventory` = 0 
ORDER BY 
	`slot` 
DESC"
);

function GetID($row) {
	$itemid = $row['itemid'];
	if ($row['display_id'] > 0) {
		$itemid -= $itemid % 10000;
		$itemid += $row['display_id'];
	}
	return $itemid;
}

while ($row2 = $character_equipment->fetch_assoc()) {
	switch ($row2['slot']) {
		case -1:	// Hat
		case -101:	// NX Hat
			if ($row2['itemid'] != 1002186) // Invisible Hat
				$hat = GetID($row2);
			else
				unset($hat);
			break;
		case -2:	// Face Accessory
		case -102:	// NX Face Accessory
			$mask = GetID($row2);
			break;
		case -3:	// Eye Accessory
		case -103:	// NX Eye Accessory
			$eyes = GetID($row2);
			break;
		case -4:	// Earrings
		case -104:	// NX Earrings
			$ears = GetID($row2);
			break;
		case -5:	// Top | Overall
		case -105:	// NX Top | Overall
			if (substr($row2['itemid'], 0, 3) == "105") {
				$overall = GetID($row2);
				$top = NULL;
			}
			else {
				$top = GetID($row2);
				$overall = NULL;
			}
			break;
		case -6:	// Bottom
		case -106:	// NX Bottom
			if (!isset($overall))
				$pants = GetID($row2);
			break;
		case -7:	// Shoes
		case -107:	// NX Shoes
			$shoe = GetID($row2);
			break;
		case -8:	// Gloves
		case -108:	// NX Gloves
			$glove = GetID($row2);
			break;
		case -9:	// Cape
		case -109:	// NX Cape
			$cape = GetID($row2);
			break;
		case -10:	// Shield
		case -110:	// NX Shield
			if (floor($row2['itemid'] / 100) != 13527) { // Bullet for Mech
				$shield = GetID($row2);
			}
			break;
		case -11:	// Weapon
			$wep = GetID($row2);
			break;
		case -111:	// NX Weapon
			$nxwep = GetID($row2);
			break;
	}
}

$character_equipment->free();

if (isset($_GET['bg'])) {
	$bgid = intval($_GET['bg']);
	$bgname = '';
	switch ($bgid) {
		case 0: $bgname = 'fm'; break;
		case 1: $bgname = 'kerning'; break;
		case 2: $bgname = 'kerning_hideout'; break;
		case 3: $bgname = 'monsterpark'; break;
		case 4: $bgname = 'ardentmill'; break;
		default: $bgname = 'fm'; break;
	}
	add_image(__DIR__.'/../inc/img/avatar_backgrounds/'.$bgname.'.png', 0, 0);
}


// This section determines which stand to use based on the weapon you have
// Credit goes to zOmgnO1 for improved code
if ($wep) {
	$wepType = (int) substr($wep, 0, 3);
	switch ($wepType) {
		case 130:	// 1-Handed Sword
		case 131:	// 1-Handed Axe
		case 132:	// 1-Handed BW
		case 133:	// Dagger
		case 137:	// Wand
		case 138:	// Staff
		case 139:	// ?? Unknown
		case 145:	// Bow
		case 147:	// Claw
		case 148:	// Knucle
		case 149:	// Gun
			$stand = 1;
			break;
		case 140:	// 2-Handed Sword
		case 141:	// 2-Handed Axe
		case 142:	// 2-Handed BW
		case 143:	// Spear
		case 146:	// Crossbow
			$stand = 2;
			break;
		case 144:	// Pole Arm
			$location = "Weapon/0" . $wep . ".img/stand1.0.weapon.png";
			if (file_exists($location)) // Snowboards are stand = 1
				$stand = 1;
			else
				$stand = 2;
			break;
		default:
			$stand = 1;
			break;
	}
}
else {
	$stand = 1;
}

// I have no idea what this does
// Credit goes to zOmgnO1 for improved code
if (isset($nxwep)) {
	$nxwep = $wep;
	/*
	$wepnum = 0;
	for ($i = 29; $i <= 49; $i++) {
		$location = $characterwz."/Weapon/0" . $nxwep . ".img/" . $i . ".stand" . $stand . ".0.weapon.png";
		if (file_exists($location)) {
			$wepnum = $i;
			break;
		}
	}
	*/
}
else {
	$nxwep = $wep;
}
$vslot = "";


//$faces = array("hot"); // array("angry", "bewildered", "blaze", "bowing", "cheers", "chu", "cry", "dam", "despair", "glitter", "hit", "hot", "love");
$faces = array('default');

// Create face
$chosenface = $faces[rand(0, count($faces) - 1)];
$chosenface_info = $chosenface == 'default' ? $chosenface : $chosenface.'_0';
$chosenface_img = $chosenface == 'default' ? $chosenface : $chosenface.'.0';

// Coordinates for the face
if (isset($face)) {
	$facearray = get_data($face);
	$facex = -$facearray[$chosenface_info.'_face_origin_X'] - $facearray[$chosenface_info.'_face_map_brow_X'];
	$facey = -$facearray[$chosenface_info.'_face_origin_Y'] - $facearray[$chosenface_info.'_face_map_brow_Y'];
}

// Coordinates for the hair
if (isset($hair)) {
	$hairarray = get_data($hair);
	if (isset($hairarray['stand'.$stand.'_0_hairBelowBody_origin_X'])) {
		$backhairx = -$hairarray['stand'.$stand.'_0_hairBelowBody_origin_X'] - $hairarray['stand'.$stand.'_0_hairBelowBody_map_brow_X'];
		$backhairy = -$hairarray['stand'.$stand.'_0_hairBelowBody_origin_Y'] - $hairarray['stand'.$stand.'_0_hairBelowBody_map_brow_Y'];
	}
	else {
		$backhairx = $backhairy = 0;
	}
	$shadehairx = -$hairarray['stand'.$stand.'_0_hairShade_0_origin_X'] - $hairarray['stand'.$stand.'_0_hairShade_0_map_brow_X'];
	$shadehairy = -$hairarray['stand'.$stand.'_0_hairShade_0_origin_Y'] - $hairarray['stand'.$stand.'_0_hairShade_0_map_brow_Y'];
	$hairx = -$hairarray['stand'.$stand.'_0_hair_origin_X'] - $hairarray['stand'.$stand.'_0_hair_map_brow_X'];
	$hairy = -$hairarray['stand'.$stand.'_0_hair_origin_Y'] - $hairarray['stand'.$stand.'_0_hair_map_brow_Y'];
	$overhairx = -$hairarray['stand'.$stand.'_0_hairOverHead_origin_X'] - $hairarray['stand'.$stand.'_0_hairOverHead_map_brow_X'];
	$overhairy = -$hairarray['stand'.$stand.'_0_hairOverHead_origin_Y'] - $hairarray['stand'.$stand.'_0_hairOverHead_map_brow_Y'];
}

// Eyes
if (isset($eyes)) {
	$eyesarray = get_data($eyes);
	$eyesx = -$eyesarray[$chosenface_info.'_default_origin_X'] - $eyesarray[$chosenface_info.'_default_map_brow_X'];
	$eyesy = -$eyesarray[$chosenface_info.'_default_origin_Y'] - $eyesarray[$chosenface_info.'_default_map_brow_Y'];
	$eyesz = $eyesarray[$chosenface_info.'_default_z'];
}

// Mask
if (isset($mask)) {
	$maskarray = get_data($mask);
	$maskz = $maskarray['default_default_z'];
	$maskx = -$maskarray['default_default_origin_X'] - $maskarray['default_default_map_brow_X'];
	$masky = -$maskarray['default_default_origin_Y'] - $maskarray['default_default_map_brow_Y'];
}

// Ears
if (isset($ears)) {
	$earsarray = get_data($ears);
	$earsx = -$earsarray['default_default_origin_X'] - $earsarray['default_default_map_brow_X'];
	$earsy = -$earsarray['default_default_origin_Y'] - $earsarray['default_default_map_brow_Y'];
}

// Hat
if (isset($hat)) {
	$hatarray = get_data($hat);
	$vslot = $hatarray['info_vslot'];
	$hatx = -$hatarray['default_default_origin_X'] - $hatarray['default_default_map_brow_X'];
	$haty = -$hatarray['default_default_origin_Y'] - $hatarray['default_default_map_brow_Y'];
	if (isset($hatarray['default_defaultAc_origin_X'])) {
		$zhatx = -$hatarray['default_defaultAc_origin_X'] - $hatarray['default_defaultAc_map_brow_X'];
		$zhaty = -$hatarray['default_defaultAc_origin_Y'] - $hatarray['default_defaultAc_map_brow_Y'];
	}
	else {
		$zhatx = $zhaty = 0;
	}
	if (isset($hatarray['default_defaultBelowBody_origin_X'])) {
		$bbhatx = -$hatarray['default_defaultBelowBody_origin_X'] - $hatarray['default_defaultBelowBody_map_brow_X'];
		$bbhaty = -$hatarray['default_defaultBelowBody_origin_Y'] - $hatarray['default_defaultBelowBody_map_brow_Y'];
	}
	else {
		$bbhatx = $bbhaty = 0;
	}
}

// Cape
if (isset($cape)) {
	$capearray = get_data($cape);
	if (!isset($capearray['stand1_0_cape_origin_X'])) {
		unset($cape);
	}
	else {
		if ($stand == 2) {
			$cape2x = -$capearray['stand2_0_cape_origin_X'] - $capearray['stand2_0_cape_map_navel_X'];
			$cape2y = -$capearray['stand2_0_cape_origin_Y'] - $capearray['stand2_0_cape_map_navel_Y'];
		}
		else {
			$cape2x = $cape2y = 0;
		}
		$capex = -$capearray['stand1_0_cape_origin_X'] - $capearray['stand1_0_cape_map_navel_X'];
		$capey = -$capearray['stand1_0_cape_origin_Y'] - $capearray['stand1_0_cape_map_navel_Y'];
		$capez = $capearray['stand1_0_cape_z'];
		if (isset($capearray['stand'.$stand.'_0_capeArm_origin_X'])) {
			$zcapex = -$capearray['stand'.$stand.'_0_capeArm_origin_X'] - $capearray['stand'.$stand.'_0_capeArm_map_navel_X'];
			$zcapey = -$capearray['stand'.$stand.'_0_capeArm_origin_Y'] - $capearray['stand'.$stand.'_0_capeArm_map_navel_Y'];
		}
		else {
			$zcapex = $zcapey = 0;
		}
	}
}

// Shield
if (isset($shield)) {
	$shieldarray = get_data($shield);
	$shieldx = -$shieldarray['stand1_0_shield_origin_X']-$shieldarray['stand1_0_shield_map_navel_X'];
	$shieldy = -$shieldarray['stand1_0_shield_origin_Y']-$shieldarray['stand1_0_shield_map_navel_Y'];
}

// Shoes
if (isset($shoe)) {
	$shoesarray = get_data($shoe);
	$shoesx = -$shoesarray['stand1_0_shoes_origin_X'] - $shoesarray['stand1_0_shoes_map_navel_X'];
	$shoesy = -$shoesarray['stand1_0_shoes_origin_Y'] - $shoesarray['stand1_0_shoes_map_navel_Y'];
	$shoesz = $shoesarray['stand1_0_shoes_z'];
}

// Glove
if (isset($glove)) {
	$glovearray = get_data($glove);
	if ($stand == 1) {
		$lglove1x = -$glovearray['stand1_0_lGlove_origin_X'] - $glovearray['stand1_0_lGlove_map_navel_X'];
		$lglove1y = -$glovearray['stand1_0_lGlove_origin_Y'] - $glovearray['stand1_0_lGlove_map_navel_Y'];
		$rglove1x = -$glovearray['stand1_0_rGlove_origin_X'] - $glovearray['stand1_0_rGlove_map_navel_X'];
		$rglove1y = -$glovearray['stand1_0_rGlove_origin_Y'] - $glovearray['stand1_0_rGlove_map_navel_Y'];
	}
	elseif ($stand == 2) {
		$lglove2x = -$glovearray['stand2_0_lGlove_origin_X'] - $glovearray['stand2_0_lGlove_map_navel_X'];
		$lglove2y = -$glovearray['stand2_0_lGlove_origin_Y'] - $glovearray['stand2_0_lGlove_map_navel_Y'];
		$rglove2x = -$glovearray['stand2_0_rGlove_origin_X'] - $glovearray['stand2_0_rGlove_map_navel_X'];
		$rglove2y = -$glovearray['stand2_0_rGlove_origin_Y'] - $glovearray['stand2_0_rGlove_map_navel_Y'];
	}
}

// Pants
if (isset($pants)) {
	$pantsarray = get_data($pants);
	if($stand == 2) {
		$pants2x = -$pantsarray['stand2_0_pants_origin_X'] - $pantsarray['stand2_0_pants_map_navel_X'];
		$pants2y = -$pantsarray['stand2_0_pants_origin_Y'] - $pantsarray['stand2_0_pants_map_navel_Y'];
	}
	else {
		$pants2x = $pants2y = 0;
	}
	$pantsx = -$pantsarray['stand1_0_pants_origin_X'] - $pantsarray['stand1_0_pants_map_navel_X'];
	$pantsy = -$pantsarray['stand1_0_pants_origin_Y'] - $pantsarray['stand1_0_pants_map_navel_Y'];	
	$pantsz = $pantsarray['stand1_0_pants_z'];
}

// Top
if (isset($top)) {
	$mailarray = get_data($top);
	if($stand == 2) {
		$mail2x = -$mailarray['stand2_0_mail_origin_X'] - $mailarray['stand2_0_mail_map_navel_X'];
		$mail2y = -$mailarray['stand2_0_mail_origin_Y'] - $mailarray['stand2_0_mail_map_navel_Y'];
		$maila2x = -$mailarray['stand2_0_mailArm_origin_X'] - $mailarray['stand2_0_mailArm_map_navel_X'];
		$maila2y = -$mailarray['stand2_0_mailArm_origin_Y'] - $mailarray['stand2_0_mailArm_map_navel_Y'];
	}
	$mailx = -$mailarray['stand1_0_mail_origin_X'] - $mailarray['stand1_0_mail_map_navel_X'];
	$maily = -$mailarray['stand1_0_mail_origin_Y'] - $mailarray['stand1_0_mail_map_navel_Y'];
	$mailax = -$mailarray['stand1_0_mailArm_origin_X'] - $mailarray['stand1_0_mailArm_map_navel_X'];
	$mailay = -$mailarray['stand1_0_mailArm_origin_Y'] - $mailarray['stand1_0_mailArm_map_navel_Y'];
}

// Overall
if (isset($overall)) {
	$mailarray = get_data($overall);
	if($stand == 2) {
		$mail2x = -$mailarray['stand2_0_mail_origin_X'] - $mailarray['stand2_0_mail_map_navel_X'];
		$mail2y = -$mailarray['stand2_0_mail_origin_Y'] - $mailarray['stand2_0_mail_map_navel_Y'];
		$maila2x = -$mailarray['stand2_0_mailArm_origin_X'] - $mailarray['stand2_0_mailArm_map_navel_X'];
		$maila2y = -$mailarray['stand2_0_mailArm_origin_Y'] - $mailarray['stand2_0_mailArm_map_navel_Y'];
	}
	$mailx = -$mailarray['stand1_0_mail_origin_X'] - $mailarray['stand1_0_mail_map_navel_X'];
	$maily = -$mailarray['stand1_0_mail_origin_Y'] - $mailarray['stand1_0_mail_map_navel_Y'];
	$mailz = $mailarray['stand1_0_mail_z'];
	$mailax = -$mailarray['stand1_0_mailArm_origin_X'] - $mailarray['stand1_0_mailArm_map_navel_X'];
	$mailay = -$mailarray['stand1_0_mailArm_origin_Y'] - $mailarray['stand1_0_mailArm_map_navel_Y'];
}

// Weapon
if (isset($nxwep)) {
	$weaponarray = get_data($nxwep);
	$wepx = $mainx + 12;
	$wepy = $necky + 6;
	$key = isset($wepnum) ? ($wepnum . '_stand' . $stand) : ('stand' . $stand);
	if (isset($weaponarray[$key.'_0_weapon_map_hand_X']))
		$position = 'hand';
	else {
		$position = 'navel';
		$wepx = $mainx;
		$wepy = $necky;
	}			
	if (isset($wepnum)) {
		$weaponz = $weaponarray[$wepnum.'_stand'.$stand.'_0_weapon_z'];
		$weaponx = -$weaponarray[$wepnum.'_stand'.$stand.'_0_weapon_origin_X'] - $weaponarray[$wepnum.'_stand'.$stand.'_0_weapon_map_'.$position.'_X'];
		$weapony = -$weaponarray[$wepnum.'_stand'.$stand.'_0_weapon_origin_Y'] - $weaponarray[$wepnum.'_stand'.$stand.'_0_weapon_map_'.$position.'_Y'];
	}
	else {
		$weaponz = $weaponarray['stand'.$stand.'_0_weapon_z'];
		$weaponx = -$weaponarray['stand'.$stand.'_0_weapon_origin_X'] - $weaponarray['stand'.$stand.'_0_weapon_map_'.$position.'_X'];
		$weapony = -$weaponarray['stand'.$stand.'_0_weapon_origin_Y'] - $weaponarray['stand'.$stand.'_0_weapon_map_'.$position.'_Y'];
	}
	if($stand == 2 && $position == 'hand') {
		$wepx -= 7;
		$wepy -= 7;
	}
}

// Shoe stuff
$positions = array(
	"shoesTop",
	"mailChestOverHighest",			
	"pantsOverMailChest",
	"mailChest",
	"pantsOverShoesBelowMailChest",
	"shoesOverPants",
	"mailChestOverPants",
	"pants",
	"pantsBelowShoes",
	"shoes"
);
$other = isset($pants) ? $pantsz : $mailz;
$shoe_pos = array_search($shoesz, $positions);
$other_pos = array_search($other, $positions);

// Create weaponBelowBody
if($weaponz == 'weaponBelowBody') {
	if($wepnum)
		$wep_location = $characterwz."/Weapon/0".$nxwep.".img/".$wepnum.".stand".$stand.".0.weapon.png";
	else
		$wep_location = $characterwz."/Weapon/0".$nxwep.".img/stand".$stand.".0.weapon.png";
	add_image($wep_location, $wepx + $weaponx, $wepy + $weapony);
}

// Create top cap
if (isset($hat)) {
	$cap_location = $characterwz."/Cap/0".$hat.".img/default.default.defaultBelowBody.png";
	add_image($cap_location, $mainx + $bbhatx, $mainy + $bbhaty);
	
	$cap_location = $characterwz."/Cap/0".$hat.".img/default.defaultAc.png";
	add_image($cap_location, $mainx + $zhatx, $mainy + $zhaty);
}

// Create back hair and cape
if (isset($capez) && $capez == 'capeBelowBody' && (substr_count($vslot, 'H1H2H3H4H5H6') != 1)) {
	$bhair_location = $characterwz."/Hair/000".$hair.".img/default.hairBelowBody.png";
	add_image($bhair_location, $mainx + $backhairx, $mainy + $backhairy);
}

if (isset($cape) && file_exists($characterwz."/Cape/0".$cape.".img/stand2.0.cape.png") && $stand == 2)
	add_image($characterwz."/Cape/0".$cape.".img/stand2.0.cape.png", $mainx + $cape2x, $necky + $cape2y);

elseif (isset($cape) && file_exists($characterwz."/Cape/0".$cape.".img/stand1.0.cape.png"))
	add_image($characterwz."/Cape/0".$cape.".img/stand1.0.cape.png", $mainx + $capex, $necky + $capey);

if (((isset($capez) && $capez != 'capeBelowBody') || true) && (substr_count($vslot, 'H1H2H3H4H5H6') != 1)) {
	$bhair_location = $characterwz."/Hair/000".$hair.".img/default.hairBelowBody.png";
	add_image($bhair_location, $mainx + $backhairx, $mainy + $backhairy);
}

// Create shield
if (isset($shield)) {
	$shield_location = $characterwz."/Shield/0".$shield.".img/stand1.0.shield.png";
	add_image($shield_location, $mainx + $shieldx, $necky + $shieldy);
}

// Create body 
if($stand == "")
	$stand = 1;
add_image($characterwz."/0000".$skin.".img/stand".$stand.".0.body.png", ($mainx + $stand) - 9, $mainy + 21);

// Create shoes: under
if (isset($shoe)) {
	$shoe_location = $characterwz."/Shoes/0" . $shoe . ".img/stand1.0.shoes.png";
	if ($shoe_pos > $other_pos) {
		add_image($shoe_location, $mainx + $shoesx, $necky + $shoesy);
	}
}

// Create stand1 left glove
if (isset($glove) && $stand == 1){
	$glove_location = $characterwz."/Glove/0".$glove.".img/stand1.0.lGlove.png";
	add_image($glove_location, $mainx + $lglove1x, $necky + $lglove1y);
}

// Clothe the naked
if(!isset($pants) || !isset($overall)) {
	if ($gender == 0)
		add_image($characterwz."/Pants/01060026.img/stand1.0.pants.png", $mainx - 3, $necky + 1);
	elseif ($gender == 1) 
		add_image($characterwz."/Pants/01061039.img/stand1.0.pants.png", $mainx - 3, $necky + 1);
}

// Create pants
if (isset($pants)) {
	$pants_location = $characterwz."/Pants/0".$pants.".img/stand";
	if(file_exists($pants_location."2.0.pants.png") && $stand == 2)
		add_image($pants_location."2.0.pants.png", $mainx + $pants2x, $necky + $pants2y);
	elseif(file_exists($pants_location."1.0.pants.png"))
		add_image($pants_location."1.0.pants.png", $mainx + $pantsx, $necky + $pantsy);
}	

// Create top
if (isset($top)) {
	$top_location = $characterwz."/Coat/0".$top.".img/stand";
	if(file_exists($top_location."2.0.mail.png") && $stand == 2)
		add_image($top_location."2.0.mail.png", $mainx + $mail2x, $necky + $mail2y);
	elseif(file_exists($top_location."1.0.mail.png"))
		add_image($top_location."1.0.mail.png", $mainx + $mailx, $necky + $maily);
}

// Create overall
if (isset($overall)) {
	$overall_location = $characterwz."/Longcoat/0".$overall.".img/stand";
	if(file_exists($overall_location."2.0.mail.png") && $stand == 2)
		add_image($overall_location."2.0.mail.png", $mainx + $mail2x, $necky + $mail2y);
	elseif(file_exists($overall_location."1.0.mail.png"))
		add_image($overall_location."1.0.mail.png", $mainx + $mailx, $necky + $maily);
}

// Create shoes
if (isset($shoe)) {
	if ($shoe_pos < $other_pos) {
		$shoe_location = $characterwz."/Shoes/0".$shoe.".img/stand1.0.shoes.png";
		add_image($shoe_location, $mainx + $shoesx, $necky + $shoesy);
	}
}

// Clothe the naked
if(!($top || $overall)) {
	if ($gender == 0)
		add_image($characterwz."/Coat/01040036.img/stand1.0.mail.png", $mainx - 3, $necky - 9);
	elseif ($gender == 1)
		add_image($characterwz."/Coat/01041046.img/stand1.0.mail.png", $mainx - 3, $necky - 9);
}

// Create armBelowHeadOverMailChest
if($weaponz == 'armBelowHeadOverMailChest') {
	if($wepnum)
		$wep_location = $characterwz."/Weapon/0".$nxwep.".img/".$wepnum.".stand".$stand.".0.weapon.png";
	else
		$wep_location = $characterwz."/Weapon/0".$nxwep.".img/stand".$stand.".0.weapon.png";
	add_image($wep_location, $wepx + $weaponx, $wepy + $weapony);
}

// Create capeArm
if (isset($cape) && file_exists($characterwz."/Cape/0".$cape.".img/stand" . $stand . ".0.capeArm.png"))
	add_image($characterwz."/Cape/0".$cape.".img/stand" . $stand . ".0.capeArm.png", $mainx + $zcapex, $necky + $zcapey);
	
// Create head
$head = $skin + 10000;
add_image($characterwz."/000".$head.".img/front.head.png", $mainx - 15, $mainy - 12);

// Create earring
if (isset($ears)) {
	$ear_location = $characterwz."/Accessory/0".$ears.".img/default.default.png";
	add_image($ear_location, $mainx + $earsx, $mainy + $earsy);
}

// Create shade hair
if (substr_count($vslot, 'H1H2H3H4H5H6') != 1) {
	$shair_location = $characterwz."/Hair/000".$hair.".img/default.hairShade.".$hairshade.".png";
	add_image($shair_location, $mainx + $shadehairx, $mainy + $shadehairy);
}

// Create mask
if (isset($mask) && $maskz == "accessoryFaceBelowFace") {
	$mask_location = $characterwz."/Accessory/0".$mask.".img/".$chosenface_img.".default.png";
	add_image($mask_location, $mainx + $maskx, $mainy + $masky);
}

// Create face
$face_location = $characterwz."/Face/000".$face.".img/".$chosenface_img.".face.png";
add_image($face_location, $mainx + $facex, $mainy + $facey);

// Create mask
if (isset($maskz) && $maskz == "accessoryFace") {
	$mask_location = $characterwz."/Accessory/0".$mask.".img/".$chosenface_img.".default.png";
	add_image($mask_location, $mainx + $maskx, $mainy + $masky);
}

// Create eyes item
if (isset($eyes) && $eyesz == "accessoryEye") {
	$eyes_location = $characterwz."/Accessory/0".$eyes.".img/".$chosenface_img.".default.png";
	add_image($eyes_location, $mainx + $eyesx, $mainy + $eyesy);
}

// Create hair
if (isset($hair) && (substr_count($vslot, 'H1H2H3H4H5H6') != 1)) {
	$hair_location = $characterwz."/Hair/000".$hair.".img/default.hair.png";
	add_image($hair_location, $mainx + $hairx, $mainy + $hairy);
}

// Create accessoryFaceOverFaceBelowCap
if (isset($maskz) && $maskz == "accessoryFaceOverFaceBelowCap") {
	$mask_location = $characterwz."/Accessory/0".$mask.".img/".$chosenface_img.".default.png";
	add_image($mask_location, $mainx + $maskx, $mainy + $masky);
}

// Create hairoverhead or hat
if (isset($hat) && ($vslot == "Cp" || $vslot == "CpH5")) {
	$hat_location = $characterwz."/Hair/000".$hair.".img/default.hairOverHead.png";
	add_image($hat_location, $mainx + $overhairx, $mainy + $overhairy);
}

// Create hat
if (isset($hat)) {
	$cap_location = $characterwz."/Cap/0".$hat.".img/default.default.png";
	add_image($cap_location, $mainx + $hatx, $mainy + $haty);
}
else {
	// Create top hair
	$thair_location = $characterwz."/Hair/000".$hair.".img/default.hairOverHead.png";
	add_image($thair_location, $mainx + $overhairx, $mainy + $overhairy);
}

// Create accessoryEyeOverCap
if (isset($eyes) && $eyesz == "accessoryEyeOverCap") {
	$eyes_location = $characterwz."/Accessory/0".$eyes.".img/default.default.png";
	add_image($eyes_location, $mainx + $eyesx, $mainy + $eyesy);
}

// Create weapon - stand 1
if ($weaponz == 'weapon' && $stand == 1) {
	if (isset($wepnum))
		$wep_location = $characterwz."/Weapon/0".$nxwep.".img/".$wepnum.".stand".$stand.".0.weapon.png";
	else
		$wep_location = $characterwz."/Weapon/0".$nxwep.".img/stand".$stand.".0.weapon.png";
	add_image($wep_location, $wepx + $weaponx, $wepy + $weapony);
}

// Create arm
$arm_location = $characterwz."/0000".$skin.".img/stand".$stand.".0.arm.png";
add_image($arm_location, $mainx + (($stand == 1) ? 8 : 4), $mainy + 23);

// create coatArm - top
if (isset($top)) {
	$coatarm_location = $characterwz."/Coat/0".$top.".img/stand" . $stand . ".0.mailArm.png";
	add_image($coatarm_location, $mainx + (($stand == 1) ? $mailax : $maila2x), $necky + (($stand == 1) ? $mailay : $maila2y));
}

// create coatArm - overall
if (isset($overall)) {
	$coatarm_location = $characterwz."/Longcoat/0".$overall.".img/stand" . $stand . ".0.mailArm.png";
	add_image($coatarm_location, $mainx + (($stand == 1) ? $mailax : $maila2x), $necky + (($stand == 1) ? $mailay : $maila2y));
}

// Create weaponOverArm
if($weaponz == 'weaponOverArm') {	
	if($wepnum)
		$wep_location = $characterwz."/Weapon/0".$nxwep.".img/".$wepnum.".stand".$stand.".0.weapon.png";
	else
		$wep_location = $characterwz."/Weapon/0".$nxwep.".img/stand".$stand.".0.weapon.png";
	add_image($wep_location, $wepx + $weaponx, $wepy + $weapony);
}

// Create hand2
if ($stand == 2) {
	$hand2_location = $characterwz."/0000".$skin.".img/stand2.0.hand.png";
	add_image($hand2_location, $mainx - 10, $mainy + 26);
}

if ((isset($glove)) && $stand == 2) {
	// Create lglove2
	$lglove2_location = $characterwz."/Glove/0".$glove.".img/stand2.0.lGlove.png";
	add_image($lglove2_location, $mainx + $lglove2x, $necky + $lglove2y);
	
	// Create rglove2		
	$rglove2_location = $characterwz."/Glove/0".$glove.".img/stand2.0.rGlove.png";
	add_image($lglove2_location, $mainx + $rglove2x, $necky + $rglove2y);
}

if ((isset($glove)) && $stand == 1) {
	//create rglove1
	$rglove1_location = $characterwz."/Glove/0".$glove.".img/stand1.0.rGlove.png";
	add_image($rglove1_location, $mainx + $rglove1x, $necky + $rglove1y);
}

// Create weaponOverGlove
if($weaponz == 'weaponOverGlove' || $weaponz == 'weaponOverHand') {
	if (isset($wepnum))
		$wep_location = $characterwz."/Weapon/0".$nxwep.".img/".$wepnum.".stand".$stand.".0.weapon.png";
	else
		$wep_location = $characterwz."/Weapon/0".$nxwep.".img/stand".$stand.".0.weapon.png";
	add_image($wep_location, $wepx + $weaponx, $wepy + $weapony);
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
		$image = imagecreatefrompng($location);
		imagecopy($im, $image, $x, $y, 0, 0, imagesx($image), imagesy($image));
	}
	elseif (isset($_GET['debug'])) {
		echo "-- Could not find ".$location." -- <br />";
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
				add_image($guild_info_location.'/BackGround/0000'.$res[1].'/'.$res[2].'.png', $startWidth - 18, $y + 1);
			}
			if ($res[3] != 0 || $res[4] != 0) {
				$name = "";
				$sort = floor($res[3] / 1000);
				if ($sort == 2) $name = "Animal";
				elseif ($sort == 3) $name = "Plant";
				elseif ($sort == 4) $name = "Pattern";
				elseif ($sort == 5) $name = "Letter";
				elseif ($sort == 9) $name = "Etc";
				add_image($guild_info_location.'/Mark/'.$name.'/0000'.$res[3].'/'.$res[4].'.png', $startWidth - 17, $y + 2);
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