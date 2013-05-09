<?php

require_once 'inc/functions.php';
set_time_limit(0);
apc_clear_cache();
apc_clear_cache('opcode');


error_reporting(E_ALL);

$q = $__database->query("
SELECT
	objecttype,
	objectid,
	`key`
FROM
	`strings`
");

$i = 0;

while ($row = $q->fetch_row()) {
	$i++;
	if ($i % 100 == 0) {
		$cacheinfo = apc_sma_info();
		echo 'Memory size: '.memory_get_usage(false).'<br />';
		echo 'Memory size free APC: '.$cacheinfo['avail_mem'].'<br />';
	}
	GetMapleStoryString($row[0], $row[1], $row[2]);
	
	if ($row[2] == 'item')
		GetItemWZInfo($row[1]);
		GetPotentialInfo($row[1]);
		GetItemDefaultStats($row[1]);
	
}

$q->free();


?>