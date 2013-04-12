<?php
require_once __DIR__.'/inc/header.php';
error_reporting(E_ALL);
$error = null;

if (IsLoggedin()) {
?>
<meta http-equiv="refresh" content="3;URL='/stream/'" />
<p class="lead alert alert-danger">You are already logged in! You'll be redirected to the main page in 3 seconds. If not, <a href="/">click here</a>.</p>
<?php
	require_once __DIR__.'/inc/footer.php';
	die(); // Prevent error after login
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (!isset($_POST['username'], $_POST['password'])) {
		// $error = "Opps! Your username or password was not included.";
		$error = "Opps! Your e-mail or password was not included.";
	}
	else {
		$username = $__database->real_escape_string($_POST['username']);
		$password = $__database->real_escape_string($_POST['password']);
		
		// $query = $__database->query("SELECT * FROM accounts WHERE username = '".$username."'");
		$query = $__database->query("SELECT * FROM accounts WHERE email = '".$username."'");
		if ($query->num_rows == 1) {
			$row = $query->fetch_assoc();
			
			$encrypted = GetPasswordHash($password, $row['salt']);
			if ($encrypted === $row['password']) {
				$_SESSION['username'] = $row['username'];
				$_loginaccount = new Account($row);
				
				$_loggedin = true;
				
				$__database->query("UPDATE accounts SET last_login = NOW(), last_ip = '".$_SERVER['REMOTE_ADDR']."' WHERE id = '".$_loginaccount->GetID()."'");
			}
			else {
				// $error = "Oops! Your username or password provided was incorrect.";
				$error = "Oops! Your e-mail or password provided was incorrect.";
			}
		}
		else {
			// $error = "Oops! Your username or password provided was incorrect.";
			$error = "Oops! Your e-mail or password provided was incorrect.";
		}
		$query->free();
	}
}

if ($_loggedin) {
	$push_to_page = (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'logoff') === FALSE && strpos($_SERVER['HTTP_REFERER'], 'login') === FALSE) ? $_SERVER['HTTP_REFERER'] : '/stream/';

?>
<meta http-equiv="refresh" content="3;URL='<?php echo $push_to_page; ?>'" />

<p class="lead alert info-danger">You successfully logged in! You'll be redirected in 3 seconds.<br />
If not, <a href="<?php echo $push_to_page; ?>">click here</a>.
</p>
<?php
}
else {
	if ($error != null) {
?>
<p class="lead alert-error alert"><?php echo $error; ?></p>
<?php
	}
?>

<?php
$q = $__database->query("
SELECT
	name
FROM
	characters 
WHERE
	level > 30
ORDER BY
	rand()
	LIMIT 5
");
$cache = array();

while ($row = $q->fetch_assoc()) {
	$cache[] = $row;
}
$q->free();
?>

<div class="row">
	<div class="span9 offset2 logincharacter">
<?php
foreach ($cache as $row) {
?>
		<a href="//<?php echo $domain; ?>/player/<?php echo $row['name']; ?>" style="text-decoration: none !important; font-weight: 300; color: inherit;">
			<img src="//<?php echo $domain; ?>/avatar/<?php echo $row['name']; ?>" title="<?php echo $row['name']; ?>"/>
		</a>
<?php
}
?>
	</div>
	
	<div class="span12">
		<center>
			<h1>Welcome back, hundreds of other maplers await your return!</h1>
			<br />
		</center>
	</div>
	<div class="span12">
<?php
	$form = new Form('', 'form-horizontal span4 loginpageform');
	$form->AddBlock('E-mail', 'username', (isset($errorList['username']) ? 'error' : ''), 'text', @$_POST['username']);
	$form->AddBlock('Password', 'password', (isset($errorList['password']) ? 'error' : ''), 'password');
	$form->MakeSubmit('Login');
	$form->End();
}
?>
	</div>
<?php
require_once __DIR__.'/inc/footer.php';
?>