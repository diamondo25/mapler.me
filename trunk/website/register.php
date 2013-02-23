<?php
include_once('inc/header.php');

$error = null;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (!CheckArrayOf($_POST, array("username", "password", "password2", "fullname", "email", "nickname", "key"), $errorList)) {
		$error = "The input you've entered has some errors. Please correct these errors and try again.";
	}
	else {
		// Validate email
		$email = $__database->real_escape_string($_POST['email']);
		if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
			$error = "The email you entered is invalid.";
			$errorList["email"] = true;
		}
		
		if (count($errorList) == 0) {
			// Check ToU
			$notou = !isset($_POST['tou']);
			if ($notou) {
				$error = "You didn't accept the ToU";
				$errorList['ToU'] = true;
			}
		}
		
		if (count($errorList) == 0) {
			// Check passwords
			if ($_POST['password'] != $_POST['password2']) {
				$error = "Your passwords didn't match.";
				$errorList['password'] = true;
			}
		}
		
		if (count($errorList) == 0) {
			// Check username
			$username = $__database->real_escape_string($_POST['username']);
			$len = strlen($username);
			if ($len < 4 || $len > 20) {
				$error = "Username must be at least 4 and at max 20 characters long.";
				$errorList['username'] = true;
			}
			else {
				$result = $__database->query("SELECT id FROM accounts WHERE username = '".$username."'");
				if ($result->num_rows == 1) {
					$error = "This username has already been taken.";
					$errorList['username'] = true;
				}
				$result->free();
			}
		}
		
		if (count($errorList) == 0) {
			// Check nickname
			$nickname = $__database->real_escape_string($_POST['nickname']);
			$len = strlen($nickname);
			if ($len < 4 || $len > 20) {
				$error = "Nickname must be at least 4 and at max 20 characters long.";
				$errorList['nickname'] = true;
			}
			else {
			
				$result = $__database->query("SELECT id FROM accounts WHERE nickname = '".$nickname."'");
				if ($result->num_rows == 1) {
					$error = "This nickname has already been taken.";
					$errorList['nickname'] = true;
				}
				$result->free();
			}
		}
		
		if (count($errorList) == 0) {
			// Check beta key
			$key = $__database->real_escape_string($_POST['key']);
			$result = $__database->query("SELECT 1 FROM beta_invite_keys WHERE invite_key = '".$key."' AND assigned_to IS NULL");
			if ($result->num_rows == 1) {
				$__database->query("UPDATE beta_invite_keys SET assigned_to = '".$username."' WHERE invite_key = '".$key."'");
			}
			else {
				$error = "Incorrect beta code, or it was already used!"; // Default response!
				$errorList['key'] = true;
			}
			$result->free();
		}
		
		
		if (count($errorList) == 0) {
			// Add account
			$salt = '';
			for ($i = 0; $i < 8; $i++) {
				$salt .= chr(0x30 + rand(0, 20));
			}
			
			$encryptedpassword = GetPasswordHash($_POST['password'], $salt);
			
			$ip_address = $_SERVER['REMOTE_ADDR'];
			$fullname = $__database->real_escape_string($_POST["fullname"]);
			$email = $__database->real_escape_string($_POST['email']);
			$nickname = $__database->real_escape_string($_POST['nickname']);
			
			$statement = $__database->prepare("INSERT INTO accounts 
				(id, username, password, salt, full_name, email, nickname, last_login, last_ip) VALUES
				(NULL,?,?,?,?,?,?,NOW(),?)");
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
				
				$message = file_get_contents('inc/email_signup.php');
				$message = str_replace("{NICK}", $nickname, $message);

				// To send HTML mail, the Content-type header must be set
				$headers  = 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
				$headers .= 'From: Mapler.me <no-reply@mapler.me>' . "\r\n";

				// Mail it
				mail($to, $subject, $message, $headers);
?>
<p class="lead alert-info alert">Welcome to Mapler.me, <?php echo $nickname; ?>! Check your email (<?php echo $email; ?>) for more information!</p>
<?php
			}
		}
	}
}


if ($_loggedin) {
?>
<meta http-equiv="refresh" content="3;URL='/'" />
<p class="lead alert alert-danger">You are already signed up! You'll be redirected to the main page in 3 seconds. If not, <a href="/">click here</a>.</p>

<?php
}
else {
	if ($error != null) {
?>
<p class="lead alert-error alert"><?php echo $error;?></p>
<?php
	}
?>
<p class="lead">Sign up for a Mapler.me account</p>

<div class="pull-right"><img src="https://dl.dropbox.com/u/22875564/Random/lulzbean.png" class="pull-right"/><p>Sign up is currently only available to those invited as a Beta Tester. If you were given a code, use it in the form to the left!</p>

<p>In order to provide a robust, amazing experience, we have opened our doors to a select group of players. We will work together with these individuals to craft a service crafted better then the best Rising Sun Pendent.</p></div>
<?php
	
	$form = new Form('', 'form-horizontal');
	$form->AddBlock('Username', 'username', (isset($errorList['username']) ? 'error' : ''), 'text', @$_POST['username']);
	$form->AddBlock('Password', 'password', (isset($errorList['password']) ? 'error' : ''), 'password');
	$form->AddBlock('Password (again)', 'password2', (isset($errorList['password']) ? 'error' : ''), 'password');
	$form->AddEmptyBlock();
	$form->AddBlock('Full name', 'fullname', (isset($errorList['fullname']) ? 'error' : ''), 'text', @$_POST['fullname']);
	$form->AddBlock('Nickname', 'nickname', (isset($errorList['nickname']) ? 'error' : ''), 'text', @$_POST['nickname']);
	$form->AddBlock('E-mail', 'email', (isset($errorList['email']) ? 'error' : ''), 'text', @$_POST['email']);
	$form->AddBlock('Beta Key', 'key', (isset($errorList['key']) ? 'key' : ''), 'text', @$_POST['key']);
	$form->Agreement();
	$form->MakeSubmit('Sign up!');
	
	$form->End();
	
}

include_once('inc/footer.php');
?>