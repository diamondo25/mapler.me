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

if($check->httpstatus == 200)
{
    $oauth_token = htmlentities($_SESSION['oauth_token']);
    $oauth_token_secret = htmlentities($_SESSION['oauth_token_secret']);
    $_loginaccount->SetConfigurationOption('twitter_oauth_token', $oauth_token);
    $_loginaccount->SetConfigurationOption('twitter_oauth_token_secret', $oauth_token_secret);
}

if($check->httpstatus == 489)
{
	$error = 'Your account is rate limited. Try again later.';
	return;
}

if(isset($_GET['unset'])) 
{
	unset($_SESSION['oauth_token']);
	unset($_SESSION['oauth_token_secret']);
    $_loginaccount->SetConfigurationOption('twitter_oauth_token', '');
    $_loginaccount->SetConfigurationOption('twitter_oauth_token_secret', '');
}

if ($error != '') {
?>
<p class="lead alert-danger alert">Error: <?php echo $error; ?></p>
<?php
}
    $twitterenabled = $_loginaccount->GetConfigurationOption('twitter_oauth_token');
?>			
			<div class="span9">
			<h2>Twitter</h2>
            <p>By connecting your Twitter account, any status messages or screenshots posted on Mapler.me will automatically be tweeted
            on your Twitter account. This is currently in beta.</p>
			<div class="span9">
				<div class="item">
					<div class="row">
						<div class="span6">
                        <?php if($twitterenabled == '') {
                        ?>
						<button onclick="redir()" class="btn btn-info"><i class="icon-twitter"></i> Connect Twitter</button>
                            <script type="text/javascript">
                            function redir()
                            {
                                window.location="/callback/process.php";
                            }
                            </script>
                            <?php
                            }
                            else {
                            ?>
                            <a href="?unset" class="btn btn-info"><i class="icon-remove"></i> Remove Connection</a>
                            <?php
                            }
                            ?>
						</div>
					</div>
				</div>
			</div>