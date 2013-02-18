<?php
require_once '../inc/header.php';
if (!IsOwnAccount()) {
?>
	This is not your account.
<?php
	require_once '../inc/footer.php';
	die();
}

$maycheck = true;
if ($_SESSION['login_data']['last_account_addition'] != '0000-00-00 00:00:00') {
	$tmp = strtotime($_SESSION['login_data']['last_account_addition']);
	$tmp += 5 * 60; // 5 minutes
	
	if (time() < $tmp) { // 5 minutes
		$maycheck = false;
		$minutes_timeout = ceil(($tmp - time()) / 60);
	}

}


if ($maycheck && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['username'], $_POST['password'])) {
	// Oh jolly
	$maycheck = false;
	$minutes_timeout = 5;
	$_SESSION['login_data']['last_account_addition'] = date("Y-m-d H:i:s");
	
	$__database->query("UPDATE accounts SET last_account_addition = NOW() WHERE id = ".$_SESSION['login_data']['id']);

	$post_values = array(
		'userID' => $_POST['username'], 
		'password' => $_POST['password']
	);
	
	$curl = curl_init();

	curl_setopt($curl, CURLOPT_URL, "http://www.nexon.net/api/v001/account/login");
	curl_setopt($curl, CURLOPT_HEADER, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post_values));
	curl_setopt($curl, CURLOPT_HTTPHEADER, array('User-Agent: Mapler.me/1.0'));
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

	$result = curl_exec($curl);

	$info = curl_getinfo($curl);
	curl_close($curl);

	preg_match('/HTTP\/1.1 (.*?) (.*?)\r\n/', $result, $matches);
	if (count($matches) != 3) {
		print_r($matches);
		die('Error parsing header.');
	}
	
	if ($matches[1] == '429') {
?>
<p class="lead alert-error alert">Too many incorrect logins. Please try again later.</p>
<?php
	}
	elseif ($matches[1] != '200') {
?>
<p class="lead alert-error alert">Invalid username or password entered</p>
<?php
	}
	else {
		preg_match('/Set-Cookie: session=(.*?);/', $result, $matches);
		$real_username = $matches[1];
		
		
		$q = $__database->query("SELECT 1 FROM users WHERE username = '".$__database->real_escape_string($real_username)."' UNION SELECT 1 FROM users_weblogin WHERE name = '".$__database->real_escape_string($real_username)."'");
		if ($q->num_rows != 0) {
?>
<p class="lead alert-error alert">This account has already been added to a Mapler.me account!</p>
<?php
		
		}
		else {
			if (strtolower($real_username) != strtolower($post_values['userID'])) { 
?>
<p class="lead alert-info alert">Your 'real' username is <strong><?php echo $real_username; ?></strong>. You can use this instead of your e-mail address to login on MapleStory!</p>
<?php
			}
			
			$__database->query("INSERT INTO users_weblogin VALUES (NULL, ".$__url_useraccount->GetId().", '".$__database->real_escape_string($real_username)."')");
		
?>
<p class="lead alert-success alert">Your MapleStory account has been successfully added to your Mapler.me account!</p>
<?php

		}
	}
}



$q = $__database->query("
SELECT 
	username
FROM 
	users
WHERE
	account_id = ".$__url_useraccount->GetId()."
");

?>

<h3>Currently registered MapleStory accounts</h3>
<p class="alert-info alert">Note that if you are using your e-mail address to login into MapleStory, your username shown on this page will not be the same.</p>
<ul>
<?php while ($row = $q->fetch_assoc()): ?>
	<li><?php echo $row['username']; ?></li>
<?php endwhile; ?>
</ul>

<h3>Register a MapleStory account</h3>
<?php if (!$maycheck): ?>
<p class="lead alert-error alert">You have to wait <?php echo $minutes_timeout; ?> minute<?php echo $minutes_timeout > 1 ? 's' : ''; ?>...</p>
<?php else: ?>
<form class="form-horizontal" action="" method="post">
	<div class="control-group">
		<label class="control-label" for="inputEmail">Email or Username</label>
		<div class="controls">
			<input type="text" id="inputEmail" name="username" placeholder="Email" />
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="inputPassword">Password</label>
		<div class="controls">
			<input type="password" id="inputPassword" name="password" placeholder="Password" />
		</div>
	</div>
	<div class="control-group">
		<div class="controls">
			<button type="submit" class="btn">Sign in</button>
		</div>
	</div>
</form>
<?php endif; ?>

<?php
require_once '../inc/footer.php';
?>