<?php
include_once('inc/header.php');

$error = null;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (!CheckArrayOf($_POST, array("username", "password", "password2", "fullname", "email", "nickname"), $errorList)) {
		$error = "The input you've entered has some errors. Please correct these errors and try again.";
	}
	else {
		// Validate email
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
			$result = $__database->query("SELECT id FROM accounts WHERE username = '".$username."'");
			if ($result->num_rows == 1) {
				$error = "This username has already been taken.";
				$errorList['username'] = true;
			}
			$result->free();
		}
		
		if (count($errorList) == 0) {
			// Check nickname
			$nickname = $__database->real_escape_string($_POST['nickname']);
			$result = $__database->query("SELECT id FROM accounts WHERE nickname = '".$nickname."'");
			if ($result->num_rows == 1) {
				$error = "This nickname has already been taken.";
				$errorList['nickname'] = true;
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
			
			$statement = $__database->prepare("INSERT INTO accounts 
				(id, username, password, salt, full_name, email, nickname, last_login, last_ip) VALUES
				(NULL,?,?,?,?,?,?,NOW(),?)");
			$statement->bind_param('sssssss', $username, $encryptedpassword, 
				$__database->real_escape_string($salt), $fullname, 
				$__database->real_escape_string($_POST['email']), $__database->real_escape_string($_POST['nickname']),
				$ip_address);
	
			$statement->execute();
			
			if ($statement->affected_rows == 1) {
				echo 'Created!';
			}
		}
		// echo $encryptedpassword;
	}
}


if ($_loggedin) {
?>
<meta http-equiv="refresh" content="1;URL='/'" />
You'll be redirected to the main page in 1 second. If not, <a href="/">click here</a>.
<?php
}
else {
	if ($error != null) {
?>
<p class="lead alert-error alert"><?php echo $error;?></p>
<?php
	}
?>
<p class="lead">Register for a Mapler.me account</p><img src="https://dl.dropbox.com/u/22875564/Random/lulzbean.png" class="pull-right"/>
<?php
	
	$form = new Form('', 'form-horizontal');
	$form->AddBlock('Username', 'username', (isset($errorList['username']) ? 'error' : ''), 'text', @$_POST['username']);
	$form->AddBlock('Password', 'password', (isset($errorList['password']) ? 'error' : ''), 'password');
	$form->AddBlock('Password (again)', 'password2', (isset($errorList['password']) ? 'error' : ''), 'password');
	$form->AddEmptyBlock();
	$form->AddBlock('Full name', 'fullname', (isset($errorList['fullname']) ? 'error' : ''), 'text', @$_POST['fullname']);
	$form->AddBlock('Nickname', 'nickname', (isset($errorList['nickname']) ? 'error' : ''), 'text', @$_POST['nickname']);
	$form->AddBlock('E-mail', 'email', (isset($errorList['email']) ? 'error' : ''), 'text', @$_POST['email']);
	$form->Agreement();
	$form->MakeSubmit('Register');
	
	$form->End();
	
}

include_once('inc/footer.php');
?>