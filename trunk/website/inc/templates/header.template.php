<?php
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
<link rel="stylesheet" href="//<?php echo $domain; ?>/inc/<?php echo $dirname; ?>/<?php echo $filename.'.'.$modificationTime.'.'.$extension; ?>" type="text/<?php echo $type; ?>" />
<?php
	}
	elseif ($what == 'js') {
?>
<script type="text/javascript" src="//<?php echo $domain; ?>/inc/<?php echo $dirname; ?>/<?php echo $filename.'.'.$modificationTime.'.'.$extension; ?>"></script>
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
_AddHeaderLink('css', 'style.min');
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
</head>

<body>

<header>
    <div class="sticky-nav stuck span12">
        <nav id="rightmenu">
        	<ul id="menu-rightnav">
        	<li class="dropdown">
        		<a id="goUp" data-toggle="dropdown" class="dropdown-toggle hidden-phone" data-toggle="dropdown" data-hover="dropdown" data-delay="100" data-close-others="true" href="#"><img src="http://mapler.me/inc/img/shadowlogo.png" style="width:35px;position:relative;top:10px;"/> <b>mapler</b>.me
        			<?php if ($_loggedin && GetNotification() != '0'): ?>
        				(<?php echo GetNotification(); ?><i class="icon-bell-alt icon-white"></i>)
        			<?php endif; ?> <i class="icon-chevron-down"></i>
        		</a>
        		
        		<a id="goUp" data-toggle="dropdown" class="dropdown-toggle showmobile" data-toggle="dropdown" data-hover="dropdown" data-delay="100" data-close-others="true" href="#"><img src="http://mapler.me/inc/img/shadowlogo.png" class="showmobile" style="width:35px;position:relative;top:10px;"/>
        		</a>
        		
								<ul class="dropdown-menu" style="">
<?php
// Display subdomain pages related to the user
if (isset($__url_useraccount)):
?>
									<?php if ($_loggedin && GetNotification() != '0'): ?>
									<li><a href="//<?php echo $domain; ?>/settings/friends/"><?php echo GetNotification(); ?> Notifications</a></li>
									<li class="divider"></li>
									<?php endif; ?>

									<li><a href="//<?php echo $subdomain.".".$domain; ?>/"><?php echo $__url_useraccount->GetNickName(); ?></a></li>
									<li><a href="//<?php echo $subdomain.".".$domain; ?>/characters">Characters</a></li>
									<li><a href="//<?php echo $subdomain.".".$domain; ?>/friends">Friends</a></li>
									<li class="divider"></li>
									<li style="font-weight:500;"><a href="<?php if ($_loggedin): ?>//<?php echo $domain; ?>/stream/">Stream<?php else: ?>//<?php echo $domain; ?>">Home<?php endif; ?></a></li>

<?php
// Display normal pages if not a subdomain
else:
?>
									<?php if ($_loggedin && GetNotification() != '0'): ?>
									<li><a href="//<?php echo $domain; ?>/settings/friends/"><?php echo GetNotification(); ?> Notifications</a></li>
									<li class="divider"></li>
									<?php endif; ?>
									        		<li> 
	        		<form method="post" action="http://<?php echo $domain; ?>/search/" style="margin:0 !important;">
		        		<input type="text" name="search" class="search-query searchbar" placeholder="Find a character?" />
		        		<input type="hidden" name="type" value="character" />
		        	</form>
        		</li>
									<li class="divider"></li>
									<li style="font-weight:500;"><a href="<?php if ($_loggedin): ?>//<?php echo $domain; ?>/stream/">Stream<?php else: ?>//<?php echo $domain; ?>">Home<?php endif; ?></a></li>
									<li class="divider"></li>
									<li><a href="//<?php echo $domain; ?>/rankings/">Rankings</a></li>
									<li><a href="//blog.mapler.me/">Blog</a></li>
<?php if ($_loggedin): ?>
									<li><a href="//<?php echo $domain; ?>/about?guide">Guide</a></li>
									<li><a href="//<?php echo $domain; ?>/downloads/">Downloads</a></li>
									<li><a href="//<?php echo $domain; ?>/cdn/">CDN</a></li>
<?php endif; ?>
									<li class="divider"></li>
									<li><a href="//<?php echo $domain; ?>/team/">Our Team</a></li>
<?php
endif;
?>
								</ul>
								
								</li>
        	</ul>
        </nav>
<?php
if ($_loggedin):
?>        
        <nav id="rightmenu">
        	<ul id="menu-rightnav">
        									<li class="dropdown">

								<a data-toggle="dropdown" class="dropdown-toggle" style="z-index:1;overflow:hidden;" data-toggle="dropdown" data-hover="dropdown" data-delay="100" data-close-others="true" href="#">
											<span>@<?php echo $_loginaccount->GetUsername(); ?></span>
											<!-- function needed that displays rank as text instead off number -->
											
									<i class="icon-chevron-down"></i>		 

								</a>
								<ul class="dropdown-menu" style="margin-right: 9px;">
									<li><a href="//<?php echo $_loginaccount->GetUsername(); ?>.<?php echo $domain; ?>/">Profile</a></li>
									<li class="divider"></li>
									<li><a href="//<?php echo $_loginaccount->GetUsername(); ?>.<?php echo $domain; ?>/characters">Characters</a></li>
									<li><a href="//<?php echo $_loginaccount->GetUsername(); ?>.<?php echo $domain; ?>/friends">Friends</a></li>
									<li class="dropdown-submenu">
										<a tabindex="-1" href="//<?php echo $domain; ?>/settings/profile/">Settings</a>
											<ul class="dropdown-menu">
												<li><a href="//<?php echo $domain; ?>/settings/general/">General</a></li>
												<li><a href="//<?php echo $domain; ?>/settings/characters/">Characters</a></li>
												<li><a href="//<?php echo $domain; ?>/settings/friends/">Friend Requests</a></li>
											</ul>
									</li>

<?php
if ($_loginaccount->GetAccountRank() >= RANK_ADMIN):
?>
									<li class="divider"></li>
									<li class="dropdown-submenu">
										<a tabindex="-1" href="//<?php echo $domain; ?>/manage/general/">Manage</a>
											<ul class="dropdown-menu">
												<li><a href="//<?php echo $domain; ?>/manage/general/">General</a></li>
												<li><a href="//<?php echo $domain; ?>/manage/statuses/">Statuses</a></li>
												<li><a href="//<?php echo $domain; ?>/manage/revisions/">Revisions</a></li>
												<li><a href="//<?php echo $domain; ?>/manage/statistics/">Statistics</a></li>
												<li><a href="//<?php echo $domain; ?>/manage/serverlog/">Log</a></li>
												<li><a href="//<?php echo $domain; ?>/manage/findstring/">Search</a></li>
											</ul>
									</li>
<?php
endif;
?>
									<li class="divider"></li>
									<li><a href="//<?php echo $domain; ?>/logoff">Sign Out</a></li>
								</ul>
							</li>
						</ul>
					</nav>
<?php
else:
?>
	<nav id="menu">
		<ul id="menu-nav">
			<li><a href="//<?php echo $domain; ?>/login/"><i class="icon-check"></i> Login</a></li>
		</ul>
	</nav>
<?php
endif;
?>
        
        <nav id="menu">
        	<ul id="menu-nav">
<?php
	if ($_loggedin) {
		// Shouldn't be here...
		$main_char = $_loginaccount->GetMainCharacterName();
		
		if (!$_loginaccount->IsMuted()):
?>
							<li>
								<a href="#post" role="button" data-toggle="modal"><i class="icon-plus"></i></a>
							</li>

<?php
		endif;
	}
?>				
            </ul>
        </nav>
        <?php
        	if ($_loggedin):
        ?>
        <nav id="menu" class="hidemobile">
        	<ul id="menu-nav">
        		<li><a href="//<?php echo $domain; ?>/stream/"><i class="icon-reorder"></i> Stream</a></li>
        		<li><a href="//<?php echo $domain; ?>/stream/mentions/"><i class="icon-comments"></i> Mentions</a></li>
			</ul>
        </nav>
        <?php
        	endif;
        ?>
        
    </div>
</header>

	<div class="container main" style="padding: 20px; border-radius: 5px; margin-top: 90px; margin-bottom:30px;">

<?php
if ($_loggedin && $_loginaccount->GetAccountRank() <= RANK_AWAITING_ACTIVATION) {
?>
		<p class="lead alert alert-danger">You are currently restricted from using Mapler.me. <a href="//<?php echo $domain; ?>/support/">Request support?</a></p>
<?php
	require_once __DIR__.'/../../inc/footer.php';
	die;
}

if ($_loggedin && !$_loginaccount->IsMuted()):
	require_once 'social.php';
endif;

if ($_loggedin && $_loginaccount->IsRankOrHigher(RANK_ADMIN)):
require_once 'banhammer.php';
endif;

if ($_loggedin && $_loginaccount->IsMuted()):
?>
	<p class="lead alert alert-danger">You are currently muted. Posting statuses and sending friend requests disabled.</p>
<?php
endif;
?>