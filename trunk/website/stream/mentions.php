<?php
require_once __DIR__.'/../inc/header.php';
if (!$_loggedin) {
?>
<meta http-equiv="refresh" content="0;URL='/'" />
<?php
}
else {
	$mentioned = '@'.$_loginaccount->GetUsername();

	$statuses = new Statuses();
	$statuses->Load("s.content LIKE '%".$__database->real_escape_string($mentioned)."%'","35");

	require_once __DIR__.'/../inc/templates/stream.sidebar.template.php';
?>

<?php
	if ($_loginaccount->GetConfigurationOption('last_status_sent') == '') {
?>
<p class="lead alert alert-info">Hello, it seems you're new! Get started with Mapler.me and <a href="//<?php echo $domain; ?>/about?guide">view our guide! F2</a></p>
<p>This will disappear once you've successfully sent a status!</p>
<?php
	}
?>

<div class="stream_display row">
<div class="span8">
<h1>Mentions</h1>
<?php
	foreach ($statuses->data as $status) {
		$status->PrintAsHTML();
	}
?>
</div>
</div>
<?php
}

require_once __DIR__.'/../inc/footer.php';
?>