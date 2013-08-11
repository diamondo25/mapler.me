<?php
require_once __DIR__.'/../inc/header.php';

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
?>
	<p class="alert alert-success">Successfully updated notice!</p>
<?php
}
?>
		<h4>Change the stream notice:</h4>

		<form method="post">
			<textarea name="updatetxt" id="updatetxt" style="height:50px;" rows="5"><?php echo (file_exists($notice_filename) ? file_get_contents($notice_filename) : ''); ?></textarea>
			<button type="submit" class="btn">Update!</button>
		</form>

		<h4>Various functions and information:</h4>
		<button type="button" class="btn" onclick="location.href = '?clear_cache'">Clear Cache</button>
		<button type="button" class="btn" onclick="location.href = '/internal/php/'">PHP Information</button>
		<button type="button" class="btn" onclick="location.href = '/internal/apc/'">APC(Cache) Information</button>
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
	<button type="button" class="btn" onclick="location.href = '?signupon'">Allow Registering</button>
	<button type="button" class="btn" onclick="location.href = '?signupoff'">Disable Registering</button>
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
