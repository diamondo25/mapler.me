<?php
require_once('../inc/codebird.php');
require_once('../inc/functions.php');

// It works like this: 
// [1] They click the button on landing.php, that sends them to process.php where the stuff begins
// [2] It asks twitter for the token for this request and stores it.
// [3] It redirects the user to the twitter authorize page. When they accept it, twitter sends them back here.
// [4] When they're sent back here, the URL it uses includes $_GET values for the oauth info you requested. The script stores these.
// [5] The user is redirected to success.php, which would be the main page of the app where the user can do stuff.

    $CONSUMER_KEY = 'AeH4Ka2jIhiBWASIQUEQ';
    $CONSUMER_SECRET = 'RjHPE4FXqsznLGohdHzSDnOeIuEucnQ6fPc0aNq8sw';

\Codebird\Codebird::setConsumerKey($CONSUMER_KEY, $CONSUMER_SECRET);
$cb = \Codebird\Codebird::getInstance();

// [1]
if (!isset($_SESSION['oauth_token'])) 
{
	//[2]
	$reply = $cb->oauth_requestToken(array('oauth_callback' => 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']));
	$cb->setToken($reply->oauth_token, $reply->oauth_token_secret);
    $_SESSION['oauth_token'] = $reply->oauth_token;
    $_SESSION['oauth_token_secret'] = $reply->oauth_token_secret;
    $_SESSION['oauth_verify'] = true;

    // [3]
    $auth_url = $cb->oauth_authorize();
    header('Location: ' . $auth_url);
    die();
}
    
elseif (isset($_GET['oauth_verifier']) && isset($_SESSION['oauth_verify'])) 
{
	//[4]
	$cb->setToken($_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
    unset($_SESSION['oauth_verify']);

    //storing the stuff now  
    $reply = $cb->oauth_accessToken(array('oauth_verifier' => $_GET['oauth_verifier']));   
    $_SESSION['oauth_token'] = $reply->oauth_token;
    $_SESSION['oauth_token_secret'] = $reply->oauth_token_secret;
    header('Location: /settings/connections/');
    die();
}

    $check = $cb->account_verifyCredentials();

    if($check->httpstatus != 200)
    {
        $reply = $cb->oauth_requestToken(array('oauth_callback' => 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']));
        $cb->setToken($reply->oauth_token, $reply->oauth_token_secret);
        $_SESSION['oauth_token'] = $reply->oauth_token;
        $_SESSION['oauth_token_secret'] = $reply->oauth_token_secret;
        $_SESSION['oauth_verify'] = true;
        $auth_url = $cb->oauth_authorize();
        header('Location: ' . $auth_url);
        die();
    }
    else
    {
        header('Location: /settings/connections/');
    }
?>
