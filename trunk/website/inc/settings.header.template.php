<!DOCTYPE html>
<html lang="en">
<head>
	<?php
		if (!isset($__url_useraccount)):
	?>
	<title>Mapler.me &middot; MapleStory Social Network</title>
	<?php
		else:
	?>
	<title><?php echo $__url_useraccount->GetNickname(); ?> &middot; Mapler.me</title>
	<?php
		endif;
	?>  
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
	<meta name="keywords" content="maplestory, maple, story, mmorpg, maple story, maplerme, mapler, me, Mapler Me, Mapler.me, Nexon, Nexon America,
	henesys, leafre, southperry, maplestory rankings, maplestory, realtime updates, Maplestory items, MapleStory skills, guild, alliance, GMS, KMS, EMS, <?php
		if (isset($__url_useraccount)):
		echo $__url_useraccount->GetNickname().', '.$__url_useraccount->GetNickname()."'s Mapler.me";
		endif;
	?>" />
	<meta name="description" content="Mapler.me is a MapleStory social network and service providing innovative features to enhance your gaming experience!" />
	
	<link href='http://fonts.googleapis.com/css?family=Muli:300,400,300italic,400italic' rel='stylesheet' type='text/css' />
	<link rel="stylesheet" href="//<?php echo $domain; ?>/inc/css/style.css" type="text/css" />
<?php if (strpos($_SERVER['REQUEST_URI'], '/player/') !== FALSE): ?>
	<link rel="stylesheet" href="//<?php echo $domain; ?>/inc/css/style.player.css" type="text/css" />
<?php endif; ?>
	<link rel="shortcut icon" href="//<?php echo $domain; ?>/inc/img/favicon.ico" />
	<link rel="icon" href="//<?php echo $domain; ?>/inc/img/favicon.ico" type="image/x-icon" />
</head>

<body>

	<div class="navbar navbar-fixed-top">
		<div class="navbar-inner">
			<div class="container">
				<a class="brand" href="
					<?php if ($_loggedin): ?>
						//<?php echo $domain; ?>/stream/
					<?php else: ?>
						//<?php echo $domain; ?>
					<?php endif; ?>"><img src="//<?php echo $domain; ?>/inc/img/shadowlogo.png" style="float:left;position:relative;bottom:0px;right:0px;width:60px;"/></a>
				<div class="nav-collapse">
					<ul class="nav hidden-phone">
<?php require_once('panel_settings_menu.php'); ?>
					</ul>
				
					<!-- Login / Main Menu -->	
					<ul class="nav hidden-phone pull-right">
						<li class="dropdown">
<?php
if ($_loggedin):
?>
							<a data-toggle="dropdown" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="100" data-close-others="true" href="#"><?php echo $_loginaccount->GetFullName(); ?> <b class="caret"></b></a>
							<ul class="dropdown-menu">
								<li><a href="//<?php echo $_loginaccount->GetUsername(); ?>.<?php echo $domain; ?>/">My Profile</a></li>
								<li><a href="//<?php echo $_loginaccount->GetUsername(); ?>.<?php echo $domain; ?>/characters">My Characters</a></li>
								<li><a href="//<?php echo $domain; ?>/settings/general/">Settings</a></li>
						
<?php
if ($_loginaccount->GetAccountRank() >= RANK_ADMIN):
?>
								<li class="divider"></li>
								<li id="fat-menu"><a href="//<?php echo $domain; ?>/actions/website/">Manage Website</a></li>
<?php
endif;
?>
								<li class="divider"></li>
								<li><a href="//<?php echo $domain; ?>/logoff">Log off</a></li>
							</ul>
<?php
else:
?>
							<a data-toggle="dropdown" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="100" data-close-others="true" href="#">Login <b class="caret"></b></a>
							<ul class="dropdown-menu">
								<form class="form-horizontal login" style="margin:10px;" action="//<?php echo $domain; ?>/login/" method="post">
									<div class="control-group">
										<div class="controls">
											<input type="text" id="inputUsername" name="username" placeholder="Email" style="width: 222px;"/>
										</div>
									</div>
									<div class="control-group">
										<div class="controls">
											<input type="password" id="inputPassword" name="password" placeholder="Password" style="width: 222px;"/>
										</div>
									</div>
									<div class="control-group">
										<div class="controls">
											<button type="submit" class="btn btn-success" style="margin-right:2px;width:240px;">Sign in</button>
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
		
					<ul class="nav mobile pull-right">
						<li class="menu dropdown">
							<a data-toggle="dropdown" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="100" data-close-others="true" href="#"><span class="sprite more menu"></span></a>

							<ul class="dropdown-menu">
<?php
if ($_loggedin):
?>
								<li><a href="//<?php echo $_loginaccount->GetUsername(); ?>.<?php echo $domain; ?>/">My Profile</a></li>
								<li><a href="//<?php echo $_loginaccount->GetUsername(); ?>.<?php echo $domain; ?>/characters">My Characters</a></li>
						
<?php
if ($_loginaccount->GetAccountRank() == RANK_ADMIN):
?>
								<li class="divider"></li>
								<li id="fat-menu"><a href="//<?php echo $domain; ?>/actions/website/">Update Website</a></li>
<?php
endif;
?>
								<li class="divider"></li>
								<li><a href="//<?php echo $domain; ?>/logoff">Log off</a></li>
<?php
else:
?>
								<form class="form-horizontal login" style="margin:10px;" action="//<?php echo $domain; ?>/login/" method="post">
									<div class="control-group">
										<div class="controls">
											<input type="text" id="inputUsername" name="username" placeholder="Email" style="width: 222px;"/>
										</div>
									</div>
									<div class="control-group">
										<div class="controls">
											<input type="password" id="inputPassword" name="password" placeholder="Password" style="width: 222px;"/>
										</div>
									</div>
									<div class="control-group">
										<div class="controls">
											<button type="submit" class="btn btn-success" style="margin-right:2px;width:240px;">Sign in</button>
											<button type="button" onclick="document.location = 'http://<?php echo $domain; ?>/register/'" class="btn pull-right" style="display:none;">Register?</button>
										</div>
									</div>
								</form>
<?php
endif;
?>
					 			
					 		</ul>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>

	<div class="container" style="background: rgba(255,255,255,0.6);padding: 20px;border-radius: 5px;">
	
<?php
$ip = "mc.craftnet.nl";
$port = 23711;
$onlinetext = "Mapler.me's servers are currently online!";
$offlinetext = "Mapler.me's servers are currently offline or undergoing a maintenance! Clients are disabled.";

if(!@fsockopen($ip, $port, $errno, $errstr, 2)) {
?>
	<p class="lead alert alert-danger"><?php echo $offlinetext; ?></p>
<?php
}
?>