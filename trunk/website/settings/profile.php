<?php
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['name'], $_POST['nick'], $_POST['bio'], $_POST['email'])) {
	if ($error == '') {
		$name = htmlentities($_POST['name'], ENT_COMPAT, 'UTF-8');
		if (strlen(trim($name)) == 0)
			$error = 'You have to enter a full name.';
	}
	
	if ($error == '') {
		$nick = htmlentities($_POST['nick'], ENT_COMPAT, 'UTF-8');
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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['topsecret'], $_POST['twitch'])) {
	if ($error == '') {
		$twitchtopsecret = htmlentities($_POST['topsecret'], ENT_COMPAT, 'UTF-8');
		if (strlen(trim($twitchtopsecret)) == 0)
			$error = 'You have to enter your account\'s API code to allow Twitch.tv to function.';
	}
	if ($error == '') {
		$twitchname = htmlentities($_POST['twitch'], ENT_COMPAT, 'UTF-8');
		if (strlen(trim($twitchname)) == 0)
			$error = 'You have to enter your Twitch.tv username to connect it to Mapler.me!';
	}
	
	if ($error == '') {
		//execute
		$_loginaccount->SetConfigurationOption('twitch_username', $twitchname);
		$_loginaccount->SetConfigurationOption('twitch_api_code', $twitchtopsecret);
	}
}

if ($error != '') {
?>
<p class="lead alert-warn alert">Error: <?php echo $error; ?></p>
<?php
}

?>

<style>
.label {
	background-color: transparent !important;
	}
</style>

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
			
			<br />
			
			<h2>Twitch.tv</h2>
			<p>Automatically display your Twitch.tv stream on your Mapler.me profile whenever you play MapleStory! <br/> Visit <a href="http://www.twitch.tv/settings/applications">http://www.twitch.tv/settings/applications</a> and create an application to obtain an API code.</p>
			<form id="settings-form" method="post">
			<div class="span9">
				<div class="item">
					<div class="row">
						<div class="span2 label">Username</div>
						<div class="span4">
							<input type="text" name="twitch" id="inputName" value="<?php echo $_loginaccount->GetConfigurationOption('twitch_username'); ?>" />
						</div>
					</div>
				</div>
				<div class="item">
					<div class="row">
						<div class="span2 label">API Code</div>
						<div class="span4">
							<input type="text" name="topsecret" id="inputNick" value="<?php echo $_loginaccount->GetConfigurationOption('twitch_api_code'); ?>" />
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