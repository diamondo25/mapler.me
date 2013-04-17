<?php
require_once __DIR__.'/../inc/header.php';

function RunCMD($cmd) {
	echo 'COMMAND: '.$cmd."\r\n\r\n";
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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['searchfor'], $_POST['lines'])) {
	$searchfor = $_POST['searchfor'];
	$lines = intval($_POST['lines']);
	if ($lines == 0) $lines = 30;
}

?>
<form method="post">
Text to search <input type="text" name="searchfor" value="<?php echo $searchfor; ?>" /><br />
Lines <input type="text" name="lines" value="<?php echo $lines; ?>" /><br />
<input type="submit" />
</form>
<?php

$oldestTime = 0;
$name = '';
foreach (glob('/mplrserver/logs/*') as $filename) {
	$t = filectime($filename);
	if ($t > $oldestTime) {
		$oldestTime = $t;
		$name = $filename;
	}
}
?>
<pre>
<?php echo RunCMD('grep '.escapeshellarg($searchfor).' '.escapeshellarg($name).' | tail -n '.$lines); ?>
</pre>
<?php
require_once __DIR__.'/../inc/footer.php';
?>