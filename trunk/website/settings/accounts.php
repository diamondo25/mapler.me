		<div class="span7">
		
<script type="text/javascript">
function RemoveAccount(id) {
	if (confirm("Are you sure you want to delete this account? Doing so will remove all characters aswell!!!! THIS IS IRREVERSIBLE!!!")) {
		document.location.href = '?removeid=' + id;
	}
}
</script>
<?php

$maycheck = true;
if ($_loginaccount->GetConfigurationOption('last_account_addition', 0) != 0) {
	$tmp = strtotime($_loginaccount->GetConfigurationOption('last_account_addition'));
	$tmp += 4 * 60; // 3 minutes
	
	if (time() < $tmp) { // 3 minutes
		$maycheck = false;
		$minutes_timeout = ceil(($tmp - time()) / 60);
	}

}


if ($maycheck && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['username'], $_POST['password'])) {
	// Oh jolly
	$maycheck = false;
	$minutes_timeout = 4;
	$_loginaccount->SetConfigurationOption('last_account_addition', date("Y-m-d H:i:s"));

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
	elseif ($matches[1] == '503') {
?>
<p class="lead alert-error alert">Nexon's login servers are currently under maintenance. Try at a later time!</p>
<?php
	}
	elseif ($matches[1] != '200') {
?>
<p class="lead alert-error alert">The login details provided was incorrect, try again?</p>
<?php
	}
	else {
		preg_match('/Set-Cookie: session=(.*?);/', $result, $matches);
		$real_username = $matches[1];
		
		
		$q = $__database->query("SELECT 1 FROM users_weblogin WHERE name = '".$__database->real_escape_string($real_username)."'");
		if ($q->num_rows != 0) {
?>
<p class="lead alert-error alert">This account has already been added to a Mapler.me account! If the account is not shown above and you are still encountering this error, please contact support!</p>
<?php
		
		}
		else {
			if (strtolower($real_username) != strtolower($post_values['userID'])) { 
?>
<p class="lead alert-info alert">TIP: Your 'real' username is <strong><?php echo $real_username; ?></strong>. You can use this instead of your e-mail address to login on MapleStory!</p>
<?php
			}
			
			$__database->query("INSERT INTO users_weblogin VALUES (NULL, ".$_loginaccount->GetId().", '".$__database->real_escape_string($real_username)."', '".$__database->real_escape_string($_POST['username'])."')");
		
?>
<p class="lead alert-success alert">Your MapleStory account has been successfully added to your Mapler.me account!</p>
<?php

		}
	}
}
elseif ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['removeid'])) {
	// Removing account, please stand by...
	
	$username = $__database->real_escape_string($_GET['removeid']);
	
	$__database->query("DELETE FROM users WHERE username = '".$username."' AND account_id = ".$_loginaccount->GetId());
	$__database->query("DELETE FROM users_weblogin WHERE name = '".$username."' AND account_id = ".$_loginaccount->GetId());
?>
<p class="lead alert-info alert">Deleted this account from Mapler.me! :(</p>
<?php
}



$q = $__database->query("
SELECT
	t.username,
	t.email,
	t.remove_id
FROM
	(
		SELECT
			userwl.name AS `username`,
			userwl.email AS `email`,
			userwl.name AS `remove_id`
		FROM 
			`users_weblogin` userwl
		WHERE
			userwl.account_id = ".$_loginaccount->GetId()."
	UNION
		SELECT
			user.username AS `username`,
			'' AS `email`,
			user.username AS `remove_id`
		FROM 
			`users` user
		WHERE
			user.account_id = ".$_loginaccount->GetId()."
	) AS t
GROUP BY
	t.username
");

?>

<h3>Your Accounts</h3>
<p>In order to connect your account to Mapler.me, you must login to your account below. All confidential information is discarded after connecting an account.</p>
<p class="alert-info alert">Note that if you are using your e-mail address to login into MapleStory, your username shown on this page will not be the same.</p>
<ul>
<?php while ($row = $q->fetch_row()): ?>
<div class="btn-group">
	<button class="btn">
	<?php echo $row[1] != '' ? ($row[1].' ('.$row[0].')') : $row[0]; ?>
	</button>
	<button class="btn btn-danger" onclick="RemoveAccount('<?php echo $row[2]; ?>')">
		x
	</button>
</div>
<?php endwhile; ?>
</ul>
<div style="clear:both;"></div>
<h3>Connect an account:</h3>
<?php if (!$maycheck): ?>
<p class="lead alert-error alert">Error: You have to wait <?php echo $minutes_timeout; ?> minute<?php echo $minutes_timeout > 1 ? 's' : ''; ?> before trying again...</p>
<p>Reason: When you connect a Nexon account, you are logged in through Nexon's login API which can use up their resources. Additionally, to allow Nexon to track players connecting their accounts to Mapler.me, it also states that the login came from here. To prevent abuse or damage, we permit one attempt every three minutes.
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
					<button type="submit" class="btn btn-primary">Connect this account to Mapler.me</button>
				</div>
			</div>
		</form>
<?php endif; ?>
		</div>