<?php
session_start();
include('functions.php');


$__database = new ExtendedMysqli("127.0.0.1", "maplestats", "maplederp", "maplestats");
if ($__database->connect_errno != 0) {
	die("<strong>Failed to connect to the MySQL server: ".$__database->connect_error." (errno: ".$__database->connect_errno.")</strong>");
}

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
    <link href="http://stats.craftnet.nl/inc/css/bootstrap.min.css" rel="stylesheet">
    <link href="http://thebluecorsair.com/includes/font-awesome/css/font-awesome.css" rel="stylesheet">
    <style type="text/css">
      body {
        padding-top: 20px;
      }

      /* Custom container */
      .container-narrow {
        margin: 0 auto;
        max-width: 900px;
        margin-bottom:100px;
      }
      .container-narrow > hr {
        margin: 30px 0;
      }

      /* Main marketing message and sign up button */
      .jumbotron {
        margin: 60px 0;
        text-align: center;
      }
      .jumbotron h1 {
        font-size: 72px;
        line-height: 1;
      }
      .jumbotron .btn {
        font-size: 21px;
        padding: 14px 24px;
      }

      /* Supporting marketing content */
      .marketing {
        margin: 60px 0;
      }
      .marketing p + h4 {
        margin-top: 28px;
      }
      
      .snow {
	      position: fixed;
	      bottom: -1px;
	      left: -20px;
	      right: -20px;
	      width: 150%;
	      background: url('http://puu.sh/1sB0L');
	      height: 34px;
	      z-index: 9001;
	  }
	  
	  .login .controls {
		  margin-left: 0px;
	  }
	  
	  .form-horizontal .control-group {
		  margin-bottom: 10px !important;
	  }
	  
	 featurette-divider {
      margin: 80px 0; /* Space out the Bootstrap <hr> more */
    }
    .featurette {
      padding-top: 120px; /* Vertically center images part 1: add padding above and below text. */
      overflow: hidden; /* Vertically center images part 2: clear their floats. */
    }
    .featurette-image {
      margin-top: -120px; /* Vertically center images part 3: negative margin up the image the same amount of the padding to center it. */
    }

    /* Give some space on the sides of the floated elements so text doesn't run right into it. */
    .featurette-image.pull-left {
      margin-right: 40px;
    }
    .featurette-image.pull-right {
      margin-left: 40px;
    }

    /* Thin out the marketing headings */
    .featurette-heading {
      font-size: 50px;
      font-weight: 300;
      line-height: 1;
      letter-spacing: -1px;
    }
    
    footer {
    padding: 40px 0;
    text-align: center;
    margin-top: 40px;
    color: #777;
    }
    
    footer a {
	    margin: 0 1.5em;
    }

    </style>
    <link href="../assets/css/bootstrap-responsive.css" rel="stylesheet">

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

  </head>

  <body>

    <div class="container-narrow">

		<div class="navbar">
		  <div class="navbar-inner">
			<div class="container">
		 
			  <!-- .btn-navbar is used as the toggle for collapsed navbar content -->
			  <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			  </a>
		 
			  <img src="http://maplemation.com/forum/digitalvb/refineblue/statusicon/forum_new.gif" class="pull-left" style="position:relative;right:10px;top:3px;"/>
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
					<li id="fat-menu"><a href="/my-characters">My Characters</a></li>
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
