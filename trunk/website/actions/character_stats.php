<?php
include_once('../inc/database.php');
include_once('job_list.php');

$font = "arial.ttf";
$font_size = "9.25";

if (!isset($_GET['debug']))
	header('Content-Type: image/png');

$charname = isset($_GET['name']) ? $_GET['name'] : 'ixdiamondoz';

$len = strlen($charname);
if ($len < 4 || $len > 12) {
	$im = imagecreatetruecolor (192, 345);
	$bgc = imagecolorallocate ($im, 255, 255, 255);
	$tc = imagecolorallocate ($im, 0, 0, 0);

	imagefilledrectangle ($im, 0, 0, 192, 345, $bgc);

	/* Output an error message */
	imagestring ($im, 1, 5, 5, 'I AM ERROR', $tc);
	imagestring ($im, 1, 5, 20, "No data found", $tc);
	imagepng($im);
	imagedestroy($im);
	die();
}

$q = $__database->query("SELECT * FROM characters WHERE name = '".$__database->real_escape_string($charname)."'");
if ($q->num_rows == 0) {
	$im = imagecreatetruecolor (192, 345);
	$bgc = imagecolorallocate ($im, 255, 255, 255);
	$tc = imagecolorallocate ($im, 0, 0, 0);

	imagefilledrectangle ($im, 0, 0, 192, 345, $bgc);

	/* Output an error message */
	imagestring ($im, 1, 5, 5, 'I AM ERROR', $tc);
	imagestring ($im, 1, 5, 20, "No data found", $tc);
	imagepng($im);
	imagedestroy($im);
	die();
}


$q2 = $__database->query("SELECT id FROM cache WHERE charactername = '".$__database->real_escape_string($charname)."' AND type = 'stats' AND DATE_ADD(`added`, INTERVAL 1 DAY) >= NOW()");
if ($q2->num_rows == 1) {
	$row = $q2->fetch_assoc();
	$filename = '../cache/'.$row['id'].'.png';
	if (file_exists($filename)) {
		readfile($filename);
		die();
	}
}
$q2->free();

$row = $q->fetch_assoc();

$id = uniqid().($row['ID'] % 10);


$image = imagecreatetruecolor(192, 345);
imagealphablending($image, false);
imagesavealpha($image, true);

$bg_image = imagecreatefrompng("img/stat_window.png");
imagecopyresampled($image, $bg_image, 0, 0, 0, 0, 192, 345, 192, 345);
imagealphablending($image, true);

$base_x = 74;
$base_y = 38;
$step = 18;
$i = 0;
ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * $i++), imagecolorallocate($image, 0, 0, 0), $font, $row["name"]);
ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * $i++), imagecolorallocate($image, 0, 0, 0), $font, $job_names[$row["job"]]);
ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * $i++), imagecolorallocate($image, 0, 0, 0), $font, $row["level"]);
ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * $i++), imagecolorallocate($image, 0, 0, 0), $font, $row["exp"]);
ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * $i++), imagecolorallocate($image, 0, 0, 0), $font, 0); // Honor Level
ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * $i++), imagecolorallocate($image, 0, 0, 0), $font, 0); // Honor EXP
ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * $i++), imagecolorallocate($image, 0, 0, 0), $font, ""); // Guild
ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * $i++), imagecolorallocate($image, 0, 0, 0), $font, $row["chp"]." / ".$row["mhp"]); // Seems strange, but MS updates MP/SP after logging in. -.-'
ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * $i++), imagecolorallocate($image, 0, 0, 0), $font, $row["cmp"]." / ".$row["mmp"]);
ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * $i++), imagecolorallocate($image, 0, 0, 0), $font, $row["fame"]);
$base_y += 23;
ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * $i++), imagecolorallocate($image, 0, 0, 0), $font, $row["ap"]);
$base_y += 10;
ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * $i++), imagecolorallocate($image, 0, 0, 0), $font, $row["str"]);
ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * $i++), imagecolorallocate($image, 0, 0, 0), $font, $row["dex"]);
ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * $i++), imagecolorallocate($image, 0, 0, 0), $font, $row["int"]);
ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * $i++), imagecolorallocate($image, 0, 0, 0), $font, $row["luk"]);


imagepng($image);

$filename = '../cache/'.$id.'.png';

imagepng($image, $filename);
imagedestroy($image);

$q->free();
$__database->query("INSERT INTO cache VALUES ('".$__database->real_escape_string($charname)."', 'stats', '".$id."', NOW()) ON DUPLICATE KEY UPDATE `id` = VALUES(`id`), `added` = NOW()");

?>