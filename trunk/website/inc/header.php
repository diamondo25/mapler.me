<?php
session_start();
include_once('functions.php');
include_once('database.php');


// Initialize Login Data
$_loggedin = false;
if (isset($_SESSION['login_data'])) {
	$_logindata = $_SESSION['login_data'];
	$_loggedin = true;
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
    <link href="/inc/css/bootstrap.min.css" rel="stylesheet">
    <link href="/inc/css/style.css" rel="stylesheet">
    <link href="http://thebluecorsair.com/includes/font-awesome/css/font-awesome.css" rel="stylesheet">
    <style type="text/css">
      body {
        padding-top: 20px;
      }
    </style>
    <link href="/inc/css/bootstrap-responsive.css" rel="stylesheet">

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
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
		 
			  <img src="/inc/img/logo.gif" class="pull-left" style="position:relative;right:10px;top:3px;"/>
			  <!-- Be sure to leave the brand out there if you want it shown -->
			  <a class="brand" href="/"><strong>Mapler</strong>.me</a>
		 
			  <!-- Everything you want hidden at 940px or less, place within here -->
			  <div class="nav-collapse collapse">
			   <ul class="nav">
			  <li><a href="/intro/">About</a></li>
			  <li><a href="/developers/">Developers</a></li>
			</ul>
			<ul class="nav pull-right">
				<li id="fat-menu" class="dropdown">
<?php
if ($_loggedin):
?>
				  <a href="#" id="drop3" role="button" class="dropdown-toggle" data-toggle="dropdown">Welcome back, <?php echo $_logindata['full_name']; ?></a>
				  <ul class="dropdown-menu" role="menu" aria-labelledby="drop3">
				    <li id="fat-menu"><a href="/me/">Profile</a></li>
					<li id="fat-menu"><a href="/me/my-characters">My Characters</a></li>
					<li id="fat-menu"><a href="/logoff">Log off</a></li>
				  </ul>
<?php
else:
?>
				  <a href="#" id="drop3" role="button" class="dropdown-toggle" data-toggle="dropdown">Login / Register<b class="caret"></b></a>
				  <ul class="dropdown-menu" role="menu" aria-labelledby="drop3">
					 <form class="form-horizontal login" style="margin:10px;" action="/login" method="post">
					 <p>Login with your <b>Mapler.me</b> account!</p>
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
						  <button type="submit" class="btn" style="margin-right:2px;">Sign in</button>
						  <button type="button" onclick="document.location = '/lost-account'" class="btn btn-mini">Forgot?</button>
						  <button type="button" onclick="document.location = '/register'" class="btn pull-right">Register?</button>
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
