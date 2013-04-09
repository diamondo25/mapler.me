<?php
require_once __DIR__.'/../inc/header.php';
if (!$_loggedin) {
?>
<meta http-equiv="refresh" content="0;URL='/'" />
<?php
}
else {

	$char_config = $_loginaccount->GetConfigurationOption('character_config', array('characters' => array(), 'main_character' => null));

	$has_characters = !empty($char_config['main_character']);
	
	$q = $__database->query("
SELECT
	social_statuses.*,
	accounts.username,
	TIMESTAMPDIFF(SECOND, timestamp, NOW()) AS `secs_since`
FROM
	social_statuses
LEFT JOIN
	accounts
	ON
		social_statuses.account_id = accounts.id
WHERE
	override = 1 OR account_id = ".$_loginaccount->GetID()." OR FriendStatus(account_id, ".$_loginaccount->GetID().") = 'FRIENDS'
ORDER BY
	secs_since ASC");

	$statusses = new Statusses();
	$statusses->FeedData($q);
	$q->free();

	require_once __DIR__.'/../inc/stream.notice.template.php';
?>

<?php
		if (!$has_characters || $_loginaccount->GetConfigurationOption('last_status_sent') == '') {
?>
<p class="lead alert alert-info">Hello, it seems you're new! Get started with Mapler.me and <a href="//<?php echo $domain; ?>/about?guide">view our guide! F2</a></p>
<?php
		}
?>

<div class="load status-loading" style="width:200px;margin:0 auto;">
	<center>
		<img src="//mapler.me/<?php echo $main_char; ?>"/><br />
		-loading-
	</center>
</div>

<div class="stream_display row">
<?php
	foreach ($statusses->data as $status) {
		$status->PrintAsHTML(' span12');
	}
?>
</div>
<?php
}

require_once __DIR__.'/../inc/footer.php';
?>