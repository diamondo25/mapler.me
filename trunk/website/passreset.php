<?php
require_once __DIR__.'/inc/header.php';
error_reporting(E_ALL);
$error = null;

if (IsLoggedin()) {
?>
<meta http-equiv="refresh" content="3;URL='/stream/'" />
<p class="lead alert alert-info">You are logged in! You'll be redirected to the main page in 3 seconds. If not, <a href="/">click here</a>.</p>
<?php
	require_once __DIR__.'/inc/footer.php';
	die(); // Prevent error after login
}
if (isset($_GET['code'])) {
    
    $code = $__database->real_escape_string($_GET['code']);
    $query = $__database->query("SELECT account_id FROM maplestats.account_tokens WHERE code = '".$code."' AND type = 'password_reset' AND till > NOW()");
    
    if ($query->num_rows == 0) {
        $query->free();
?>
    <br />
    <p class="lead alert-error alert">The token you requested expired or didn't exist.</p>
<?php
        die;
    }
    
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if ($_POST['password'] == $_POST['password-verify']) {
        $code = $__database->real_escape_string($_GET['code']);
		$query = $__database->query("SELECT account_id FROM maplestats.account_tokens WHERE code = '".$code."' AND type = 'password_reset' AND till > NOW()");
		
		if ($query->num_rows == 1) {
			$row = $query->fetch_row();
			$id = $row[0];
			$query->free();
			
			
			$salt = '';
			for ($i = 0; $i < 8; $i++) {
				$salt .= chr(0x30 + rand(0, 20));
			}
			
			$encryptedpassword = GetPasswordHash($_POST['password'], $salt);
			
			$__database->query("DELETE FROM maplestats.account_tokens WHERE code = '".$code."' AND type = 'password_reset'");
			$__database->query("UPDATE maplestats.accounts SET `password` = '".$encryptedpassword."', salt = '".$__database->real_escape_string($salt)."' WHERE id = ".$id);
		
?>
<p class="lead alert-success alert">Password reset!</p>
<?php
		}
		else {
?>
<p class="lead alert-error alert">The token you requested expired or didn't exist.</p>
<?php
		}
	}
	else
	{
?>
<p class="lead alert-error alert">Passwords didn't match.</p>
<?php
	}
	}

?>

	<div class="row login">
		<div class="span12 left_box">

			<h3>Reset your password below:</h3>
            <br />
						<div class="form">
                            <form id="pwForm" method="POST">
                            <input id="txtPass" type="password" name="password" placeholder="New password">
                            <input id="txtPassVerify" type="password" name="password-verify" placeholder="Confirm password">
                            <br>
                            <input id="btnSubmit" type="submit" value="Reset password">
                            </form>
						</div>

					</div>
				</div>
<?php

}
else {

	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		$email = $__database->real_escape_string($_POST['username']);
		
		$query = $__database->query("SELECT id, username FROM maplestats.accounts WHERE email = '".$email."'");
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$error = "The email you entered is invalid.";
		}
        
		elseif ($query->num_rows == 1) {
			$row = $query->fetch_row();
			$id = $row[0];
			$username = $row[1];
			$query->free();
			// Check if already sent
			$query = $__database->query("SELECT 1 FROM maplestats.account_tokens WHERE account_id = ".$id." AND type = 'password_reset' AND till > NOW()");
		
			if ($query->num_rows == 0) {
				$code = md5(time().' -- -- -- -- - '.$id.' - '.$username);
				$message = <<<END
Dear {USERNAME},<br />
<br />
A password reset has been requested by someone with the IP {IP}.<br />
You can reset your password by clicking the following link:<br />
<a href="http://{DOMAIN}/resetpassword/?code={CODE}">http://{DOMAIN}/resetpassword/?code={CODE}</a><br />
If you did not request the password reset, you can simply ignore this e-mail, as the token will expire after 1 day. We do suggest, however, to report the IP address to support@mapler.me to prevent this from happening a second time.<br />
<br />
Have a nice day,<br />
The Mapler.me team
END;
				$message = str_replace(
				array('{USERNAME}', '{DOMAIN}', '{CODE}', '{IP}'),
				array($username, $domain, $code, $_SERVER['REMOTE_ADDR']),
				$message);
				
				

				// subject
				$subject = 'Mapler.me - Password reset request';

				// To send HTML mail, the Content-type header must be set
				$headers  = 'MIME-Version: 1.0' . "\n";
				$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\n";
				$headers .= 'From: Mapler.me <no-reply@mapler.me>' . "\n";
				$headers .= 'To: '.$email . "\n";

				// Mail it
				mail($email, $subject, $message, $headers);
				
				$__database->query("
INSERT INTO 
	maplestats.account_tokens 
VALUES 
	(".$id.", 'password_reset', '".$code."', DATE_ADD(NOW(), INTERVAL 1 DAY))
ON DUPLICATE KEY UPDATE
	`code` = VALUES(`code`),
	`till` = VALUES(`till`)
			");
?>
<p class="lead alert-success alert">An e-mail has been sent to your e-mail address with a link to reset your password.</p>
<?php
			}
			else {
				$error = "There's already a password request done!";
			}
		}
		else {
			$error = "We couldn't find an account with that e-mail.";
		}
		$query->free();
	}

	if ($error != null) {
?>
<p class="lead alert-error alert"><?php echo $error; ?></p>
<?php
	}
?>

	<div class="row login">
		<div class="span12">
			<h3>Request a password change:</h3>
            <p>If you've forgotten your password to your Mapler.me account, you can request a change by entering the email used for your account below:</p>
						<div class="form">
							<form method="POST">
								<input type="text" name="username" placeholder="Email">
								<input type="submit" value="Request password reset">
							</form>
						</div>

					</div>
				</div>
<?php
}
require_once __DIR__.'/inc/footer.php';
?>