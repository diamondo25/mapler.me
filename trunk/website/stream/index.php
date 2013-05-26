<?php
require_once __DIR__.'/../inc/header.php';
if (!$_loggedin) {
?>
<meta http-equiv="refresh" content="0;URL='/'" />
<?php
}
else {
?>

<?php
		if ($_loginaccount->GetConfigurationOption('last_status_sent') == '') {
?>
<p class="lead alert alert-info">Hello, it seems you're new! Get started with Mapler.me and <a href="//<?php echo $domain; ?>/about?guide">view our guide! F2</a></p>
<p>This will disappear once you've successfully sent a status!</p>
<?php
require_once __DIR__.'/../inc/footer.php';
die;
		}
?>

<div class="row">
	<div class="span4">
		<div class="stream-block">
		<div class="character" style="background: url('//mapler.me/<?php echo $main_char; ?>') no-repeat center -17px #FFF;"></div>
		<p style="margin:0;border-bottom:1px solid rgba(0,0,0,0.1);margin-bottom:10px;">@<?php echo $_loginaccount->GetUsername(); ?> <span class="ct-label"><?php echo GetRankTitle($rank); ?></span><br/>
		<sup><a href="//<?php echo $_loginaccount->GetUsername(); ?>.<?php echo $domain; ?>/">View my profile..</a></sup></p>
		</div>
		<?php require_once __DIR__.'/../inc/templates/stream.notice.template.php'; ?>	
	</div>
	
	<div class="stream_display span8" id="statuslist"></div>
</div>

<p>
	<center><button onclick="TryRequestMore(false, false);" class="btn btn-large" type="button">Load more statuses..</button></center>
</p>
<script>
$(document).ready(function() { TryRequestMore(true, true); });
</script>
<?php
}

require_once __DIR__.'/../inc/footer.php';
?>