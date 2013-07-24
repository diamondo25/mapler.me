<?php

if (!isset($_GET['id'])) die();

require_once __DIR__.'/../inc/functions.php';
require_once __DIR__.'/../inc/functions.datastorage.php';

if (!isset($_GET['debug']))
	header('Content-Type: image/png');
else
	error_reporting(E_ALL);

$petid = intval($_GET['id']);
if ($petid < 0 || $petid > 1000) die();
$petid = 5000000 + $petid;

$images_location = 'http://static_images.mapler.me/Inventory/Pet/';

// Add ID to image location
$images_location .= $petid.'.img/';

$image = imagecreatetruecolor(128, 128);
imagesavealpha($image, true);
$trans = imagecolorallocatealpha($image, 0, 0, 0, 127);
imagefill($image, 0, 0, $trans);

$x = 64;
$y = 64;

$info = GetItemWZInfo($petid);

$image_offset = array(0, 0);
if ($info['stand0'] !== null) {
	$origin = $info['stand0']['0']['origin'];
	$image_offset[0] = $origin['X'];
	$image_offset[1] = $origin['Y'];
}

AddImage('stand0.0.png', $x - $image_offset[0], $y - $image_offset[1]);


imagepng($image);
imagedestroy($image);


function AddImage($url, $x, $y) {
	global $image, $images_location;
	$img = @imagecreatefrompng($images_location.$url) or die('image not found');
	imagecopyresampled($image, $img, $x, $y, 0, 0, imagesx($img), imagesy($img), imagesx($img), imagesy($img));
	imagealphablending($img, true);
}

?>