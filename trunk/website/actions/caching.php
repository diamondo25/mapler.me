<?php

function CacheImage($charactername, $type, $image, $id) {
	global $__database;
	
	$filename = '../cache/'.$id.'.png';

	imagepng($image, $filename);

	$__database->query("INSERT INTO cache VALUES ('".$__database->real_escape_string($charactername)."', '".$type."', '".$id."', NOW()) ON DUPLICATE KEY UPDATE `id` = VALUES(`id`), `added` = NOW()");
}


function ShowCachedImage($charactername, $type, $alivetime = '1 DAY') {
	global $__database;
	
	$q = $__database->query("SELECT id, DATE_ADD(`added`, INTERVAL ".$alivetime.") >= NOW() FROM cache WHERE charactername = '".$__database->real_escape_string($charactername)."' AND type = '".$type."'");
	if ($q->num_rows == 1) {
		$row = $q->fetch_row();
		$filename = '../cache/'.$row[0].'.png';
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