<?php

$cache_folder = '../cache/';

// DISABLE CACHING FULLY
$_GET['NO_CACHING'] = 'true';

function AddCacheImage($character_id, $type, $from, $image_id) {
	return;
	global $__database;

	$__database->query("
INSERT INTO 
	character_cache
VALUES
	(
		".$character_id.", 
		'".$type."', 
		'".$from."', 
		'".$image_id."',
		0
	)
ON DUPLICATE KEY UPDATE
	`image_id` = VALUES(`image_id`)
");
}

function SaveCacheImage($character_id, $type, $image, $image_id) {
	return;
	global $__database, $cache_folder;
	
	header('Last-Modified: '.gmdate('D, d M Y H:i:s', time()).' GMT');
	
	$filename = $cache_folder.$image_id.'.png';

	imagepng($image, $filename);

	$__database->query("UPDATE character_cache SET done = 1 WHERE character_id = ".$character_id." AND type = '".$type."' AND image_id = '".$image_id."'");
}


function ShowCachedImage($character_id, $type, $from, $is_history, $alivetime = '1 DAY') {
	return;
	global $__database, $cache_folder;
	
	$query = "
	SELECT 
		image_id, 
		DATE_ADD(`from`, INTERVAL ".$alivetime.") >= NOW(),
		DATE_ADD(`from`, INTERVAL 1 DAY) <= NOW(),
		done
	FROM 
		character_cache
	WHERE
		character_id = ".$character_id."
	AND
		type = '".$type."'
	AND
		`from` <= '".$from."'
	LIMIT 1
	";
	
	$return = 'NOT_FOUND';
	
	$q = $__database->query($query);
	if ($q->num_rows == 1) {
		$row = $q->fetch_row();
		$filename = $cache_folder.$row[0].'.png';
		
		for ($i = 0; $row[3] == 0 && $i < 10; $i++) {
			$q->free();
			sleep(1); // Wait till it's loaded.
			$q = $__database->query($query);
			$row = $q->fetch_row();
		}
		
		if (file_exists($filename)) {
			if ($is_history || $row[1] == 1 || ($row[1] == 0 && $row[2] == 1)) { // Is history OR not is expired OR is expired BUT older than 1 day
				if (isset($_GET['debug']))
					echo 'found file<br />';

				header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($filename)).' GMT');
				
				readfile($filename);
				
				$q->free();
				die();
			}
			else {
				$return = "EXPIRED";
				// Lol expired. Delete!
				if (isset($_GET['debug']))
					echo 'Expired<br />';
				unlink($filename);
			}
		}
		else {
			if (isset($_GET['debug']))
				echo 'Not found<br />';
		}
	}
	$q->free();
}

?>