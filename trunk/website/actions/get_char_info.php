<?php
include_once('../inc/database.php');
include_once("job_list.php");
$is_include = isset($_HERP);

if (!$is_include) {
	$charname = isset($_GET['name']) ? $_GET['name'] : 'ixdiamondoz';
}

$len = strlen($charname);
if ($len < 4 || $len > 12) {
	die();
}

function RequestData($rankingpage, $onlyrankdata = true) {
	global $charname;
	$ret = array();
	$data = file_get_contents("http://maplestory.nexon.net/rankings/".$rankingpage."?pageIndex=1&character_name=".$charname."&search=true");



	preg_match_all('/<tr>(.*?)<\/tr>/s', $data, $matches);

	$ret = array();

	for ($i = 1; $i < sizeof($matches[0]); $i++) {
		$match = $matches[0][$i];
		
		preg_match_all('/<td(.*?)>(.*?)<\/td>/s', $match, $columns);
		
		$name = trim($columns[2][2]);
		
		if (strtolower($name) != strtolower($charname)) continue;
		$rank = $columns[2][0];
		
		if ($onlyrankdata) {
			if (strpos($rankingpage, "fame") !== false) {
				$ret = array(
					"rank" => (int)$rank,
					"amount" => (int)trim($columns[2][5]));

			}
			else {
				preg_match('/<div class="rank-(?P<change>\w+)">(?P<rank>\d*)(.*?)<\/div>/s', $columns[2][5], $rankdata);
				
				$ret = array(
					"rank" => (int)$rank,
					"move_rank" => isset($rankdata['rank']) ? (int)$rankdata['rank'] : '-',
					"move_change" => $rankdata['change']);
			}
		}
		else {
		
			preg_match_all('/<img (.*?)src=\'(.*?)\'(.*?)\/>/s', $columns[2][1], $image_data);
			
			$image = array();
			for ($j = 0; $j < sizeof($image_data[2]); $j++) {
				$url = $image_data[2][$j];
				$type = substr($url, strpos($url, '/', 10) + 1);
				$type = substr($type, 0, strpos($type, '/'));
				$image[$type] = $url;
			}
			
			$world = $columns[2][3];
			
			$world = substr($world, strpos($world, 'world ') + 6);
			$world = substr($world, 0, strpos($world, '"'));
			
			$job = $columns[2][4];
			
			$job = substr($job, strpos($job, 'title="') + 7);
			$job = substr($job, 0, strpos($job, '"'));
			
			
			preg_match('/(?P<level>\d+)<br\/>\((?P<exp>\d+)\)/s', trim($columns[2][5]), $leveldata);
			
			preg_match('/<div class="rank-(?P<change>\w+)">(?P<rank>\d*)(.*?)<\/div>/s', $columns[2][5], $rankdata);
			
			$ret = array(
				"name" => $name,
				"level" => (int)$leveldata['level'],
				"exp" => (int)$leveldata['exp'],
				"job" => $job,
				"world" => ucfirst($world),
				"rank" => (int)$rank,
				"move_rank" => isset($rankdata['rank']) ? (int)$rankdata['rank'] : '-',
				"move_change" => $rankdata['change'],
				"images" => $image);
			
		}
		break;
	}
	return $ret;
}

function SaveData($result, $exists) {
	global $is_include, $charname, $__database;
	
	if ($exists) {
		$__database->query("UPDATE info_requests SET last_check = NOW(), data = '".$__database->real_escape_string($result)."' WHERE charactername = '".$__database->real_escape_string($charname)."'");
	}
	else {
		$__database->query("INSERT INTO info_requests VALUES ('".$__database->real_escape_string($charname)."', NOW(), 0, '".$__database->real_escape_string($result)."')");
	}
	if ($is_include) {
		global $json_data;
		$json_data = $result;
	}
	else {
		die($result);
	}
}

// Check if there's a request done last day
$q = $__database->query("SELECT data, blocked, DATE_ADD(last_check, INTERVAL 1 DAY) > NOW(), UNIX_TIMESTAMP(NOW()) FROM info_requests WHERE charactername = '".$__database->real_escape_string($charname)."' LIMIT 1");

$exists = false;
$done = false;
$last_update = time();
if ($q->num_rows == 1) {
	$exists = true;
	$row = $q->fetch_array();
	$last_update = $row[3];
	if ($row[2] == 1) { // Not old enough
		$result = "";
		if ($row[1] == 1) { // Blocked
			$result = json_encode(array("error" => "Info request blocked for this charactername."));
		}
		else {
			$result = $row[0];
		}
		if ($is_include) {
			$done = true;
			$json_data = $result;
		}
		else {
			die($result);
		}
	}
}

if (!$done) {
	$q = $__database->query("SELECT c.name, c.level, c.exp, c.job, c.fame, w.world_name AS worldname FROM characters c LEFT JOIN world_data w ON w.world_id = c.world_id WHERE c.name = '".$__database->real_escape_string($charname)."'");
	
	$overall = RequestData("overall-ranking/legendary", false);
	
	if (sizeof($overall) == 0) {
		// Check DB for 'some' info
		if ($q->num_rows > 0) {
			$row = $q->fetch_array();
			// Load values
			$images = array(
				"Character" => "img/no-character.gif",
				"Pet" => "HERP-HHJLHDFFEFDGELLNFOPOKJFPLJIJIOFJGKBFIENAOLFLCACMGPEDJLOKHBLCGMBN-ignore"
			);
			$overall = array(
				"name" => $row['name'],
				"level" => (int)$row['level'],
				"exp" => (int)$row['exp'],
				"job" => $job_names[$row['job']],
				"world" => ucfirst($row['worldname']),
				"rank" => '????',
				"fame" => $row['fame'],
				"move_rank" => '-',
				"move_change" => '????',
				"images" => $images);
				
			$overall["ranking"]["job"] = array("move_rank" => '-', "move_change" => '????', "rank" => '????');
			$overall["ranking"]["fame"] = array("rank" => '????');
		}
		else {
			$overall = array("error" => "Character not found");
		}
	}
	else {
	
		$overall["ranking"]["overall"] = array("move_rank" => $overall["move_rank"], "move_change" => $overall["move_change"], "rank" => $overall["rank"]);
		unset($overall["rank"]);
		unset($overall["move_change"]);
		unset($overall["move_rank"]);
	
	
		$world = RequestData("world-ranking/".strtolower($overall["world"]));
		$overall["ranking"]["world"] = array("move_rank" => $world["move_rank"], "move_change" => $world["move_change"], "rank" => $world["rank"]);
		
		// Job ranking
		$pagename = "";
		$jobname = strtolower($overall["job"]);
		
		
		if ($q->num_rows > 0) {
			$row = $q->fetch_array();
			$overall["job"] = $job_names[$row['job']]; // Correct jobname
		}
		
		if ($jobname == "beginner" || $jobname == "warrior" || $jobname == "magician" || $jobname == "bowman" || $jobname == "thief" || $jobname == "pirate") {
			$pagename = "explorer/".$jobname;
		}
		elseif ($jobname == "noblesse" || $jobname == "dawn warrior" || $jobname == "blaze wizard" || $jobname == "wind archer" || $jobname == "night walker" || $jobname == "thunder breaker") {
			$pagename = "cygnus-knights/".$jobname;
		}
		elseif ($jobname == "legend" || $jobname == "aran") {
			$pagename = "aran/".$jobname;
		}
		elseif ($jobname == "citizen" || $jobname == "demon slayer" || $jobname == "battle mage" || $jobname == "wild hunter" || $jobname == "mechanic") {
			$pagename = "resistance/".$jobname;
		}
		else {
			$pagename = $jobname."/".$jobname;
		}
		
		$pagename = str_replace(" ", "-", $pagename);
		
		$job = RequestData("job-ranking/".$pagename);
		$overall["ranking"]["job"] = array("move_rank" => $job["move_rank"], "move_change" => $job["move_change"], "rank" => $job["rank"]);
		
		$fame = RequestData("fame-ranking/legendary");
		$overall["ranking"]["fame"] = array("rank" => $fame["rank"]);
		$overall["fame"] = $fame["amount"];
		
		$overall["last_update"] = $last_update;
	}
	
	$result = json_encode($overall);
	SaveData($result, $exists);
}
?>