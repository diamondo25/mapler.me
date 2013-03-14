<?php
require_once __DIR__.'../inc/database.php';

function CompressResult($query) {
	// Assoc
	$ret = array();
	while ($row = $query->fetch_assoc())
		$ret[] = $row;
	$query->free();
	return $ret;
}


$q = $__database->query("
SELECT 
	c.*
FROM 
	characters c
LEFT JOIN
	character_progress cp
		ON
			cp.character_id = c.internal_id
WHERE
	cp.character_id IS NULL
		OR
	cp.from < c.last_update
");


while ($row = $q->fetch_assoc()) {
	$internal_id = $row['internal_id'];
	
	$compression = array();
	$compression['own_data'] = $row;
	$compression['items'] = CompressResult($__database->query("SELECT * FROM items WHERE character_id = ".$internal_id));
	
	$cashids = array();
	foreach ($compression['items'] as $itemdata) {
		if ($itemdata['cashid'] != 0) $cashids[] = $itemdata['cashid'];
	}
	$compression['pets'] = array();
	if (count($cashids) != 0)
		$compression['pets'] = CompressResult($__database->query("SELECT * FROM pets WHERE cashid IN (".join(',', $cashids).")"));
	
	$compression['skills'] = CompressResult($__database->query("SELECT * FROM skills WHERE character_id = ".$internal_id));
	$compression['skillmacros'] = CompressResult($__database->query("SELECT * FROM skillmacros WHERE character_id = ".$internal_id));
	$compression['sp_data'] = CompressResult($__database->query("SELECT * FROM sp_data WHERE character_id = ".$internal_id));
	
	$__database->query("INSERT INTO character_progress VALUES (".$internal_id.", '".$row['last_update']."', '".$__database->real_escape_string(json_encode($compression))."')");
	unset($compression);
}

?>