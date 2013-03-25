<?php
if (isset($__url_useraccount)) {
	$title = $__url_useraccount->GetNickname()." &middot; Mapler.me";
}
else {
	$title = "Mapler.me &middot; MapleStory Social Network";
}

if ($_loggedin) {
	$notifications = GetNotification();
	if ($notifications > 0)
		$title = '('.$notifications.') '.$title;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title><?php echo $title; ?></title> 
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
								<li><a href="//<?php echo $subdomain.".".$domain; ?>/characters">Characters</a></li>
								<li><a href="//<?php echo $subdomain.".".$domain; ?>/friends">Friends</a></li>
								
<?php
// Display normal pages if not a subdomain
else:
?>
								<li><a href="//<?php echo $domain; ?>/intro/">About</a></li>
								<?php if ($_loggedin): ?><li><a href="//<?php echo $domain; ?>/downloads/">Downloads</a></li>
								<li><a href="//<?php echo $domain; ?>/todo/">To-do*</a></li><?php endif; ?>
								<li class="divider"></li>
								<li><a href="//status.mapler.me/">Status</a></li>
<?php
endif;
?>
					 		</ul>
						</li>
					</ul>
				
					<!-- Login / Main Menu -->	
					<ul class="nav hidden-phone pull-right">
<?php
if ($_loggedin):
?>					
					<li>
						<a id="notify" href="//<?php echo $domain; ?>/settings/friends/">
							<span class="sprite notify"></span>
							<span class="notification-badge"><?php echo GetNotification(); ?></span>
						</a>
					</li>
					
					<li>
						<a href="#PostStatus" data-toggle="modal">+Post</a>
					</li>
					
					
<?php
endif;
?>

						<li class="dropdown">
<?php
if ($_loggedin):
?>
							<a data-toggle="dropdown" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="100" data-close-others="true" href="#">Me <b class="caret"></b></a>
							<ul class="dropdown-menu">
							
							<?php
							$main_char = $_loginaccount->GetMainCharacterName();
							if ($main_char == null)
								$main_char = 'inc/img/no-character.gif';
							else
								$main_char = 'avatar/'.$main_char;
							?>
								<li id="user-dropdown">
									<a href="//<?php echo $_loginaccount->GetUsername(); ?>.<?php echo $domain; ?>/">
										<img src="//mapler.me/<?php echo $main_char; ?>" width="40" height="40">
									<div class="info">
										<p style="text-transform:lowercase;"><?php echo $_loginaccount->GetUsername(); ?></p>
										<!-- function needed that displays rank as text instead off number -->
										<span class="ct-label">Member</span>
									</div>
									</a>
								</li>
								<li class="divider"></li>
								<li><a href="//<?php echo $_loginaccount->GetUsername(); ?>.<?php echo $domain; ?>/characters">Characters</a></li>
								<li><a href="//<?php echo $_loginaccount->GetUsername(); ?>.<?php echo $domain; ?>/friends">Friends</a></li>
								<li><a href="//<?php echo $domain; ?>/settings/profile/">Settings</a></li>
						
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
							<?php
							$main_char = $_loginaccount->GetMainCharacterName();
							if ($main_char == null)
								$main_char = 'inc/img/no-character.gif';
							else
								$main_char = 'avatar/'.$main_char;
							?>
								<li id="user-dropdown">
									<a href="//<?php echo $_loginaccount->GetUsername(); ?>.<?php echo $domain; ?>/">
										<img src="//mapler.me/<?php echo $main_char; ?>" width="40" height="40">
									<div class="info">
										<p style="text-transform:lowercase;"><?php echo $_loginaccount->GetUsername(); ?></p>
										<!-- function needed that displays rank as text instead off number -->
										<span class="ct-label">Member</span>
									</div>
									</a>
								</li>
								<li class="divider"></li>
								<li><a href="//<?php echo $_loginaccount->GetUsername(); ?>.<?php echo $domain; ?>/characters">Characters</a></li>
								<li><a href="//<?php echo $_loginaccount->GetUsername(); ?>.<?php echo $domain; ?>/friends">Friends</a></li>
								<li><a href="//<?php echo $domain; ?>/settings/profile/">Settings</a></li>
						
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
	
	<?php require_once __DIR__.'/../inc/social.php'; ?>
	
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