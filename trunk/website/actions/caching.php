<?php

function AddCacheImage($charactername, $type, $id) {
	global $__database;

	$__database->query("INSERT INTO cache VALUES ('".$__database->real_escape_string($charactername)."', '".$type."', '".$id."', NOW(), 0) ON DUPLICATE KEY UPDATE `id` = VALUES(`id`), `added` = NOW(), `done` = 0");
}

function SaveCacheImage($charactername, $type, $image, $id) {
	global $__database;
	
	$filename = '../cache/'.$id.'.png';

	imagepng($image, $filename);

	$__database->query("UPDATE cache SET done = 1 WHERE charactername = '".$__database->real_escape_string($charactername)."' AND type = '".$type."' AND id = '".$id."'");
	
	// $__database->query("INSERT INTO cache VALUES ('".$__database->real_escape_string($charactername)."', '".$type."', '".$id."', NOW(), 1) ON DUPLICATE KEY UPDATE `id` = VALUES(`id`), `added` = NOW(), `done` = 1");
}


function ShowCachedImage($charactername, $type, $alivetime = '1 DAY') {
	global $__database;
	
	$query = "SELECT id, DATE_ADD(`added`, INTERVAL ".$alivetime.") >= NOW(), done FROM cache WHERE charactername = '".$__database->real_escape_string($charactername)."' AND type = '".$type."'";
	
	$q = $__database->query($query);
	if ($q->num_rows == 1) {
		$row = $q->fetch_row();
		$filename = '../cache/'.$row[0].'.png';
		
		for ($i = 0; $row[2] == 0 && $i < 10; $i++) {
			$q->free();
			sleep(1); // Wait till it's loaded.
			$q = $__database->query($query);
			$row = $q->fetch_row();
		}
		
		if (file_exists($filename)) {
			if ($row[1] == 1) {
				if (isset($_GET['debug']))
					echo 'found file<br />';
				readfile($filename);
				die();
			}
			else {
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