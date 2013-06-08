<?php
$username = "maplerme-website";
$password = "#FMO@JF)JNRWGO$@Ngf9hwref923@R#@";

$svn_arguments = '--non-interactive --username '.escapeshellarg($username).' --password '.escapeshellarg($password).' /var/www/maplestats_svn/ 2>&1';

function RunCMD($cmd) {
	$descriptorspec = array(
	   0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
	   1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
	   2 => array("file", "/tmp/error-output.txt", "a") // stderr is a file to write to
	);

	$cwd = '/var/www/maplestats_svn';

	$process = proc_open($cmd, $descriptorspec, $pipes, $cwd);

	if (is_resource($process)) {
		$data = stream_get_contents($pipes[1]);
		fclose($pipes[1]);

		$return_value = proc_close($process);
	}
	else {
		$data = "ERROR";
	}

	return $data;
}

if (isset($_GET['EMERGENCY_UPDATE']) && $_GET['EMERGENCY_UPDATE'] == 'NOSHITBRO') {
?>
Result:<br />
<pre>
<?php echo RunCMD('svn up '.$svn_arguments); ?>
</pre>
<?php
	die();
}
?>