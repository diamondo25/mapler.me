<?php
require_once __DIR__.'/../inc/header.php';
if (!$_loggedin) {
?>
<meta http-equiv="refresh" content="0;URL='/'" />
<?php
	die();
}
if ($_loginaccount->GetConfigurationOption('last_status_sent') == '') {
?>
<p class="lead alert alert-info">Hello, it seems you're new! Get started with Mapler.me and <a href="//<?php echo $domain; ?>/about?guide">view our guide! F2</a></p>
<p>This will disappear once you've successfully sent a status!</p>
<?php
	require_once __DIR__.'/../inc/footer.php';
	die;
}
?>

<style>
@media (max-width: 480px) {
	.container {
		padding:0px !important;
	}
}
</style>

<div class="row">

	<?php require_once __DIR__.'/../inc/templates/stream.sidebar.template.php'; ?>
	
	<div class="stream_display span8" id="statuslist"></div>

</div>
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
<?php

require_once __DIR__.'/../inc/footer.php';
?>