<?php
require_once __DIR__.'/../inc/header.php';

$q = $__database->query("
SELECT
	*
FROM
	notes
");

$notes = array();

while ($row = $q->fetch_assoc()) {
	$notes[] = $row;
}

$q->free();

$notice_filename = 'notice.txt';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['updatetxt'])) {
	$updatetxt = $_POST['updatetxt']; //not protected from sql injection to prevent html / php added from derping.
	
	if (!file_exists('notice.txt')) {
		file_put_contents('notice.txt', '');
	}

	if (!is_writable($notice_filename)) {
		chmod($notice_filename, 0755);
	}
	file_put_contents('notice.txt', $updatetxt);
	Logging('admin', $_loginaccount->GetUsername(), 'notice_update', 'Updated notice on Stream.');
?>
	<p class="alert alert-success">Successfully updated notice!</p>
<?php
}
?>
		<h2>General</h2>
		
		<p>Welcome to the administrative panel of Mapler.me. You can control many aspects of the site including monitoring status messages and our listening servers.</p>
		
		<p><i class="icon-exclamation-sign"></i> <b>Please be aware some actions or changes are logged.</b></p>
		
		<h2>Stream Notice</h2>

		<form method="post">
			<textarea name="updatetxt" class="input-xxlarge" id="updatetxt" style="height:200px;" rows="5"><?php echo (file_exists($notice_filename) ? file_get_contents($notice_filename) : ''); ?></textarea>
			<button type="submit" class="btn btn-success">Update</button>
		</form>
		
		<h2>Notes / Todo (edit in database)</h2>
			<?php
				foreach ($notes as $row) {
			?>
				<p class="alert alert-danger"><i class="icon-pushpin"></i> <?php echo $row['data']; ?> - <?php echo $row['fixed']; ?></p>
			<?php
				}
			?>

		<h2>Various functions and information:</h2>
		<button type="button" class="btn btn-warning" onclick="location.href = '?clear_cache'">Clear Cache</button>
		<button type="button" class="btn btn-warning" onclick="location.href = '/internal/php/'">PHP Information</button>
		<button type="button" class="btn btn-warning" onclick="location.href = '/internal/apc/'">APC(Cache) Information</button>
		<br />
		<br />
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
<p class="alert alert-danger">Notice: <?php echo $i; ?> cached files were deleted! </p>
<?php

	// Clear data caches
	apc_clear_cache();
}
?>

	<h4>Signup Control</h4>
	<button type="button" class="btn btn-success" onclick="location.href = '?signupon'">Allow Registering</button>
	<button type="button" class="btn btn-danger" onclick="location.href = '?signupoff'">Disable Registering</button>
	<br/><br/>
	<?php
if (isset($_GET['signupon'])) {
?>
<p class="alert alert-success">Notice: Sign-up is now open! </p>
<?php
$__database->query("UPDATE signup_lock SET status = 0");
}
?>
	<?php
if (isset($_GET['signupoff'])) {
?>
<p class="alert alert-danger">Notice: Sign-up is now closed! </p>
<?php
$__database->query("UPDATE signup_lock SET status = 1");
}
?>
	
</div>

<?php
require_once __DIR__.'/../inc/footer.php';
?>
