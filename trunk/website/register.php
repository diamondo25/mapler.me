<?php
require_once __DIR__.'/inc/header.php';
	
$x = $__database->query('
SELECT
	status
FROM
	signup_lock
');

$row = $x->fetch_assoc();
if($row['status'] == '1') {
?>
	<p>
		<center class="status lead">
				<img src="//<?php echo $domain; ?>/inc/img/icon.png" width="200px" /><br />
				We're sorry! The amount of new members today has reached it's max.<br />
				Come back tomorrow!<br/>
			<sub>Tip: The limit resets at <b>8AM</b> (PST / MapleStory Time)</sub>
		</center>
	</p>
<?php
	require_once __DIR__.'/inc/footer.php';
	die;
}



$username_regex = '/^[a-z0-9-_]+$/';

$error = null;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (!CheckArrayOf($_POST, array('username', 'password', 'password2', 'fullname', 'email', 'nickname'), $errorList)) {
		$error = 'The input you\'ve entered has some errors. Please correct these errors and try again.';
	}
	else {
		// Validate email
		$email = $__database->real_escape_string($_POST['email']);
		if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
			$error = 'The email you entered is invalid.';
			$errorList['email'] = true;
		}
		else {
			$email = $__database->real_escape_string($_POST['email']);
		}

		if (count($errorList) == 0) {
			$result = $__database->query("SELECT id FROM ".DB_ACCOUNTS.".accounts WHERE email = '".$email."'");
			if ($result->num_rows == 1) {
				$error = 'The email you entered is already used.';
				$errorList['email'] = true;
			}
			$result->free();
		}
		
		if (count($errorList) == 0) {
			// Check ToU
			$notou = !isset($_POST['tou']);
			if ($notou) {
				$error = 'You didn\'t accept our Terms of Service. This is required to sign up for Mapler.me.';
				$errorList['ToU'] = true;
			}
		}
		
		if (count($errorList) == 0) {
			// Check passwords
			if ($_POST['password'] != $_POST['password2']) {
				$error = 'Your passwords didn\'t match, please try again.';
				$errorList['password'] = true;
			}
		}
		
		if (count($errorList) == 0) {
			// Check username
			$username = $__database->real_escape_string($_POST['username']);
			$len = strlen($username);
			$disallowed = array('nexon', 'nexonamerica', 'wizet', 'hacker', 'waltzing', 'maple', 'maplestory', 
			'staff', 'admin', 'administrator', 'moderator', 'team', 'hack', 'hacking', 'mesos', 'meso', 'fuck', 
			'shit', 'asshole', 'nigger', 'faggot', 'cunt', 'pussy', 'dick', 'vagina', 'penis', 'mail', 'cdn', 
			'user', 'users', 'contact', 'support', 'legal', 'sales', 'bitch', 'whore', 'slut',
			'maryse');
			if ($len < 4 || $len > 20) {
				$error = 'A Mapler.me username has to be between four and twenty characters long.';
				$errorList['username'] = true;
			}
			elseif (preg_match($username_regex, $username) == 0) {
				$error = 'A Mapler.me username may only hold lowercase alphanumeric characters.';
				$errorList['username'] = true;
			}
			else {
				$nope = false;
				foreach ($disallowed as $name) {
					if (strpos($username, $name) !== FALSE) {
						$nope = true;
						break;
					}
				}
				if ($nope) {
					$error = 'That username is disallowed, please choose another.';
					$errorList['username'] = true;
				}
				else {
					$result = $__database->query("SELECT id FROM ".DB_ACCOUNTS.".accounts WHERE username = '".$username."'");
					if ($result->num_rows == 1) {
						$error = 'This username has already been taken, please try another.';
						$errorList['username'] = true;
					}
					$result->free();
				}
			}
		}
		
		if (count($errorList) == 0) {
			// Check nickname
			$nickname = $__database->real_escape_string($_POST['nickname']);
			$len = strlen($nickname);
			if ($len < 4 || $len > 20) {
				$error = 'Nickname must be at least 4 and at max 20 characters long.';
				$errorList['nickname'] = true;
			}
		}
		
		//if (count($errorList) == 0) {
		//	// Check beta key
		//	$key = $__database->real_escape_string($_POST['key']);
		//	$result = $__database->query('SELECT 1 FROM beta_invite_keys WHERE invite_key = ''.$key.'' AND assigned_to IS NULL');
		//	if ($result->num_rows == 1) {
		//		$__database->query('UPDATE beta_invite_keys SET assigned_to = ''.$username.'' WHERE invite_key = ''.$key.''');
		//	}
		//	else {
		//		$error = 'Incorrect beta code, or it was already used!'; // Default response!
		//		$errorList['key'] = true;
		//	}
		//	$result->free();
		// }
		
		
		if (count($errorList) == 0) {
			// Add account
			$salt = '';
			for ($i = 0; $i < 8; $i++) {
				$salt .= chr(0x30 + rand(0, 20));
			}
			
			$encryptedpassword = GetPasswordHash($_POST['password'], $salt);
			
			$ip_address = $_SERVER['REMOTE_ADDR'];
			$fullname = $__database->real_escape_string($_POST['fullname']);
			$nickname = $__database->real_escape_string($_POST['nickname']);
			
			$statement = $__database->prepare('INSERT INTO accounts 
				(id, username, password, salt, full_name, email, nickname, last_login, last_ip, registered_on) VALUES
				(NULL,?,?,?,?,?,?,NOW(),?,NOW())');
			$statement->bind_param('sssssss', $username, $encryptedpassword, 
				$__database->real_escape_string($salt), $fullname, 
				$email, $nickname,
				$ip_address);
	
			$statement->execute();
			
			if ($statement->affected_rows == 1) {
			
				$to = $email;

				// subject
				$subject = 'Mapler.me - Welcome!';

				// message
				
				$message = file_get_contents('inc/templates/emails/signup.php');

				// To send HTML mail, the Content-type header must be set
				$headers  = 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
				$headers .= 'From: Mapler.me <no-reply@mapler.me>' . "\r\n";

				// Mail it
				mail($to, $subject, $message, $headers);
?>
<p class="lead alert-info alert">Welcome to Mapler.me, <?php echo $nickname; ?>! Check your email (<?php echo $email; ?>) for more information! Note: <b>Check your spam folder if you don't receive it after a few minutes!</b></p>
<?php
			}
		}
	}
}


if ($_loggedin) {
?>
<meta http-equiv="refresh" content="3;URL='/'" />
<?php DisplayError(2); ?>

<?php
}
else {
	if ($error != null) {
?>
<p class="lead alert-error alert"><?php echo $error;?></p>
<?php
	}

?>

<div class="row">
<div class="span12">
<p class="lead">Sign up for a Mapler.me account..</p>
<img src="https://dl.dropbox.com/u/22875564/Random/lulzbean.png" class="pull-right"/><p>You're only a few steps away from joining hundreds of other Maplers!</p>
</div>

<div class="span12">
<?php
	
	$form = new Form('', 'form-horizontal');
	$form->AddBlock('Display Name:<br/><sup>(your profile name, such as <strong>name</strong>.mapler.me).</sup>', 'username', (isset($errorList['username']) ? 'error' : ''), 'text', @$_POST['username']);
	$form->AddBlock('Password', 'password', (isset($errorList['password']) ? 'error' : ''), 'password');
	$form->AddBlock('Password<br/><sup>(again for confirmation)</sup>', 'password2', (isset($errorList['password']) ? 'error' : ''), 'password');
	$form->AddEmptyBlock();
	$form->AddBlock('Full name<br/><sup>(kept private)</sup>', 'fullname', (isset($errorList['fullname']) ? 'error' : ''), 'text', @$_POST['fullname']);
	$form->AddBlock('Nickname<br/><sup>(what is shown publicly)</sup>', 'nickname', (isset($errorList['nickname']) ? 'error' : ''), 'text', @$_POST['nickname']);
	$form->AddBlock('E-mail<br/><sup>(for email notifications)</sup>', 'email', (isset($errorList['email']) ? 'error' : ''), 'text', @$_POST['email']);
	$form->Agreement();
	$form->MakeSubmit('Sign up!');
	
	$form->End();
	
}
?>
</div>
</div>
<?php
require_once __DIR__.'/inc/footer.php';
?>