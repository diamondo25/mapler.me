<?php
if (!isset($_GET['debug']))
	header('Content-Type: image/png');
else
	error_reporting(E_ALL);

$images_loc = __DIR__.'/../inc/img/ui/bits/';
$_rows = isset($_GET['rows']) ? intval($_GET['rows']) : 2;
$_cols = isset($_GET['cols']) ? intval($_GET['cols']) : 2;

if ($_rows < 1) $_rows = 1;
elseif ($_rows > 10) $_rows = 10;

if ($_cols < 1) $_cols = 1;
elseif ($_cols > 10) $_cols = 10;

$padding = array();
$padding['top'] = 35;
$padding['bottom'] = 22;
$padding['left'] = 42;
$padding['right'] = 42;

$icon_margin = array();
$icon_margin['top'] = 0;
$icon_margin['bottom'] = 2;
$icon_margin['left'] = 0;
$icon_margin['right'] = 5;

$icon_size = array();
$icon_size['width'] = 32;
$icon_size['height'] = 32;

$content_margin = array();
$content_margin['top'] = -10;
$content_margin['bottom'] = -10;
$content_margin['left'] = -30;
$content_margin['right'] = -35;

$width = $_cols * (($icon_size['width'] + $icon_margin['left'] + $icon_margin['right']));
$width += $padding['left'] + $padding['right'];
$width += $content_margin['left'] + $content_margin['right'];

$height = $_rows * ($icon_size['height'] + $icon_margin['top'] + $icon_margin['bottom']);
$height += $padding['top'] + $padding['bottom'];
$height += $content_margin['top'] + $content_margin['bottom'];


$image = imagecreatetruecolor($width, $height);
imagealphablending($image, false);
imagesavealpha($image, true);

$gx = 0; // Global X

function AddImage($wut, $x, $y, $tow = null, $toh = null) {
	global $image, $images_loc, $gx;
	$img = imagecreatefrompng($images_loc.$wut.'.png');
	imagecopyresampled($image, $img, $x, $y, 0, 0, $tow === null ? imagesx($img) : $tow, $toh === null ? imagesy($img) : $toh, imagesx($img), imagesy($img));
	imagealphablending($img, true);
	
	$gx += $tow === null ? imagesx($img) : $tow;
}

// Build top

$center_width = $width;
$center_width -= $padding['left'] + $padding['right'];
$center_height = $height;
$center_height -= $padding['top'] + $padding['bottom'];

$gx = 0;
AddImage('nw', $gx, 0);
for ($i = 0; $i < $center_width; $i++)
	AddImage('n', $gx, 0);
AddImage('ne', $gx, 0);




$y = $padding['top'];
$gx = 0;
AddImage('w', $gx, $y, null, $center_height);
AddImage('c', $gx, $y, $center_width, $center_height);
AddImage('e', $gx, $y, null, $center_height);


$gx = 0;
$y = $padding['top'] + $center_height;
AddImage('sw', $gx, $y);
for ($i = 0; $i < $center_width; $i++)
	AddImage('s', $gx, $y);
AddImage('se', $gx, $y);


$y = 0;
for ($row = 0; $row < $_rows; $row++) {
	$y = ($row * ($icon_size['height'] + $icon_margin['top'] + $icon_margin['bottom'])) + $padding['top'] + $content_margin['top'];
	if ($row > 0) {
		$y += $icon_margin['bottom'];
	}

	$gx = $padding['left'] + $content_margin['left'];
	for (; $gx + $padding['right'] <= $width;) {
		$gx += $icon_margin['left'];
		AddImage('icon', $gx, $y);
		$gx += $icon_margin['right'];
	}
}

imagepng($image);
imagedestroy($image);
?>