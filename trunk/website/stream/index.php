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

<style>
@media (max-width: 480px) {
	.container {
		padding:0px !important;
	}
}
</style>

<div class="row">
	
	<div class="span4 pull-right no-mobile">
		<div class="stream-block hide">
			<a href="#" class="btn btn-large btn-inverse" style="width: 100%;
max-width: 240px;">
				Start Mapler.me
			</a>
		</div>
	
		<div class="stream-block">
			<?php MakePlayerAvatar($main_char); ?>
			<p style="margin:0;border-bottom:1px solid rgba(0,0,0,0.1);margin-bottom:10px;">@<?php echo $_loginaccount->GetUsername(); ?> <span class="ct-label"><?php echo GetRankTitle($rank); ?></span><br/>
			<sup><a href="//<?php echo $_loginaccount->GetUsername(); ?>.<?php echo $domain; ?>/">View my profile..</a></sup></p>
		</div>
		<?php require_once __DIR__.'/../inc/templates/stream.notice.template.php'; ?>
<?php

	// Check for expiring items...
	$q = $__database->query("
SELECT
	c.name,
	GROUP_CONCAT(i.itemid),
	GROUP_CONCAT(UNIX_TIMESTAMP(FROM_FILETIME(i.expires)))
FROM
	items i
LEFT JOIN
	characters c
	ON
		c.internal_id = i.character_id
WHERE
	`GetCharacterAccountID`(c.id) = ".$_loginaccount->GetID()."
	AND
	i.expires <> 150842304000000000
	AND
	TO_FILETIME(NOW()) < i.expires
	AND
	TO_FILETIME(DATE_ADD(NOW(), INTERVAL 1 WEEK)) > i.expires
GROUP BY
	c.internal_id
");
		while ($row = $q->fetch_row()) {
			$itemids = explode(',', $row[1]);
			$times = explode(',', $row[2]);
?>
		
		<div class="stream-block">
			<?php MakePlayerAvatar($row[0], array('face' => 'angry', 'styleappend' => 'float: right;')); ?>
			<strong>Expiring Items</strong><br />
<?php foreach ($itemids as $index => $itemid): ?>
			<?php echo GetMapleStoryString('item', $itemid, 'name'); ?> expires in <?php echo time_elapsed_string($times[$index] - $__server_time); ?>!<br />
<?php endforeach; ?>
		</div>
<?php
		
		
		}

?>
	</div>
	
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
}

require_once __DIR__.'/../inc/footer.php';
?>