<?php
function RunCMD($cmd) {
	$descriptorspec = array(
	   0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
	   1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
	   2 => array("file", "/tmp/error-output.txt", "a") // stderr is a file to write to
	);
	
	$cwd = '/mal';
	
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
?>

<pre>
<?php
$rows = explode("\n", RunCMD('svn info /var/www/maplestats_svn/ 2>&1'));
echo $rows[7]." - ".$rows[8]." - ".$rows[9];
?>
</pre>

<form action="" method="post">
<input type="password" name="ihewfihewfewf" />
<input type="submit" />
</form>
<pre>
<?php
//echo shell_exec('ls -lart');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['ihewfihewfewf'] == 'HURR1312') {
	$username = "maplerme-website";
	$password = "#FMO@JF)JNRWGO$@Ngf9hwref923@R#@";

	echo RunCMD('svn up --non-interactive --username '.escapeshellarg($username).' --password '.escapeshellarg($password).' /var/www/maplestats_svn/ 2>&1');

}
?>
</pre>