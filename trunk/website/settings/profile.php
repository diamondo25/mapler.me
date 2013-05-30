<?php
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['name'], $_POST['nick'], $_POST['bio'], $_POST['email'])) {
	if ($error == '') {
		$name = htmlentities($_POST['name'], ENT_COMPAT, 'UTF-8');
		if (strlen(trim($name)) == 0)
			$error = 'You have to enter a fullname.';
	}
	
	if ($error == '') {
		$nick = htmlentities($_POST['nick'], ENT_COMPAT, 'UTF-8');
		$error = Account::IsCorrectNickname($name);
	}
	if ($error == '') {
		$email = htmlentities($_POST['email'], ENT_COMPAT, 'UTF-8');
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$error = "The email you entered is invalid.";
		}
	}
	
	if ($error == '') {
		$bio = htmlentities($_POST['bio'], ENT_COMPAT, 'UTF-8');
		$_loginaccount->SetFullname($name);
		$_loginaccount->SetNickname($nick);
		$_loginaccount->SetBio($bio);
		$_loginaccount->SetEmail($email);
		$_loginaccount->Save();
?>
<p class="lead alert-info alert">Successfully changed your information.</p>
<?php
	}
}

if ($error != '') {
?>
<p class="lead alert-warn alert">Error: <?php echo $error; ?></p>
<?php
}

?>
			<h2>Your Profile</h2>
			<form id="settings-form" method="post">
			<div class="span9">
				<div class="item">
					<div class="row">
						<div class="span2 label">Full Name</div>
						<div class="span4">
							<input type="text" name="name" id="inputName" value="<?php echo $_loginaccount->GetFullname(); ?>" />
						</div>
					</div>
				</div>
				<div class="item">
					<div class="row">
						<div class="span2 label">Nickname</div>
						<div class="span4">
							<input type="text" name="nick" id="inputNick" value="<?php echo $_loginaccount->GetNickname(); ?>" />
						</div>
					</div>
				</div>
				<div class="item">
					<div class="row">
						<div class="span2 label">Bio</div>
						<div class="span4">
							<textarea class="span2" style="min-height:100px;" type="text" name="bio" id="inputBio"><?php echo $_loginaccount->GetBio(); ?></textarea>
						</div>
					</div>
				</div>
				<div class="item">
					<div class="row">
						<div class="span2 label">Email</div>
						<div class="span4">
							<input type="text" name="email" id="inputEmail" value="<?php echo $_loginaccount->GetEmail(); ?>" />
						</div>
					</div>
				</div>
				<div class="item">
					<div class="controls">
						<button type="submit" class="btn btn-primary">Save</button>
					</div>
				</div>
			</div>
			</form>