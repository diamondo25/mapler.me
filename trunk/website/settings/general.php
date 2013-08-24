<?php
$error = '';

	$themes = array();
    $themes[] = array('default', 'Default', 'Mapler.me\'s current design.');
    $themes[] = array('light', 'Light', 'A lighter design with subtle feel.');
    $themes[] = array('minimal', 'Minimal', 'A minimalistic design.');

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
    	$theme = $_POST['theme'];
    	$themecheck = array('default', 'light', 'minimal');
    	if (!in_array($theme, $themecheck)) {
        	$error = "The theme you requested does not exist. Please do not attempt to hack the site.";
    	}
	}
	
	if ($error == '') {
		$bio = htmlentities($_POST['bio'], ENT_COMPAT, 'UTF-8');
		$_loginaccount->SetFullname($name);
		$_loginaccount->SetNickname($nick);
		$_loginaccount->SetBio($bio);
		$_loginaccount->SetEmail($email);
		$_loginaccount->SetConfigurationOption('theme', $theme);
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

	$currenttheme = $_loginaccount->GetTheme();

?>

<style>
.label {
	background-color: transparent !important;
	}
</style>

<script type="text/javascript">
function DeleteAccount() {
	if (confirm("Are you sure you want to delete your account?")) {
	   if (confirm("All content, statuses, and characters will be removed. Are you 100% sure?")) {
		document.location.href = '/goodbye/';
		}
	}
}
</script>

			<h2>General Settings</h2>
			<form id="settings-form" method="post">
			<div class="span9">
				<div class="item">
					<div class="row">
						<div class="span2 label setting-label">Full Name</div>
						<div class="span4">
							<input type="text" name="name" id="inputName" value="<?php echo $_loginaccount->GetFullname(); ?>" />
						</div>
					</div>
				</div>
				<div class="item">
					<div class="row">
						<div class="span2 label setting-label">Nickname</div>
						<div class="span4">
							<input type="text" name="nick" id="inputNick" value="<?php echo $_loginaccount->GetNickname(); ?>" />
						</div>
					</div>
				</div>
				<div class="item">
					<div class="row">
						<div class="span2 label setting-label">Bio</div>
						<div class="span4">
							<textarea class="span2" style="min-height:100px;" type="text" name="bio" id="inputBio"><?php echo $_loginaccount->GetBio(); ?></textarea>
						</div>
					</div>
				</div>
				<div class="item">
					<div class="row">
						<div class="span2 label setting-label">Email</div>
						<div class="span4">
							<input type="text" name="email" id="inputEmail" value="<?php echo $_loginaccount->GetEmail(); ?>" />
						</div>
					</div>
				</div>
				<div class="item">
				    <div class="row">
				        <div class="span2 label setting-label">Theme</div>
				        <div class="span4">
				            <select name="theme" style="height:35px !important;width: 150px !important;">
<?php foreach ($themes as $themeid => $data): ?>
							<option value="<?php echo $data[0]; ?>"<?php echo $currenttheme == $data[0] ? ' selected="selected"' : ''; ?>><?php echo $data[1]; ?></option>
<?php endforeach; ?>

						  </select>
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
						<div class="span2 label setting-label">Username</div>
						<div class="span4">
							<input type="text" name="twitch" id="inputName" value="<?php echo $_loginaccount->GetConfigurationOption('twitch_username'); ?>" />
						</div>
					</div>
				</div>
				<div class="item">
					<div class="row">
						<div class="span2 label setting-label">API Code</div>
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
			
			<div class="span9">
			<h2>Other options:</h2>
			<div class="span9">
				<div class="item">
					<div class="row">
						<div class="span6">
						<button class="btn btn-danger" onclick="DeleteAccount()">Delete Account</button>
						</div>
					</div>
				</div>
			</div>
			