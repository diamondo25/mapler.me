<?php

require_once('../inc/codebird.php');

// Script expects the session values oauth_token and oauth_token_secret to be set already. But not important til the connection.
// No harm done.
error_reporting(0);
$error = '';

$CONSUMER_KEY = 'AeH4Ka2jIhiBWASIQUEQ';
$CONSUMER_SECRET = 'RjHPE4FXqsznLGohdHzSDnOeIuEucnQ6fPc0aNq8sw';

\Codebird\Codebird::setConsumerKey($CONSUMER_KEY, $CONSUMER_SECRET);
$cb = \Codebird\Codebird::getInstance();

$cb->setToken($_SESSION['oauth_token'], $_SESSION['oauth_token_secret']); 

$check = $cb->account_verifyCredentials();

if ($check->httpstatus == 200) {
    $oauth_token = htmlentities($_SESSION['oauth_token']);
    $oauth_token_secret = htmlentities($_SESSION['oauth_token_secret']);
    $_loginaccount->SetConfigurationOption('twitter_oauth_token', $oauth_token);
    $_loginaccount->SetConfigurationOption('twitter_oauth_token_secret', $oauth_token_secret);
}

if ($check->httpstatus == 489) {
	$error = 'Your account is rate limited. Try again later.';
	return;
}

if (isset($_GET['unset'])) {
	unset($_SESSION['oauth_token']);
	unset($_SESSION['oauth_token_secret']);
    $_loginaccount->SetConfigurationOption('twitter_oauth_token', '');
    $_loginaccount->SetConfigurationOption('twitter_oauth_token_secret', '');
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
?>
<p class="lead alert-info alert"><i class="icon-check"></i> Twitch information updated successfully.</p>
<?php
		
	}
}


if ($error != '') {
?>
<p class="lead alert-danger alert">Error: <?php echo $error; ?></p>
<?php
}
$twitterenabled = $_loginaccount->GetConfigurationOption('twitter_oauth_token');
?>

<style>
.label {
	background-color: transparent !important;
}
</style>

			<h2>Twitch.tv</h2>
			<p>Automatically display your Twitch.tv stream on your Mapler.me profile whenever you play MapleStory! <br/> Visit <a href="http://www.twitch.tv/settings/applications">http://www.twitch.tv/settings/applications</a> and create an application to obtain an API code.</p>
            <div class="span9">
			<form id="settings-form" method="post">
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
			</form>
			</div>

<br />

			<h2>Twitter</h2>
            <p>By connecting your Twitter account, any status messages or screenshots posted on Mapler.me will automatically be tweeted
            on your Twitter account. This is currently in beta.</p>
			<div class="span9">
				<div class="item">
					<div class="row">
						<div class="span6">
<?php if ($twitterenabled == ''): ?>
						<button onclick="redir()" class="btn btn-info"><i class="icon-twitter"></i> Connect Twitter</button>
                            <script type="text/javascript">
                            function redir()
                            {
                                window.location = "/callback/process.php";
                            }
                            </script>
<?php else: ?>
                            <a href="?unset" class="btn btn-info"><i class="icon-remove"></i> Remove Connection</a>
<?php endif; ?>
						</div>
					</div>
				</div>
			</div>