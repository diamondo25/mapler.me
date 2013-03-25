<?php
require_once __DIR__.'/../inc/header.php';
if (!$_loggedin):
?>
<meta http-equiv="refresh" content="0;URL='/'" />
<?php
else:

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
	secs_since ASC
");


$social_cache = array();
while ($row = $q->fetch_assoc()) {
	$social_cache[] = $row;
}

$q->free();

?>
<div class="stream_display">
<?php

// printing table rows

foreach ($social_cache as $row) {
?>
			<div class="status <?php if ($row['override'] == 1): ?> notification<?php endif; ?>" style="width:288px; margin:10px;">
				<div class="header" style="background: url('http://mapler.me/avatar/<?php echo $row['character']; ?>') no-repeat right -30px #FFF;">
					<a href="//<?php echo $row['username'];?>.<?php echo $domain; ?>/"><?php echo $row['nickname'];?></a> said:
				</div>
				<br />
				<?php $parser->parse($row['content']); echo $parser->getAsHtml(); ?>
				<div class="status-extra">
					<?php if ($row['comments_disabled'] == '0'): ?>
					<a href="//<?php echo $domain; ?>/stream/status/<?php echo $row['id']; ?>#disqus_thread"></a>
					<img src="//<?php echo $domain; ?>/inc/img/icons/comment.png"/> – <?php endif; ?><a href="//<?php echo $domain; ?>/stream/status/<?php echo $row['id']; ?>"><?php echo time_elapsed_string($row['secs_since']); ?> ago</a>

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
endif;

require_once __DIR__.'/../inc/footer.php';
?>