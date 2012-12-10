<?php
include_once('inc/header.php');
error_reporting(E_ALL);
$error = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (!isset($_POST['username'], $_POST['password'])) {
		$error = "Username or password not set";
	}
	else {
		$username = $__database->real_escape_string($_POST['username']);
		$password = $__database->real_escape_string($_POST['password']);
		
		$query = $__database->query("SELECT * FROM accounts WHERE username = '".$username."'");
		if ($query->num_rows == 1) {
			$row = $query->fetch_assoc();
			
			$encrypted = GetPasswordHash($password, $row['salt']);
			if ($encrypted == $row['password']) {
				
				ini_set('session.cookie_domain', substr($_SERVER['SERVER_NAME'], strrpos($_SERVER['SERVER_NAME'], ".", -5)));
				$_logindata = $_SESSION['login_data'] = $row;
				
				$_loggedin = true;
			}
			else {
				$error = "Incorrect password entered.";
			}
		}
		else {
			$error = "Unknown username!";
		}
		$query->free(); // Clear dem mem up
	}
}

if ($_loggedin) {
?>
<meta http-equiv="refresh" content="3;URL='/'" />
<p class="lead alert alert-danger">You are already logged in! You'll be redirected to the main page in 3 seconds. If not, <a href="/">click here</a>.</p>
<?php
}
else {
	if ($error != null) {
?>
<p class="lead alert-error alert"><?php echo $error;?></p>
<?php
	}
	$form = new Form('', 'form-horizontal');
	$form->AddBlock('Username', 'username', (isset($errorList['username']) ? 'error' : ''), 'text', @$_POST['username']);
	$form->AddBlock('Password', 'password', (isset($errorList['password']) ? 'error' : ''), 'password');
	$form->MakeSubmit('Login');
	
	$form->End();
}

include_once('inc/footer.php');
?>