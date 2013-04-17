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

require_once __DIR__.'/../inc/functions.php';

// SHOO
if (!$_loggedin || $_loginaccount->GetAccountRank() < RANK_ADMIN) {
	header('Location: /');
	die();
}

require_once __DIR__.'/../inc/header.php';
?>
		<h4>Update the website by pushing a revision:</h4>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ihewfihewfewf']) && $_POST['ihewfihewfewf'] == 'HURR1312') {
?>
		Result:<br />
		<pre><?php echo RunCMD('svn up '.$svn_arguments); ?></pre>
<?php
}
?>

		<pre style="font-size:12px;"><?php echo RunCMD('svn log -r COMMITTED '.$svn_arguments); ?></pre>

		<form action="" method="post">
			<div class="input-append">
				<input class="span7" id="appendedInputButton" name="ihewfihewfewf" type="password" placeholder="Type the development password here.">
				<input type="submit" class="btn" style="position: relative;
				right: 1px;
				height: 36px;
				border-radius: 0px;
				width: 107px;" value="Update!"/>
			</div>
		</form>

<?php
require_once __DIR__.'/../inc/footer.php';
?>
