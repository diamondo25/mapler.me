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


	$social_cache = array();
	while ($row = $q->fetch_assoc()) {
		$social_cache[] = $row;
	}

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
		-loading-</center>
		</div>

<div class="stream_display">
<?php

	// printing table rows

	foreach ($social_cache as $row) {
		$content = $row['content'];
		//@replies
		$content1 = preg_replace('/(^|[^a-z0-9_])@([a-z0-9_]+)/i', '$1<a href="http://$2.mapler.me/">@$2</a>', $content);
		//#hashtags (no search for the moment)
		$content2 = preg_replace('/(^|[^a-z0-9_])#([a-z0-9_]+)/i', '$1<a href="#">#$2</a>', $content1);
?>

			<div class="status <?php if ($row['override'] == 1): ?> notification<?php endif; ?><?php if ($row['account_id'] == $_loginaccount->GetID()): ?> postplox<?php endif; ?>" style="width:293px; margin:10px;">
				<div class="header" style="background: url('http://mapler.me/avatar/<?php echo $row['character']; ?>') no-repeat right -30px #FFF;">
					<a href="//<?php echo $row['username'];?>.<?php echo $domain; ?>/"><?php if ($row['account_id'] == $_loginaccount->GetID()): ?>You<?php else: echo $row['nickname']; endif; ?></a> said:
				</div>
				<br />
				<?php $parser->parse($content2); echo $parser->getAsHtml(); ?>
				<div class="status-extra">
					<?php if ($row['account_id'] !== 2): ?><a href="#" class="mention-<?php echo $row['username']; ?>" onclick="Mention()" mentioned="<?php echo $row['username']; ?>"><i class="icon-share-alt"></i></a>
					
					<script type="text/javascript">
						function Mention(id) {
							$('.mention-<?php echo $row['username']; ?>').click(function() {
								var value = $(".mention").attr('mentioned');
								var input = $('#post-status');
								input.val(input.val() + '@' + value + ' ');
								return false;
								});
							}
					</script>
					<?php endif; ?>
					<a href="//<?php echo $domain; ?>/stream/status/<?php echo $row['id']; ?>"><?php echo time_elapsed_string($row['secs_since']); ?> ago</a>

<?php
		if ($_loggedin) {
			if (IsOwnAccount()) {
?>
						- <a href="#" onclick="RemoveStatus(<?php echo $row['id']; ?>);">delete?</a>
<?php
			}
			else {
				// Report button
?>
						- <a href="#"></a>
<?php
			}
		}
?>
				</div>
			</div>
			
<?php
	}
?>
</div>
<?php
}

require_once __DIR__.'/../inc/footer.php';
?>