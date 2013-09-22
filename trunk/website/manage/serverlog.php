<?php
require_once __DIR__.'/../inc/header.php';

function RunCMD($cmd) {
	$descriptorspec = array(
		0 => array('pipe', 'r'),  // stdin is a pipe that the child will read from
		1 => array('pipe', 'w'),  // stdout is a pipe that the child will write to
		2 => array('pipe', 'w') 
	);

	$cwd = '/mplrserver';

	$process = proc_open($cmd, $descriptorspec, $pipes, $cwd);

	if (is_resource($process)) {
		$data = stream_get_contents($pipes[1]);
		fclose($pipes[1]);

		$return_value = proc_close($process);
	}
	else {
		$data = 'ERROR';
	}

	return $data;
}

$searchfor = '';
$lines = 30;
$type = 'GMS';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['searchfor'], $_POST['lines'], $_POST['type'])) {
	$searchfor = $_POST['searchfor'];
	$type = $_POST['type'];
	$lines = intval($_POST['lines']);
	if ($lines == 0) $lines = 30;
}

?>

<h2>Server Log</h2>

<form method="post">
Search for? <input type="text" name="searchfor" value="<?php echo $searchfor; ?>" />
Lines <input type="text" name="lines" value="<?php echo $lines; ?>" style="width:100px;" />
Type:
<select name="type">
	<option<?php echo $type == 'EMS' ? ' selected="selected"' : ''; ?>>EMS</option>
	<option<?php echo $type == 'GMS' ? ' selected="selected"' : ''; ?>>GMS</option>
	<option<?php echo $type == 'KMS' ? ' selected="selected"' : ''; ?>>KMS</option>
</select>
<input type="submit" class="btn btn-success" value="Search"/>
</form>
<?php

$oldestTime = 0;
$name = '';
$dir = '';
if ($type == 'EMS') $dir = 'EMS/';
elseif ($type == 'KMS') $dir = 'KMS/';
foreach (glob('/mplrserver/'.$dir.'logs/*') as $filename) {
	$t = filectime($filename);
	if ($t > $oldestTime) {
		$oldestTime = $t;
		$name = $filename;
	}
}
?>
<pre>
<?php echo RunCMD('grep -i -n '.escapeshellarg($searchfor).' '.escapeshellarg($name).' | tail -n '.$lines); ?>
</pre>
<?php
require_once __DIR__.'/../inc/footer.php';
?>