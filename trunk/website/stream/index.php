<?php
require_once __DIR__.'/../inc/header.php';
if (!$_loggedin) {
?>
<meta http-equiv="refresh" content="0;URL='/'" />
<?php
}
else {
	require_once __DIR__.'/../inc/templates/stream.notice.template.php';
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

<div class="stream_display row" id="statuslist"></div>
<p>
	<center><button onclick="TryRequestMore(false, false);" class="btn btn-large" type="button">Load more</button></center>
</p>
<script>
$(document).ready(function() { TryRequestMore(true, true); });
</script>
<?php
}

require_once __DIR__.'/../inc/footer.php';
?>