<?php
require_once __DIR__.'/../inc/database.php';
require_once __DIR__.'/../inc/functions.php';
require_once __DIR__.'/../inc/job_list.php';
require_once __DIR__.'/../inc/exp_table.php';
require_once __DIR__.'/caching.php';

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
	imagestring ($im, 1, 5, 20, "Incorrect Charname", $tc);
	imagepng($im);
	imagedestroy($im);
	die();
}

if (!isset($_GET['NO_CACHING']))
	ShowCachedImage($charname, 'stats');
$id = uniqid().rand(0, 9);
AddCacheImage($charname, 'stats', $id);

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


$row = $q->fetch_assoc();

$row['guildname'] = '-';
$q2 = $__database->query("SELECT guild FROM character_views WHERE name = '".$__database->real_escape_string($charname)."'");
if ($q2->num_rows == 1) {
	// Try to fetch guildname
	$row2 = $q2->fetch_assoc();
	if ($row2['guild'] != null) {
		$row['guildname'] = $row2['guild'];
	}
}
$q2->free();


$stat_addition = GetCorrectStat($row['internal_id']);

$potential_stat_addition = GetItemPotentialBuffs($row['internal_id']);

$skill_stat_addition = GetSkillBuffs($row['internal_id']);

$before = array(
	'str' => $row['str'],
	'dex' => $row['dex'],
	'int' => $row['int'],
	'luk' => $row['luk'],
	'mhp' => $row['mhp'],
	'mmp' => $row['mmp']
);


if (isset($stat_addition['str'])) $row['str'] += $stat_addition['str'];
if (isset($stat_addition['dex'])) $row['dex'] += $stat_addition['dex'];
if (isset($stat_addition['int'])) $row['int'] += $stat_addition['int'];
if (isset($stat_addition['luk'])) $row['luk'] += $stat_addition['luk'];

if (isset($stat_addition['mhp'])) $row['mhp'] += $stat_addition['mhp'];
if (isset($stat_addition['mmp'])) $row['mmp'] += $stat_addition['mmp'];


foreach ($potential_stat_addition as $itemid => $stats) {
	if (isset($stats['incSTR'])) $row['str'] += $stats['incSTR'];
	if (isset($stats['incDEX'])) $row['dex'] += $stats['incDEX'];
	if (isset($stats['incINT'])) $row['int'] += $stats['incINT'];
	if (isset($stats['incLUK'])) $row['luk'] += $stats['incLUK'];
	
	if (isset($stats['incMHP'])) $row['mhp'] += $stats['incMHP'];
	if (isset($stats['incMMP'])) $row['mhp'] += $stats['incMMP'];
}


foreach ($skill_stat_addition as $skillid => $tempinfo) {
	$level = $tempinfo['level'];
	$data = $tempinfo['data'];
	if (isset($data['maxLevel']) && $level > $data['maxLevel']) {
		$level = $data['maxLevel'];
	}
	
	$xvalue = $level;
	if (isset($data['x'])) {
		$xvalue = CalculateSkillValue($data['x'], $level);
	}
	
	
	if (isset($data['strX'])) $row['str'] += CalculateSkillValue($data['strX'], $xvalue);
	if (isset($data['dexX'])) $row['dex'] += CalculateSkillValue($data['dexX'], $xvalue);
	if (isset($data['intX'])) $row['int'] += CalculateSkillValue($data['intX'], $xvalue);
	if (isset($data['lukX'])) $row['luk'] += CalculateSkillValue($data['lukX'], $xvalue);

	if (isset($data['mhpX'])) $row['mhp'] += CalculateSkillValue($data['mhpX'], $xvalue);
	if (isset($data['mmpX'])) $row['mmp'] += CalculateSkillValue($data['mmpX'], $xvalue);
}

// Rates
foreach ($potential_stat_addition as $itemid => $stats) {
	if (isset($stats['incSTRr'])) $row['str'] *= ((100 + $stats['incSTRr']) / 100);
	if (isset($stats['incDEXr'])) $row['dex'] *= ((100 + $stats['incDEXr']) / 100);
	if (isset($stats['incINTr'])) $row['int'] *= ((100 + $stats['incINTr']) / 100);
	if (isset($stats['incLUKr'])) $row['luk'] *= ((100 + $stats['incLUKr']) / 100);
	
	if (isset($stats['incMHPr'])) $row['mhp'] *= ((100 + $stats['incMHPr']) / 100);
	if (isset($stats['incMMPr'])) $row['mhp'] *= ((100 + $stats['incMMPr']) / 100);
}

// Moar rates

foreach ($skill_stat_addition as $skillid => $tempinfo) {
	$level = $tempinfo['level'];
	$data = $tempinfo['data'];
	if (isset($data['maxLevel']) && $level > $data['maxLevel']) {
		$level = $data['maxLevel'];
	}
	
	$xvalue = $level;
	if (isset($data['x'])) {
		$xvalue = CalculateSkillValue($data['x'], $level);
	}
	
	if (isset($data['mhpR'])) $row['mhp'] += ((100 + CalculateSkillValue($data['mhpR'], $xvalue)) / 100);
	if (isset($data['mmpR'])) $row['mmp'] += ((100 + CalculateSkillValue($data['mmpR'], $xvalue)) / 100);
}

$row['str'] = round($row['str']);
$row['dex'] = round($row['dex']);
$row['int'] = round($row['int']);
$row['luk'] = round($row['luk']);

$row['mhp'] = round($row['mhp']);
$row['mhp'] = round($row['mhp']);

$row['str'] = $row['str'].' ('.$before['str'].' + '.($row['str'] - $before['str']).')';
$row['dex'] = $row['dex'].' ('.$before['dex'].' + '.($row['dex'] - $before['dex']).')';
$row['int'] = $row['int'].' ('.$before['int'].' + '.($row['int'] - $before['int']).')';
$row['luk'] = $row['luk'].' ('.$before['luk'].' + '.($row['luk'] - $before['luk']).')';

//$row['mhp'] = $row['mhp'].' ('.$before['mhp'].' + '.($row['mhp'] - $before['mhp']).')';
//$row['mmp'] = $row['mmp'].' ('.$before['mmp'].' + '.($row['mmp'] - $before['mmp']).')';

// Get EXP percentage
$nextlevelexp = $__exp_table[$row['level']];
$row['exp'] .= ' ('.round($row['exp'] / $nextlevelexp * 100).'%)';


$image = imagecreatetruecolor(192, 345);
imagealphablending($image, false);
imagesavealpha($image, true);

$bg_image = imagecreatefrompng("../inc/img/stat_window.png");
imagecopyresampled($image, $bg_image, 0, 0, 0, 0, 192, 345, 192, 345);
imagealphablending($image, true);

$base_x = 74;
$base_y = 38;
$step = 18;
$i = 0;
ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * $i++), imagecolorallocate($image, 0, 0, 0), $font, $row['name']);
ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * $i++), imagecolorallocate($image, 0, 0, 0), $font, GetJobname($row['job']));
ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * $i++), imagecolorallocate($image, 0, 0, 0), $font, $row['level']);
ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * $i++), imagecolorallocate($image, 0, 0, 0), $font, $row['exp']);
ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * $i++), imagecolorallocate($image, 0, 0, 0), $font, $row['honourlevel']);
ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * $i++), imagecolorallocate($image, 0, 0, 0), $font, $row['honourexp']);
ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * $i++), imagecolorallocate($image, 0, 0, 0), $font, $row['guildname']); // Guild
ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * $i++), imagecolorallocate($image, 0, 0, 0), $font, $row['chp']." / ".$row['mhp']);
ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * $i++), imagecolorallocate($image, 0, 0, 0), $font, $row['cmp']." / ".$row['mmp']);
ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * $i++), imagecolorallocate($image, 0, 0, 0), $font, $row['fame']);
$base_y += 23;
ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * $i++), imagecolorallocate($image, 0, 0, 0), $font, $row['ap']);
$base_y += 10;
ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * $i++), imagecolorallocate($image, 0, 0, 0), $font, $row['str']);
ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * $i++), imagecolorallocate($image, 0, 0, 0), $font, $row['dex']);
ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * $i++), imagecolorallocate($image, 0, 0, 0), $font, $row['int']);
ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * $i++), imagecolorallocate($image, 0, 0, 0), $font, $row['luk']);


imagepng($image);


SaveCacheImage($charname, 'stats', $image, $id);

imagedestroy($image);
$q->free();
?>