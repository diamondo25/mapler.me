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

// Create blank template
$im = imagecreatetruecolor($image_width, $image_height);
imagesavealpha($im, true);
$trans = imagecolorallocatealpha($im, 0, 0, 0, 127);
imagefill($im, 0, 0, $trans);


$main_dir = '/var/www/maplestory_images/';
if (!is_dir($main_dir)) {
	$main_dir = 'P:/Result/';
	if (!is_dir($main_dir)) {
		// your call
	}
}

$characterwz = $main_dir.'Character';
$guild_info_location = $main_dir.'GuildEmblem';

$guildname = $_GET['guild'];
$worldname = $_GET['world'];

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
	$q = $__database->query("
SELECT
	world_data.world_name,
	guilds.*,
	guilds.name,
	guilds.emblem_bg,
	guilds.emblem_bg_color,
	guilds.emblem_fg,
	guilds.emblem_fg_color
FROM
	`guilds`
LEFT JOIN 
	`world_data`
	ON
		world_data.world_id = guilds.world_id
WHERE
	guilds.name = '".$__database->real_escape_string($guildname)."'
AND
	world_data.world_name = '".$__database->real_escape_string($worldname)."'");
	
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