<?php
session_start();
include_once('functions.php');
include_once('database.php');
include_once('ranks.php');

// Initialize Login Data
$_loggedin = false;
if (isset($_SESSION['login_data'])) {
	$_logindata = $_SESSION['login_data'];
	$_loggedin = (strpos($_SERVER['REQUEST_URI'], '/logoff') === FALSE);
}

$__url_userdata = null;
$subdomain = "";
$domain = "";
if (strpos($_SERVER['SERVER_NAME'], "direct.mapler.me") !== false) {
	// SOMETHING.direct.mapler.me
	$subdomain = substr($_SERVER['SERVER_NAME'], 0, strrpos($_SERVER['SERVER_NAME'], ".direct.mapler.me"));
	$domain = "direct.mapler.me";
}
elseif (strpos($_SERVER['SERVER_NAME'], "mapler.me") !== false) {
	// SOMETHING.mapler.me
	$subdomain = substr($_SERVER['SERVER_NAME'], 0, strrpos($_SERVER['SERVER_NAME'], ".mapler.me"));
	$domain = "mapler.me";
}
elseif (strpos($_SERVER['SERVER_NAME'], "mplrtest.craftnet.nl") !== false) {
	// SOMETHING.mplrtest.craftnet.nl << Test Case Server
	$subdomain = substr($_SERVER['SERVER_NAME'], 0, strrpos($_SERVER['SERVER_NAME'], ".mplrtest.craftnet.nl"));
	$domain = "mplrtest.craftnet.nl";
}
elseif (strpos($_SERVER['SERVER_NAME'], "mplr.e.craftnet.nl") !== false) {
	// SOMETHING.mplr.e.craftnet.nl << Test Case Erwin
	$subdomain = substr($_SERVER['SERVER_NAME'], 0, strrpos($_SERVER['SERVER_NAME'], ".mplr.e.craftnet.nl"));
	$domain = "mplr.e.craftnet.nl";
}

elseif (strpos($_SERVER['SERVER_NAME'], "website") !== false) {
	// SOMETHING.website << Local Testing Tyler
	$subdomain = substr($_SERVER['SERVER_NAME'], 0, strrpos($_SERVER['SERVER_NAME'], ".website:404"));
	$domain = "website:404";
}

$subdomain = trim($subdomain);

if ($subdomain != "" && $subdomain != "www" && $subdomain != "direct" && $subdomain != "dev" && $subdomain != "social") {
	// Try to get userdata... Else: error!
	
	$username = $__database->real_escape_string($subdomain);
	$q = $__database->query("SELECT * FROM accounts WHERE username = '".$username."'");
	if ($q->num_rows > 0) {
		$__url_userdata = $q->fetch_assoc();
	}
	else {
		// User not found.
		header('Location: http://'.$domain.'/?error=user-not-found');
		die();
	}
	$q->free();
	
}

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Mapler.me &middot; Official MapleStory Social Network</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le styles -->
    <link href="//<?php echo $domain; ?>/inc/css/bootstrap.min.css" rel="stylesheet">
    <link href="//<?php echo $domain; ?>/inc/css/style.css" rel="stylesheet">
    <link href="//thebluecorsair.com/includes/font-awesome/css/font-awesome.css" rel="stylesheet">
    <style type="text/css">
      body {
        padding-top: 20px;
      }
    </style>

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

  </head>

<body>

	<div class="container">

		<div class="navbar">
			<div class="navbar-inner">
				<div class="container">

					<!-- .btn-navbar is used as the toggle for collapsed navbar content -->
					<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</a>

					<img src="//<?php echo $domain; ?>/inc/img/logo.gif" class="pull-left" style="position:relative;right:10px;top:3px;"/>
					<!-- Be sure to leave the brand out there if you want it shown -->
					<a class="brand" href="//<?php echo $domain; ?>/"><strong>Mapler</strong>.me</a>

					<!-- Everything you want hidden at 940px or less, place within here -->
					<div class="nav-collapse collapse">
					<ul class="nav">
<?php
if (isset($__url_userdata)):
?>
						<li><a href="//<?php echo $subdomain.".".$domain; ?>/"><?php echo $__url_userdata['nickname']; ?></a></li>
						<li><a href="//<?php echo $subdomain.".".$domain; ?>/my-characters">Characters</a></li>
<?php
else:
?>
						<li><a href="//<?php echo $domain; ?>/intro/">About</a></li>
<?php
endif;
?>
					</ul>
					<ul class="nav pull-right">
						<li id="fat-menu" class="dropdown">
<?php
if ($_loggedin):
?>
							<a href="#" id="drop3" role="button" class="dropdown-toggle" data-toggle="dropdown">Welcome back, <?php echo $_logindata['full_name']; ?></a>
							<ul class="dropdown-menu" role="menu" aria-labelledby="drop3">
								<li id="fat-menu"><a href="//<?php echo $_logindata['username']; ?>.<?php echo $domain; ?>/">Profile</a></li>
								<li id="fat-menu"><a href="//<?php echo $_logindata['username']; ?>.<?php echo $domain; ?>/my-characters">My Characters</a></li>
								<li id="fat-menu"><a href="//<?php echo $_logindata['username']; ?>.<?php echo $domain; ?>/panel/">Settings</a></li>
<?php
if ($_logindata['account_rank'] == RANK_ADMIN):
?>
								<li class="divider"></li>
								<li id="fat-menu"><a href="//<?php echo $domain; ?>/actions/repo/">Update Website</a></li>
<?php
endif;
?>
								<li class="divider"></li>
								<li id="fat-menu"><a href="//<?php echo $domain; ?>/logoff">Log off</a></li>
							</ul>
<?php
else:
?>
							<a href="#" id="drop3" role="button" class="dropdown-toggle" data-toggle="dropdown"><img src="//<?php echo $domain; ?>/inc/img/icons/user_go.png"/><b class="caret"></b></a>
							<ul class="dropdown-menu" role="menu" aria-labelledby="drop3">
								<form class="form-horizontal login" style="margin:10px;" action="//<?php echo $domain; ?>/login/" method="post">
								<div class="control-group">
									<div class="controls">
										<input type="text" id="inputUsername" name="username" placeholder="Username" />
									</div>
								</div>
								<div class="control-group">
									<div class="controls">
										<input type="password" id="inputPassword" name="password" placeholder="Password" />
									</div>
								</div>
								<div class="control-group">
									<div class="controls">
										<button type="submit" class="btn btn-success" style="margin-right:2px;width:220px;">Sign in</button>
										<button type="button" onclick="document.location = 'http://<?php echo $domain; ?>/register/'" class="btn pull-right" style="display:none;">Register?</button>
									</div>
								</div>
								</form>
							</ul>
<?php
endif;
?>
						</li>
					</ul>
				</div>

			</div>
		</div>
	</div>
	
	<?php
if (!$_loggedin):
?>

	<p class="lead alert alert-error">&nbsp;Mapler.me is currently in private development. <b>Check back later!</b></p>
	
	<?php
endif;
?>	