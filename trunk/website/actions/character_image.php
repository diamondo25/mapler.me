<?php
$debug = isset($_GET['debug']);
$font = "arial.ttf";
$font_size = "9.25";

function LoadGif($imgname)
{
    /* Attempt to open */
    $im = @imagecreatefromgif($imgname);

    /* See if it failed */
    if(!$im)
    {
        /* Create a blank image */
        $im = imagecreatetruecolor (96, 96);
        $bgc = imagecolorallocate ($im, 255, 255, 255);
        $tc = imagecolorallocate ($im, 0, 0, 0);

        imagefilledrectangle ($im, 0, 0, 96, 96, $bgc);

        /* Output an error message */
        imagestring ($im, 1, 5, 5, 'Mapler.me failed.', $tc);
        imagestring ($im, 1, 5, 20, $imgname, $tc);
    }

    return $im;
}

function LoadPNG($imgname)
{
    /* Attempt to open */
    $im = @imagecreatefrompng($imgname);

    /* See if it failed */
    if(!$im)
    {
        /* Create a blank image */
        $im = imagecreatetruecolor (96, 96);
        $bgc = imagecolorallocate ($im, 255, 255, 255);
        $tc = imagecolorallocate ($im, 0, 0, 0);

        imagefilledrectangle ($im, 0, 0, 96, 96, $bgc);

        /* Output an error message */
        imagestring ($im, 1, 5, 5, 'Mapler.me failed.', $tc);
        imagestring ($im, 1, 5, 20, $imgname, $tc);
    }

    return $im;
}

function calculateWidth($name) {
	global $font;
	global $font_size;
	$width = 7;
	$bbox = imagettfbbox($font_size, 0, $font, $name);
	$width += abs($bbox[4] - $bbox[0]);
	return $width;
}

function imagefillroundedrect($im, $x, $y, $cx, $cy, $rad, $col) {
	imagefilledrectangle($im,$x,$y+$rad,$cx,$cy-$rad,$col);
	imagefilledrectangle($im,$x+$rad,$y,$cx-$rad,$cy,$col);
	$dia = $rad*2;
	imagefilledellipse($im, $x+$rad, $y+$rad, $rad*2, $dia, $col);
	imagefilledellipse($im, $x+$rad, $cy-$rad, $rad*2, $dia, $col);
	imagefilledellipse($im, $cx-$rad, $cy-$rad, $rad*2, $dia, $col);
	imagefilledellipse($im, $cx-$rad, $y+$rad, $rad*2, $dia, $col);
}


$charname = isset($_GET['name']) ? $_GET['name'] : 'ixdiamondoz';

if (!$debug)
	header('Content-Type: image/png');

$len = strlen($charname);
if ($len < 4 || $len > 12) {
	$im = imagecreatetruecolor (271, 162);
	$bgc = imagecolorallocate ($im, 255, 255, 255);
	$tc = imagecolorallocate ($im, 0, 0, 0);

	imagefilledrectangle ($im, 0, 0, 271, 162, $bgc);

	/* Output an error message */
	imagestring ($im, 1, 5, 5, 'I AM ERROR', $tc);
	imagestring ($im, 1, 5, 20, "Invalid Char Name", $tc);
	imagepng($im);
	imagedestroy($im);
	die();
}


mysql_connect("127.0.0.1", "maplestats", "maplederp") or die("MYSQL ERROR: ".mysql_error());
mysql_select_db("maplestats") or die("MYSQL ERROR: ".mysql_error());

$q2 = mysql_query("SELECT id FROM cache WHERE charactername = '".mysql_real_escape_string($charname)."' AND type = 'info' AND DATE_ADD(`added`, INTERVAL 1 DAY) >= NOW()");
if (mysql_num_rows($q2) == 1) {
	$row = mysql_fetch_assoc($q2);
	readfile('../cache/'.$row['id'].'.png');
	die();
}
$id = uniqid();

$_HERP = true;
include('get_char_info.php');
$json_data = json_decode($json_data, true);

if ($debug)
	print_r($json_data);

$ok = !isset($json_data["error"]);
if (!$ok) {
	$json_data["images"]["Character"] = "img/no-character.gif";
	$got_pet = false;
}
else {
	$got_pet = strpos($json_data["images"]["Pet"], "HHJLHDFFEFDGELLNFOPOKJFPLJIJIOFJGKBFIENAOLFLCACMGPEDJLOKHBLCGMBN") == FALSE;
}

$image = imagecreatetruecolor(271, 162);
imagealphablending($image, false);
imagesavealpha($image, true);

$bg_image = imagecreatefrompng("img/char_bg.png");
imagecopyresampled($image, $bg_image, 0, 0, 0, 0, 271, 386, 271, 386);
imagealphablending($image, true);

// LOAD CHARACTER

$charpos_x = 10;
$charpos_y = 20;

if ($got_pet) {
	// LOAD PET
	$pet_image = LoadGif($json_data["images"]["Pet"]);

	imagecopymerge($image, $pet_image, $charpos_x, $charpos_y, 0, 0, 96, 96, 100);
	
	//$petname = "DERP!";
	//$startWidth = ($charpos_x + (96 / 2)) - calculateWidth($petname) / 2;
	//$endWidth = ($charpos_x + (96 / 2)) + calculateWidth($petname) / 2;
	//ImageTTFText($image, 9, 0, $startWidth, ($charpos_y + 96 + 190), imagecolorallocate($image, 255, 255, 255), $font, $petname);
}

$character_image = LoadGif($json_data["images"]["Character"]);

imagecopymerge($image, $character_image, $charpos_x, $charpos_y, 0, 0, 96, 96, 100);

// SET NAMETAG
$name = $ok ? $json_data["name"] : $charname;

$x = ($charpos_x + (96 / 2));
$y = ($charpos_y + 10 + 96);

$startWidth = $x - calculateWidth($name) / 2;
$endWidth = $x + calculateWidth($name) / 2;

ImageTTFText($image, $font_size, 0, $startWidth + 3, $y - 5, imagecolorallocate($image, 255, 255, 255), $font, $name);

$base_x = 152;
$base_y = 55;
$step = 18;

ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * 0),  imagecolorallocate($image, 0, 0, 0), $font, $ok ? $json_data["level"] : '???');
ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * 1),  imagecolorallocate($image, 0, 0, 0), $font, $ok ? $json_data["job"] : '???');
ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * 2), imagecolorallocate($image, 0, 0, 0), $font, $ok ? $json_data["fame"] : '???');
//ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * 3), imagecolorallocate($image, 0, 0, 0), $font, "HAX!");
//ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * 4), imagecolorallocate($image, 0, 0, 0), $font, "HAXCLANZ");



if (!$debug) {
	$filename = '../cache/'.$id.'.png';

	imagepng($image, $filename);
	imagedestroy($image);

	mysql_query("INSERT INTO cache VALUES ('".mysql_real_escape_string($charname)."', 'info', '".$id."', NOW()) ON DUPLICATE KEY UPDATE `id` = VALUES(`id`), `added` = NOW()") or die(mysql_error());

}
?>