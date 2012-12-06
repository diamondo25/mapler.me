<?php
$start_time = microtime(true);
header("Content-Type: application/json");
require('connect.php');


// Select the database
$db = @mysql_select_db($db_name);
if (!$db) {
	$error = "Failed to select database.<br />";
	$error .= mysql_errno() . ": " . mysql_error();
	die($error);
}

if (!isset($_GET['mode'])) die(json_encode(array("error" => "Mode Not Set")));

$mode = $_GET['mode'];
if (!isset($_GET['now']) && isset($_GET['at'])) {
	$at = intval($_GET['at']);
}
else {
	$at = time() + 100000;	
}
$unix = isset($_GET['unix']);

if ($mode == "total") {
	
	$includeGone = isset($_GET['add_joined']);
	$logcolumn = "`log_date`";
	if ($unix)
		$logcolumn = "UNIX_TIMESTAMP(`log_date`)";
	$query = "
SELECT 
	SUM(`current_load`) AS `population`, 
	".$logcolumn." AS `timestamp` 
FROM 
	`log` 
WHERE
	UNIX_TIMESTAMP(`log_date`) <= ".$at."
GROUP BY 
	`log_date` 
ORDER BY 
	`log_date` DESC 
LIMIT 
	".($includeGone ? 2 : 1);
	
	$q = mysql_query($query);
	$row = mysql_fetch_assoc($q);
	$row["population"] = intval($row["population"]);
	if ($unix)
		$row["timestamp"] = intval($row["timestamp"]);
	else
		$row["timestamp"] = date("c", strtotime($row["timestamp"]));
	
	if ($includeGone) {
		$tmp = mysql_fetch_assoc($q);
		$row["joined"] = $row["population"] - $tmp["population"];
	}
	echo json_encode($row);
}
elseif ($mode == "world") {
	function GetCurrentLoads($id) {
		global $at, $unix;
		
		$logcolumn = "`log_date`";
		if ($unix)
			$logcolumn = "UNIX_TIMESTAMP(`log_date`)";
		
		$query = '
SELECT
	GROUP_CONCAT(`channel_id`) AS `channels`,
	GROUP_CONCAT(`current_load`) AS `loads`,
	'.$logcolumn.' AS `timestamp` 
FROM
	`log`
WHERE
	`world_id` = '.$id.' 
		AND
	UNIX_TIMESTAMP(`log_date`) <= '.$at.'
GROUP BY
	`log_date`
ORDER BY 
	`log_date` DESC 
LIMIT
	1
';
		$q = mysql_query($query);
		$row = mysql_fetch_array($q);
		$ret = array();
		$channels = explode(',', $row[0]);
		$loads = explode(',', $row[1]);
		for ($i = 0; $i < sizeof($channels); $i++) {
			$ret["channels"][(int)$channels[$i]] = (int)$loads[$i];
		}
		
		if ($unix)
			$ret["timestamp"] = intval($row["timestamp"]);
		else
			$ret["timestamp"] = date("c", strtotime($row["timestamp"]));
		return $ret;
	}
	function GetInfo($id) {
		$q = mysql_query('
SELECT 
	w.world_id AS world_id,
	w.world_name AS world_name,
	w.channels AS channel_amount,
	w.state AS world_state,
	w.message AS world_message
FROM 
	world_data w 
LEFT JOIN 
	world_alliance wa 
	ON 
	wa.alliance = w.world_id
WHERE
	wa.alliance IS NULL
		AND
	w.world_id = '.$id.'
');
		if (mysql_num_rows($q) == 0) return NULL;
		return mysql_fetch_assoc($q);
	}
	
	if (isset($_GET['world'])) {
		$id = intval($_GET['world']);
		
		$info = GetInfo($id);
		if ($info == NULL) {
			die(json_encode(array("error" => "World not found or is an alliance world (use first world of the alliance instead)")));
		}
		$ret = array_merge($info, GetCurrentLoads($id));
		die(json_encode($ret));
	}
	else {
		// All worlds
		$q = mysql_query(
"
SELECT 
	GROUP_CONCAT(w.world_id) 
FROM 
	world_data w 
LEFT JOIN 
	world_alliance wa 
	ON 
	wa.alliance = w.world_id
WHERE
	wa.alliance IS NULL
");
		$worldids = explode(',', mysql_result($q, 0));
		$ret = array();
		$ret["world_count"] = count($worldids);
		foreach ($worldids as $id) {
			$tmp = array_merge(GetInfo($id), GetCurrentLoads($id));
			$ret["worlds"][$id] = $tmp;
		}
		die(json_encode($ret));
	}
	
	// try get value
}
?>