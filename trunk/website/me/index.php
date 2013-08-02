<?php
require_once __DIR__.'/../inc/header.php';

if ($_loggedin && !$__url_useraccount) {
	echo '<META HTTP-EQUIV="Refresh" Content="0; URL=http://'.$_loginaccount->GetUsername().'.'.$domain.'/">';
	die;
}

require_once __DIR__.'/../inc/templates/me.header.template.php';
?>


<?php if ($__url_useraccount->GetBio() != null): ?>
	<div class="status span9 noclear">
		<p class="lead nomargin"><i class="icon-quote-left"></i> <?php echo $__url_useraccount->GetBio(); ?> <i class="icon-quote-right"></i></p>
	</div>
	
<?php
endif;
?>

<?php
 if ($__url_useraccount->GetConfigurationOption('twitch_username') !== 0) {
$clientId = $__url_useraccount->GetConfigurationOption('twitch_api_code');             // Register your application and get a client ID at http://www.twitch.tv/settings?section=applications
$twitchusername = $__url_useraccount->GetConfigurationOption('twitch_username');
$json_array = json_decode(file_get_contents('https://api.twitch.tv/kraken/streams/'.$twitchusername.'?client_id='.$clientId), true);
 
if ($json_array['stream'] != NULL) {
    $channelTitle = $json_array['stream']['channel']['display_name'];
    $streamTitle = $json_array['stream']['channel']['status'];
    $currentGame = $json_array['stream']['channel']['game'];
	
	if ($currentGame = "MapleStory") {
	?>
		<div class="span9">
		<object type="application/x-shockwave-flash" height="378" width="620" id="live_embed_player_flash" data="http://www.twitch.tv/widgets/live_embed_player.swf?channel=<?php echo $twitchusername ?>" bgcolor="#000000">
			<param name="allowFullScreen" value="true" />
			<param name="allowScriptAccess" value="always" />
			<param name="allowNetworking" value="all" />
			<param name="movie" value="http://www.twitch.tv/widgets/live_embed_player.swf" />
			<param name="flashvars" value="hostname=www.twitch.tv&channel=<?php echo $twitchusername ?>&auto_play=true&start_volume=25" />
		</object>
		</div>
	<?php
	}
	
	elseif ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['debugtwitch'])) {
	?>
		<div class="span9">
		<object type="application/x-shockwave-flash" height="378" width="620" id="live_embed_player_flash" data="http://www.twitch.tv/widgets/live_embed_player.swf?channel=<?php echo $twitchusername ?>" bgcolor="#000000">
			<param name="allowFullScreen" value="true" />
			<param name="allowScriptAccess" value="always" />
			<param name="allowNetworking" value="all" />
			<param name="movie" value="http://www.twitch.tv/widgets/live_embed_player.swf" />
			<param name="flashvars" value="hostname=www.twitch.tv&channel=<?php echo $twitchusername ?>&auto_play=true&start_volume=25" />
		</object>
		</div>
	<?php
	}
}
}
?>

	<div class="span9" id="statuslist"></div>

<p>
	<center><button onclick="syncer(true, true);" class="btn btn-large" type="button" id="syncbutton">Load more statuses..</button></center>
</p>
<script>
$(document).ready(function() { 
	$(window).scroll(function() {
		var offsetTillBottom = $(document).height() - ($(window).scrollTop() + $(window).height());
		if (offsetTillBottom <= 100) {
			syncer(true, true);
		}
	});
});
</script>
	
</div>
<?php require_once __DIR__.'/../inc/footer.php'; ?>