<!DOCTYPE html>
<?php require_once('inc/functions.php'); ?>
<head>
<title>Mapler.me</title>
	<link rel="stylesheet" type="text/css" href="http://cdn.mapler.me/etc/fpwork/frontpage.css">
	<meta name="keywords" content="maplestory, maple, story, mmorpg, maple story, maplerme, mapler, me, Mapler Me, Mapler.me, Nexon, Nexon America,
	henesys, leafre, southperry, maplestory rankings, maplestory, realtime updates, Maplestory items, MapleStory skills, guild, alliance, GMS, KMS, EMS" />
	<meta name="description" content="Mapler.me is a MapleStory social network and service providing innovative features to enhance your gaming experience!" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
	
<style>
@media (max-width: 480px) {
	.outer {
		display:none;
	}
	.mobilepls {
		display: block;
	}
}
</style>
</head>

<?php
if ($_loggedin) {
?>
<meta http-equiv="refresh" content="0;URL='/stream/'" />
<?php
	die;
}
?>
<div class="left"></div>
<div class="right"></div>
<body class="teaser-page">
	<div class="bg"></div>
		<div class="outer">
			<div class="middle">
				<div class="inner">
					<a href="#">
						<img src="http://i.imm.io/ZaH7.png">
					</a>
						<div class="info">
							<p>Mapler.me is a MapleStory <b>social network</b> and service providing innovative features to enhance your gaming experience!</p>
							
							<p>Real-time character updates, progress timelines, and more await you! <b>Join today</b>.</p>
				
				<p><a href="/login/" class="btn btn-success btn-action btn-large">Login</a></p>
				</div>
			</div>
		</div>
	</div>
	
	<div class="mobilepls" style="display:none;">
		<img src="//<?php echo $domain ?>/inc/img/icon.png" width="200px"/>
		<p><a href="/login/" class="btn btn-success btn-action btn-large">Login</a></p>
	</div>
	
	<footer>
		<p>&copy; 2013 Mapler.me</p>
		<ul>
			<li class="hide-mobile"><a href="https://twitter.com/maplerme">@maplerme</a></li>
		</ul>
	</footer>

</body>