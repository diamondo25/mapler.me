<?php
require_once __DIR__.'/../inc/classes/database.php';
require_once __DIR__.'/../inc/functions.php';
require_once __DIR__.'/../inc/job_list.php';
require_once __DIR__.'/../inc/exp_table.php';
require_once __DIR__.'/caching.php';

$__char_db = ConnectCharacterDatabase(CURRENT_LOCALE);

$font = "arial.ttf";
$font_size = "9.25";

if (!isset($_GET['debug']))
	header('Content-Type: image/png');
else
	error_reporting(E_ALL);

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

$q = $__char_db->query("SELECT * FROM characters WHERE name = '".$__char_db->real_escape_string($charname)."'");
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
$internal_id = $row['internal_id'];

if (!isset($_GET['NO_CACHING']))
	ShowCachedImage($internal_id, 'stats', $row['last_update'], false, '2 MINUTE');

$id = uniqid().rand(0, 999);
AddCacheImage($internal_id, 'stats', $row['last_update'], $id);



$row['guildname'] = '-';

$q2 = $__char_db->query("
SELECT
	g.name
FROM
	characters c
LEFT JOIN
	guild_members gm
	ON
		gm.character_id = c.id
LEFT JOIN
	guilds g
	ON
		g.id = gm.guild_id
WHERE
	c.internal_id = ".$row['internal_id']);
if ($q2->num_rows == 1) {
	// Try to fetch guildname
	$row2 = $q2->fetch_row();
	if ($row2[0] !== null) {
		$row['guildname'] = $row2[0];
	}
}
$q2->free();


$stat_addition = GetCorrectStat($row['internal_id'], CURRENT_LOCALE);

$potential_stat_addition = GetItemPotentialBuffs($row['internal_id'], CURRENT_LOCALE);

$skill_stat_addition = GetSkillBuffs($row['internal_id'], CURRENT_LOCALE);

$before = array(
	'str' => $row['str'],
	'dex' => $row['dex'],
	'int' => $row['int'],
	'luk' => $row['luk'],
	'mhp' => $row['mhp'],
	'mmp' => $row['mmp']
);

$level = $row['level'];

if (isset($stat_addition['str'])) $row['str'] += $stat_addition['str'];
if (isset($stat_addition['dex'])) $row['dex'] += $stat_addition['dex'];
if (isset($stat_addition['int'])) $row['int'] += $stat_addition['int'];
if (isset($stat_addition['luk'])) $row['luk'] += $stat_addition['luk'];

if (isset($stat_addition['mhp'])) $row['mhp'] += $stat_addition['mhp'];
if (isset($stat_addition['mmp'])) $row['mmp'] += $stat_addition['mmp'];


foreach ($potential_stat_addition as $itemid => $stats_tmp) {
	foreach ($stats_tmp as $stats) {
		if (isset($stats['incSTR'])) $row['str'] += $stats['incSTR'];
		if (isset($stats['incDEX'])) $row['dex'] += $stats['incDEX'];
		if (isset($stats['incINT'])) $row['int'] += $stats['incINT'];
		if (isset($stats['incLUK'])) $row['luk'] += $stats['incLUK'];
		if (isset($stats['incSTRlv'])) $row['str'] += ($stats['incSTRlv'] * floor($level / 10));
		if (isset($stats['incDEXlv'])) $row['dex'] += ($stats['incDEXlv'] * floor($level / 10));
		if (isset($stats['incINTlv'])) $row['int'] += ($stats['incINTlv'] * floor($level / 10));
		if (isset($stats['incLUKlv'])) $row['luk'] += ($stats['incLUKlv'] * floor($level / 10));
		
		if (isset($stats['incMHP'])) $row['mhp'] += $stats['incMHP'];
		if (isset($stats['incMMP'])) $row['mhp'] += $stats['incMMP'];
	}
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
$rates = array();
$rates['str'] = 0;
$rates['dex'] = 0;
$rates['int'] = 0;
$rates['luk'] = 0;
$rates['mhp'] = 0;
$rates['mmp'] = 0;
foreach ($potential_stat_addition as $itemid => $stats_tmp) {
	foreach ($stats_tmp as $stats) {
		if (isset($stats['incSTRr'])) $rates['str'] += (int)$stats['incSTRr'];
		if (isset($stats['incDEXr'])) $rates['dex'] += (int)$stats['incDEXr'];
		if (isset($stats['incINTr'])) $rates['int'] += (int)$stats['incINTr'];
		if (isset($stats['incLUKr'])) $rates['luk'] += (int)$stats['incLUKr'];

		if (isset($stats['incMHPr'])) $rates['mhp'] += (int)$stats['incMHPr'];
		if (isset($stats['incMMPr'])) $rates['mmp'] += (int)$stats['incMMPr'];
	}
}
foreach ($rates as $ratename => $value)
	if ($value > 0)
		$row[$ratename] *= (100 + (int)$value) / 100;

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
$row['mmp'] = round($row['mmp']);

$row['str'] = $row['str'].' ('.$before['str'].' + '.($row['str'] - $before['str']).')';
$row['dex'] = $row['dex'].' ('.$before['dex'].' + '.($row['dex'] - $before['dex']).')';
$row['int'] = $row['int'].' ('.$before['int'].' + '.($row['int'] - $before['int']).')';
$row['luk'] = $row['luk'].' ('.$before['luk'].' + '.($row['luk'] - $before['luk']).')';

//$row['mhp'] = $row['mhp'].' ('.$before['mhp'].' + '.($row['mhp'] - $before['mhp']).')';
//$row['mmp'] = $row['mmp'].' ('.$before['mmp'].' + '.($row['mmp'] - $before['mmp']).')';

$nextlevelexp = GetNextLevelEXP($row['level']);
$nextlevelperc = $nextlevelexp == 0 ? 0 : round($row['exp'] / $nextlevelexp * 100);


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
ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * $i++), imagecolorallocate($image, 0, 0, 0), $font, $row['exp'].' ('.$nextlevelperc.'%)');
ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * $i++), imagecolorallocate($image, 0, 0, 0), $font, $row['honourlevel']);
ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * $i++), imagecolorallocate($image, 0, 0, 0), $font, $row['honourexp']);
ImageTTFText($image, 9, 0, $base_x, $base_y + ($step * $i++), imagecolorallocate($image, 0, 0, 0), $font, $row['guildname']);
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


SaveCacheImage($internal_id, 'stats', $image, $id);
if (isset($_GET['debug'])) {
	echo 'Skills:'."\r\n";
	print_r($skill_stat_addition);
	echo 'Potential items:'."\r\n";
	print_r($potential_stat_addition);
	echo 'Stats:'."\r\n";
	print_r($stat_addition);
}
else
	imagepng($image);
imagedestroy($image);

$q->free();
?>