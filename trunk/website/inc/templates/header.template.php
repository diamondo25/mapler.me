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

	$rank = $_loginaccount->GetAccountRank();

	$__database->query("UPDATE accounts SET last_login = NOW(), last_ip = '".$_SERVER['REMOTE_ADDR']."' WHERE id = ".$_loginaccount->GetID());
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
	<link rel="stylesheet" href="//<?php echo $domain; ?>/inc/css/font-awesome.min.css" type="text/css" />
<?php if (strpos($_SERVER['REQUEST_URI'], '/player/') !== FALSE): ?>
	<link rel="stylesheet" href="//<?php echo $domain; ?>/inc/css/style.player.css" type="text/css" />
<?php elseif (strpos($_SERVER['REQUEST_URI'], '/guild/') !== FALSE):?>
	<link rel="stylesheet" href="//<?php echo $domain; ?>/inc/css/style.player.css" type="text/css" />
<?php endif; ?>
	<link rel="shortcut icon" href="//<?php echo $domain; ?>/inc/img/favicon.ico" />
	<link rel="icon" href="//<?php echo $domain; ?>/inc/img/favicon.ico" type="image/x-icon" />

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.js" type="text/javascript"></script>
	<script src="//<?php echo $domain; ?>/inc/js/scripts.js?refresh=<?php echo time(); ?>" type="text/javascript"></script>
<?php if (strpos($_SERVER['REQUEST_URI'], '/player/') !== FALSE): ?>
	<script src="//<?php echo $domain; ?>/inc/js/script.player.js?refresh=<?php echo time(); ?>" type="text/javascript"></script>
<?php endif; ?>
	<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/modernizr/2.6.2/modernizr.min.js"></script>
	<script src="//<?php echo $domain; ?>/inc/js/jquery.isotope.min.js" type="text/javascript"></script>
	<script src="//<?php echo $domain; ?>/inc/js/maplerme.js?refresh=<?php echo time(); ?>" type="text/javascript"></script>

	<script type="text/javascript">
	$('.in').affix();
	$('.navbar').affix();
	</script>

	<style>
	.moar-inner-navbar {
		background: #3487cb !important;
		border-bottom: 1px solid #133783 !important;
		position: relative !important;
		height:40px !important;
		padding-left: 20px !important;;
		padding-right: 20px !important;
		box-shadow: none !important;
	}
	
	.moar-navbar {
		margin-bottom: 0px ;
	}
	
	.moar-navbar a{
		color: #FFF;
		margin: 5px;
	}
	</style>
</head>

<body>
	<div class="top-nav">
		<div class="navbar moar-navbar">
			<div class="navbar-inner moar-inner-navbar">
				<div class="container">
				<?php if ($_loggedin) { ?>
					<ul class="nav pull-left">
														
								<a id="notify" href="//<?php echo $domain; ?>/settings/friends/">
							<span><?php echo GetNotification(); ?> <i class="icon-bell-alt icon-white"></i> </span>
						</a>
					</ul>
				<?php } ?>
					<div class="nav-collapse">
						<ul class="nav">
							 <li>
								<form method="post" style="margin: 0px;" class="span10" action="http://<?php echo $domain; ?>/search/">
									<input type="text" name="search" class="search-query searchbar" placeholder="Search?" />
									<input type="hidden" name="type" value="player" />
								</form>
							 </li>
						</ul>
					</div>

					<ul class="nav pull-right">
						<a href="http://twitter.com/maplerme"><i class="icon-twitter"></i></a>
						<a href="http://facebook.com/maplerme"><i class="icon-facebook"></i></a>
					</ul>
				</div>
			</div>
		</div>


		<div class="navbar main-navbar">
			<div class="navbar-inner">
				<div class="container">
					<div class="nav-collapse">
						<ul class="nav">
							 <li class="dropdown">
								<a class="brand" data-toggle="dropdown" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="100" data-close-others="true" href="#" style="border: none;"><img src="//<?php echo $domain; ?>/inc/img/shadowlogo.png" style="float:left;width:70px;"/></a>

								<ul class="dropdown-menu" style="margin-left:5px;">
<?php
// Display subdomain pages related to the user
if (isset($__url_useraccount)):
?>
									<li><a href="//<?php echo $subdomain.".".$domain; ?>/"><?php echo $__url_useraccount->GetNickName(); ?></a></li>
									<li><a href="//<?php echo $subdomain.".".$domain; ?>/characters">Characters</a></li>
									<li><a href="//<?php echo $subdomain.".".$domain; ?>/friends">Friends</a></li>
									<li class="divider"></li>
									<li style="font-weight:500;"><a href="<?php if ($_loggedin): ?>//<?php echo $domain; ?>/stream/">Stream<?php else: ?>//<?php echo $domain; ?>">Home<?php endif; ?></a></li>

<?php
// Display normal pages if not a subdomain
else:
?>
									<li style="font-weight:500;"><a href="<?php if ($_loggedin): ?>//<?php echo $domain; ?>/stream/">Stream<?php else: ?>//<?php echo $domain; ?>">Home<?php endif; ?></a></li>
									<li class="divider"></li>
									<li><a href="//<?php echo $domain; ?>/blog/">Blog</a></li>
<?php if ($_loggedin): ?>
									<li><a href="//<?php echo $domain; ?>/downloads/">Downloads</a></li>
									<li><a href="//<?php echo $domain; ?>/todo/">To-do / Issues</a></li>
<?php endif; ?>
									<li class="divider"></li>
									<li><a href="//status.mapler.me/">Server Status</a></li>
<?php
endif;
?>
								</ul>
<?php if ($_loggedin && strpos($_SERVER['REQUEST_URI'], '/settings/') === FALSE && strpos($_SERVER['REQUEST_URI'], '/manage/') === FALSE): ?>
								<li><a href="//<?php echo $domain; ?>/stream/"><i class="icon-reorder"></i> Stream</a></li>
								<li><a href="//<?php echo $domain; ?>/stream/mentions/"><i class="icon-comments"></i> Mentions</a></li>
<?php endif; ?>
<?php
		require_once __DIR__.'/additional.menu.php';
?>
								</li>
							</ul>
						</div>
						<!-- Login / Main Menu -->
						<ul class="nav hidden-phone pull-right" style="height: 73px;">
<?php
	if ($_loggedin):
	$main_char = $_loginaccount->GetMainCharacterName();
	if ($main_char == null)
		$main_char = 'inc/img/no-character.gif';
	else
		$main_char = 'avatar/'.$main_char;
?>
							<li class="<?php if (strpos($_SERVER['REQUEST_URI'], '/settings/') !== FALSE): ?>hide-settings<?php endif; ?>">
								<a href="#" data-toggle="collapse" data-target="#post" style="z-index: 999;" id="post-toggle-button"><i class="icon-plus"></i>Post</a>
							</li>

<?php
endif;
?>				
							<li class="dropdown">
<?php
if ($_loggedin):
?>
								<a data-toggle="dropdown" class="dropdown-toggle" style="z-index:1;" data-toggle="dropdown" data-hover="dropdown" data-delay="100" data-close-others="true" href="#">
									<div id="user-dropdown" class="info" style="width:200px;height:80px;">
										<img src="//mapler.me/<?php echo $main_char; ?>" style="position:relative;bottom:9px;right:10px;">
										<div style="position:relative;right:50px;top:15px;">
											<p style="text-transform:lowercase;margin-bottom:-25px !important;overflow:hidden;text-overflow:ellipsis;"><?php echo $_loginaccount->GetUsername(); ?></p>
											<!-- function needed that displays rank as text instead off number -->
											<span class="ct-label"><?php echo GetRankTitle($rank); ?></span>
										</div>
									</div>
									<i class="icon-chevron-down" style="position:relative;left:-2px;bottom:75px;"></i>		 

								</a>
								<ul class="dropdown-menu" style="margin-right: 9px;">
									<li><a href="//<?php echo $_loginaccount->GetUsername(); ?>.<?php echo $domain; ?>/">Profile</a></li>
									<li class="divider"></li>
									<li><a href="//<?php echo $_loginaccount->GetUsername(); ?>.<?php echo $domain; ?>/characters">Characters</a></li>
									<li><a href="//<?php echo $_loginaccount->GetUsername(); ?>.<?php echo $domain; ?>/friends">Friends</a></li>
									<li><a href="//<?php echo $domain; ?>/settings/profile/">Settings</a></li>

<?php
if ($_loginaccount->GetAccountRank() >= RANK_ADMIN):
?>
									<li class="divider"></li>
									<li id="fat-menu"><a href="//<?php echo $domain; ?>/manage/general/">Manage</a></li>
<?php
endif;
?>
									<li class="divider"></li>
									<li><a href="//<?php echo $domain; ?>/logoff">Log off</a></li>
								</ul>
<?php
else:
?>
								<a data-toggle="dropdown" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="100" data-close-others="true" href="#">Login <i class="icon-chevron-down"></i></a>
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

							<li>
								<a href="#PostStatus" data-toggle="collapse" data-target="#post"><i class="icon-comment"></i></a>
							</li>

							<li class="menu dropdown">
								<a data-toggle="dropdown" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="100" data-close-others="true" href="#"><i class="icon-chevron-down"></i></a>

								<ul class="dropdown-menu">
<?php
if ($_loggedin):
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
											<span class="ct-label"><?php echo GetRankTitle($rank); ?></span>
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
									<li id="fat-menu"><a href="//<?php echo $domain; ?>/manage/general/">Manage</a></li>
<?php
endif;
?>
									<li class="divider"></li>
									<li><a href="//<?php echo $domain; ?>/logoff">Log off</a></li>
								</ul>
<?php
else:
?>
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
	</div>
	
	<div class="left"></div>
	<div class="right"></div>

	<div class="container" style="background: rgba(255,255,255,0.7); padding: 20px; border-radius: 5px; margin-top: 160px">

<?php
if ($_loggedin && $_loginaccount->GetAccountRank() <= RANK_AWAITING_ACTIVATION) {
?>
		<p class="lead alert alert-danger">You are currently restricted from using Mapler.me. <a href="//<?php echo $domain; ?>/support/">Request support?</a></p>
<?php
	require_once __DIR__.'/../../inc/footer.php';
	die;
}
require_once 'social.php';

$ip = "mc.craftnet.nl";
$port = 23711;
$onlinetext = "Mapler.me's servers are currently online!";
$offlinetext = "Mapler.me's servers are currently offline or undergoing a maintenance! Clients are disabled.";

if(!@fsockopen($ip, $port, $errno, $errstr, 5)) {
?>
	<p class="lead alert alert-danger"><?php echo $offlinetext; ?></p>
<?php
}
?>