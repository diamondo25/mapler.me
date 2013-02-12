<!DOCTYPE html>
<html lang="en">
<head>    
	<title>Mapler.me &middot; MapleStory Social Network</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0" />
	<meta name="keywords" content="maplestory, maple, story, mmorpg, maple story, maplerme, mapler, me, Mapler Me, Mapler.me, Nexon, Nexon America,
	henesys, leafre, southperry, maplestory rankings, maplestory" />
	<meta name="description" content="Mapler.me is a MapleStory community and service providing innovative features to enhance your gaming experience!" />
	
	<link href='http://fonts.googleapis.com/css?family=Muli:300,400,300italic,400italic' rel='stylesheet' type='text/css' />
	<link rel="stylesheet" href="//<?php echo $domain; ?>/inc/css/style.css" type="text/css" />
	
	<style>
	.mapletop {
        box-shadow: 0px 0px 10px 0px rgba(0, 0, 0, 0.7);
		background: rgba(255,255,255,0.7);
		height:100px;
	}
	</style>
</head>

<body>

	<div class="navbar navbar-fixed-top">
	<div class="mapletop">
	c65600
	</div>
		<div class="navbar-inner">
			<div class="container">
				<a class="brand" href="//<?php echo $domain; ?>" style="margin-top: 6px;opacity: 1;color: #fff3e4;text-decoration: none;text-shadow: 0 -1px 0 rgba(0,0,0,0.25);font-size:25px !important;"><img src="//<?php echo $domain; ?>/inc/img/logo.gif" style="float:left;position:relative;bottom:5px;right:5px;"/>Mapler.me</a>
				<div class="nav-collapse">
					<ul class="nav hidden-phone">
						 <li class="dropdown">
<?php
// Not a subdomain
if (!isset($__url_useraccount)):
?>
							<a data-toggle="dropdown" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="100" data-close-others="true" href="#"> Pages <b class="caret"></b></a>
                            
<?php
// Is a subdomain
else:
?>

							<a data-toggle="dropdown" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="100" data-close-others="true" href="#"> <?php echo $__url_useraccount->GetNickname(); ?>  <b class="caret"></b></a>                         
<?php
endif;
?>                      
							<ul class="dropdown-menu">
<?php
// Display subdomain pages related to the user
if (isset($__url_useraccount)):
?>
								<li><a href="//<?php echo $subdomain.".".$domain; ?>/">Profile</a></li>
								<li><a href="//<?php echo $subdomain.".".$domain; ?>/my-characters">Characters</a></li>
<?php
// Display normal pages if not a subdomain
else:
?>
								<li><a href="//<?php echo $domain; ?>/intro/">About</a></li>
								<li><a href="//<?php echo $domain; ?>/todo">To-do</a></li>
								<li class="divider"></li>
								<li><a href="//<?php echo $domain; ?>/terms/">Terms of Service</a></li>
<?php
endif;
?>
					 		</ul>
						</li>
					</ul>
				
					<!-- Login / Main Menu -->	
					<ul class="nav hidden-phone pull-right">
						<li class="dropdown">
<?php
if ($_loggedin):
?>
							<a data-toggle="dropdown" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="100" data-close-others="true" href="#"> Welcome back! <?php echo $_loginaccount->GetFullName(); ?> <b class="caret"></b></a>
							<ul class="dropdown-menu">
								<li><a href="//<?php echo $_loginaccount->GetUsername(); ?>.<?php echo $domain; ?>/">My Profile</a></li>
								<li><a href="//<?php echo $_loginaccount->GetUsername(); ?>.<?php echo $domain; ?>/my-characters">My Characters</a></li>
								<li><a href="//<?php echo $domain; ?>/panel/settings/general/">Settings</a></li>
						
<?php
if ($_loginaccount->GetAccountRank() == RANK_ADMIN):
?>
								<li class="divider"></li>
								<li id="fat-menu"><a href="//<?php echo $domain; ?>/actions/repo/">Update Website</a></li>
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
											<input type="text" id="inputUsername" name="username" placeholder="Username" style="width: 222px;"/>
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
								<li><a href="//<?php echo $_loginaccount->GetUsername(); ?>.<?php echo $domain; ?>/my-characters">My Characters</a></li>
								<li><a href="//<?php echo $domain; ?>/panel/settings/general/">Settings</a></li>
						
<?php
if ($_loginaccount->GetAccountRank() == RANK_ADMIN):
?>
								<li class="divider"></li>
								<li id="fat-menu"><a href="//<?php echo $domain; ?>/actions/repo/">Update Website</a></li>
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
											<input type="text" id="inputUsername" name="username" placeholder="Username" style="width: 222px;"/>
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

	<div class="container">