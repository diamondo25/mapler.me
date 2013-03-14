<?php
require_once __DIR__.'../inc/database.php';
require_once __DIR__.'../inc/domains.php';
require_once __DIR__.'../inc/job_list.php';
require_once __DIR__.'caching.php';
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

if (!isset($_GET['NO_CACHING']))
	ShowCachedImage($charname, 'info');

$id = uniqid().rand(0, 9);
AddCacheImage($charname, 'info', $id);

$q = $__database->query("SELECT * FROM characters WHERE name = '".$__database->real_escape_string($charname)."'");
if ($q->num_rows == 0) {
	die("character not found");
}
$row = $q->fetch_assoc();
	

$image = imagecreatetruecolor(271, 162);
imagealphablending($image, false);
imagesavealpha($image, true);

$bg_image = imagecreatefrompng("../inc/img/char_bg.png");
imagecopyresampled($image, $bg_image, 0, 0, 0, 0, 271, 386, 271, 386);
imagealphablending($image, true);

// LOAD CHARACTER

$charpos_x = 10;
$charpos_y = 20;

//$character_image = LoadPNG("http://".$domain."/avatar/".$charname);
$character_image = LoadPNG("http://mapler.me/avatar/".$charname);

imagecopyresampled($image, $character_image, $charpos_x, $charpos_y, 0, 0, 96, 96, 96, 96);

// SET NAMETAG
$name = $row['name'];

$x = ($charpos_x + (96 / 2));
$y = ($charpos_y + 10 + 96);

$startWidth = $x - calculateWidth($name) / 2;
$endWidth = $x + calculateWidth($name) / 2;

ImageTTFText($image, $font_size, 0, $startWidth + 3, $y - 5, imagecolorallocate($image, 255, 255, 255), $font, $name);

$base_x = 152;
$base_y = 55;
$step = 18;

ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * 0),  imagecolorallocate($image, 0, 0, 0), $font, $row['level']);
ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * 1),  imagecolorallocate($image, 0, 0, 0), $font, GetJobname($row['job']));
ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * 2), imagecolorallocate($image, 0, 0, 0), $font, $row['fame']);
//ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * 3), imagecolorallocate($image, 0, 0, 0), $font, "HAX!");
//ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * 4), imagecolorallocate($image, 0, 0, 0), $font, "HAXCLANZ");



imagepng($image);


SaveCacheImage($charname, 'info', $image, $id);

imagedestroy($image);

?>