<?php
require_once '../../inc/functions.php';

// SHOO
if (!$_loggedin || $_loginaccount->GetAccountRank() < RANK_ADMIN) {
	header('Location: /');
	die();
}

require_once '../../inc/header.php';

$username = "maplerme-website";
$password = "#FMO@JF)JNRWGO$@Ngf9hwref923@R#@";

$svn_arguments = '--non-interactive --username '.escapeshellarg($username).' --password '.escapeshellarg($password).' /var/www/maplestats_svn/ 2>&1';


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

<p class="lead">Use the form below to update Mapler.me to the current revision.</p>

<pre style="font-size:12px;">
<?php
$rows = RunCMD('svn log -r COMMITTED '.$svn_arguments);

echo $rows;
?>
</pre>

<form action="" method="post">
<div class="input-append">
  <input class="span11" id="appendedInputButton" name="ihewfihewfewf" type="password" placeholder="Type the development password here.">
  <input type="submit" class="btn" style="position: relative;
right: 1px;
height: 36px;
border-radius: 0px;
width: 115px;" value="Update!"/>
</div>
</form>

<pre>
Result:<br />
<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['ihewfihewfewf'] == 'HURR1312') {
	echo RunCMD('svn up '.$svn_arguments);

}
?>
</pre>
<hr />
<button type="button" class="btn" onclick="location.href = '?clear_cache'">Clear Cache</button>
<?php
if (isset($_GET['clear_cache'])) {
	$files = glob('../../cache/*');
	$i = 0;
	foreach($files as $file){
		if (is_file($file)) {
			unlink($file);
			$i++;
		}
	}
?>
<p class="lead alert info-danger">Deleted <?php echo $i; ?> cachefiles!</p>
<?php

	// Clear data caches
	apc_delete('data_cache');
	apc_delete('data_iteminfo_cache');
	apc_delete('data_itemoptions_cache');
}




require_once '../../inc/footer.php';
?>
