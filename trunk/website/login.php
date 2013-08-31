<?php

require_once __DIR__.'/inc/functions.php';
error_reporting(E_ALL);
$error = null;

if (isset($__url_useraccount)) {
	$title = $__url_useraccount->GetNickname()." &middot; Mapler.me";
}
else {
	$title = "Mapler.me &middot; MapleStory Social Network";
}

if ($_loggedin) {
	$rank = $_loginaccount->GetAccountRank();
}

function _AddHeaderLink($what, $filename) {
	global $domain;
	switch ($what) {
		case 'css':
			$dirname = 'css';
			$extension = 'css';
			$type = 'css';
		break;
		case 'js':
			$dirname = 'js';
			$extension = 'js';
			$type = 'javascript';
		break;
	}
	
	$modificationTime = filemtime(__DIR__.'/../'.$dirname.'/'.$filename.'.'.$extension);
	if ($what == 'css') {
?>
<link rel="stylesheet" href="//<?php echo $domain; ?>/inc/<?php echo $dirname; ?>/<?php echo $filename.'.'.$extension; ?>" type="text/<?php echo $type; ?>" />
<?php
	}
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title><?php echo $title; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta name="apple-mobile-web-app-status-bar-style" content="black" />
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
	<meta name="keywords" content="maplestory, maple, story, mmorpg, maple story, maplerme, mapler, me, Mapler Me, Mapler.me, Nexon, Nexon America,
	henesys, leafre, southperry, maplestory rankings, maplestory, realtime updates, Maplestory items, MapleStory skills, guild, alliance, GMS, KMS, EMS, <?php
	if (isset($__url_useraccount)):
		echo $__url_useraccount->GetNickname().', '.$__url_useraccount->GetNickname()."'s Mapler.me";
	endif;
	?>" />
	<meta name="description" content="Mapler.me is a MapleStory social network and service providing innovative features to enhance your gaming experience!" />

	<link href='http://fonts.googleapis.com/css?family=Muli:300,400,300italic,400italic' rel='stylesheet' type='text/css' />
	
	<!-- Theme -->
	<?php
	if ($_loggedin) {
	?>
	<link href='http://<?php echo $domain; ?>/inc/css/themes/<?php echo $_loginaccount->GetTheme(); ?>.css' rel='stylesheet' type='text/css' />
	<?php
	}
	else {
	?>
    <link href='http://<?php echo $domain; ?>/inc/css/themes/light.css' rel='stylesheet' type='text/css' />
    <?php
	}
	?>
	<!-- End Theme -->
	
<?php
_AddHeaderLink('css', 'style');
_AddHeaderLink('css', 'animate.min');
_AddHeaderLink('css', 'font-awesome.min');
if (strpos($_SERVER['REQUEST_URI'], '/player/') !== FALSE ||
	strpos($_SERVER['REQUEST_URI'], '/guild/') !== FALSE) {
	_AddHeaderLink('css', 'style.player');
}

if (strpos($_SERVER['REQUEST_URI'], '/settings/') !== FALSE ||
	strpos($_SERVER['REQUEST_URI'], '/manage/') !== FALSE) {
	_AddHeaderLink('css', 'settings.style');
}
?>
	<link rel="shortcut icon" href="//<?php echo $domain; ?>/inc/img/favicon.ico" />
	<link rel="icon" href="//<?php echo $domain; ?>/inc/img/favicon.ico" type="image/x-icon" />
	
			<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js" type="text/javascript"></script>
		<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js" type="text/javascript"></script>
	
	<script>
	$(function() {
		$( ".draggable" ).draggable({ containment: "html", scroll: false });
	});
  	</script>
  	
  	<style>
        body {
            background: url('/inc/img/new-bg.png') center fixed;
            background-size: cover;
        }
        .login {
            background: url('/inc/img/login-bg.png') no-repeat;
            border-radius: 10px;
            box-shadow: 0px 0px 20px rgba(0,0,0,0.7);
            margin:0 auto;
            margin-top: 90px;
            width:450px;
            height:400px;
        }
        
        .login-extra {
            font-weight: bold;
            font-family: Helvetica, sans-serif;
            font-size:16px;
            color: #FFF;
            text-shadow: 1px 1px 5px rgba(0,0,0,0.6);
        }
        
        .login-extra a {
            color: #CCC;
        }
    </style>
</head>
<body>
<?php

if (IsLoggedin()) {
?>
<meta http-equiv="refresh" content="3;URL='/stream/'" />
<?php
    DisplayError(3);
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
		$password = $_POST['password'];
		
		// $query = $__database->query("SELECT * FROM accounts WHERE username = '".$username."'");
		$query = $__database->query("SELECT * FROM accounts WHERE email = '".$username."'");
		if ($query->num_rows == 1) {
			$row = $query->fetch_assoc();
			
			$encrypted = GetPasswordHash($password, $row['salt']);
			if ($encrypted === $row['password']) {
				$_SESSION['username'] = $row['username'];
				$_loginaccount = new Account($row);
				
				$_loggedin = true;
				
				$__database->query("UPDATE accounts SET last_login = NOW(), last_ip = '".$_SERVER['REMOTE_ADDR']."' WHERE id = ".$_loginaccount->GetID());
				/*
				$code = md5(time().' -- -- -- -- - '.$_loginaccount->GetID().' - '.$_loginaccount->GetUsername());
				$__database->query("
INSERT INTO 
	account_tokens 
VALUES 
	(".$_loginaccount->GetID().", 'login_token', '".$code."', DATE_ADD(NOW(), INTERVAL 10 YEAR))
ON DUPLICATE KEY UPDATE
	`code` = VALUES(`code`),
	`till` = VALUES(`till`)
");

				SetMaplerCookie('login_session', $code, 10 * 365);
				*/
			}
			else {
				// $error = "Oops! Your username or password provided was incorrect.";
				$error = "The login details provided were incorrect.";
			}
		}
		else {
			// $error = "Oops! Your username or password provided was incorrect.";
			$error = "The login details provided were incorrect.";
		}
		$query->free();
	}
}
?> 
    <div class="login">
        <form method="POST" style="padding-top:180px;">
        <?php
if ($_loggedin) {
	$push_to_page = (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'logoff') === FALSE && strpos($_SERVER['HTTP_REFERER'], 'login') === FALSE) ? $_SERVER['HTTP_REFERER'] : '/stream/';

?>
<meta http-equiv="refresh" content="1;URL='<?php echo $push_to_page; ?>'" />

<p class="lead alert alert-success">You successfully logged in! You'll be redirected in 1 second.<br />
If not, <a href="<?php echo $push_to_page; ?>">click here</a>.
</p>
<?php
}
else {
	if ($error != null) {
?>
<p class="alert-error alert" style="border-radius: 0px !important;font-weight:bold;">*F4* <?php echo $error; ?></p>
<?php
	}
?>
            <center>
                <input type="text" name="username" placeholder="Email" />
                <input type="password" name="password" placeholder="Password" />
                <div class="login-extra">
                    <span>Don’t have a Mapler.me account?</span>
                    <a href="//<?php echo $domain; ?>/signup/">Sign up.</a><br />
                    <span>Forgot password?</span>
                    <a href="//<?php echo $domain; ?>/resetpassword/">Reset password</a>
                </div>
            </center>
            <br />
            <center>
				<input type="submit" class="btn btn-danger btn-large" value="Login!">
            </center>
        </form>
    </div>

<?php
}
require_once __DIR__.'/inc/footer.php';
?>