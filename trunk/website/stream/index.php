<?php
require_once '../inc/header.php';
if (!$_loggedin):
?>
<meta http-equiv="refresh" content="0;URL='/'" />
<?php
else:

	$char_config = $_loginaccount->GetConfigurationOption('character_config', array('characters' => array(), 'main_character' => null));

	$has_characters = !empty($char_config['main_character']);

?>

<p><b>Stream</b> | What do you want to say, <?php echo $_loginaccount->GetFullName(); ?>?</p>
<?php
include('../inc/social.php');

$q = $__database->query("
SELECT
	*,
	TIMESTAMPDIFF(SECOND, timestamp, NOW()) AS `secs_since`
FROM
	social_statuses
WHERE
	override = 1 OR account_id = '".$__database->real_escape_string($_loginaccount->GetID())."'
ORDER BY
	secs_since ASC
");


$social_cache = array();
while ($row = $q->fetch_assoc()) {
	$social_cache[] = $row;
}

$q->free();

?>
	<div class="row">
	<div class="span9">
	<?php

// printing table rows

foreach ($social_cache as $row) {
?>
		<div class="status">
			<div class="header">
					<a href="//<?php echo $domain; ?>/stream/status/<?php echo $row['id']; ?>#disqus_thread"></a>
					<img src="//<?php echo $domain; ?>/inc/img/icons/comment.png"/> – 
					<?php echo $row['nickname'];?> said:
				<span class="pull-right">
					<a href="//<?php echo $domain; ?>/stream/status/<?php echo $row['id']; ?>"><?php echo time_elapsed_string($row['secs_since']); ?> ago</a>

<?php
	if ($_loggedin) {
		if (IsOwnAccount()) {
?>
					- <a href="#" onclick="RemoveStatus(<?php echo $row['id']; ?>)">delete?</a>
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
				</span>
			</div>
			<br />
			<img src="http://mapler.me/avatar/<?php echo $row['character']; ?>" class="pull-right" />
			<?php echo bb_parse($row['content']); ?>
		</div>

<?php
}
?>
	<hr />
	<h1>Welcome, <?php echo $_loginaccount->GetFullName(); ?>!
<?php
	if ($has_characters):
		$main_character_name = $char_config['main_character'];
		$main_character_image = '//'.$domain.'/avatar/'.$main_character_name;

?>
	<center><img id="default_character" class="pull-right" src="<?php echo $main_character_image; ?>" alt="<?php echo $main_character_name; ?>"/></center>
<?php
	endif;
?></h1>
	<p>This page includes some simple steps on how to get started!</p>

	<p>Step 1) To begin using Mapler.me, first head to <button class="btn"><a href="//<?php echo $domain; ?>/panel/settings/accounts/">Settings -> Accounts</a></button> and connect your Nexon America account with Mapler.me. When logging into
	MapleStory it will check if the account is connected to any Mapler.me account. If so, it will then be able to work properly.</p>
	<p>Step 2) Download the Mapler.me client! It will first check for a secure connection between your client and Mapler.me, then appear! Click on "Launch MapleStory" to show the Game Launcher and launch the game.<br/>
	<pre>If launching from the MapleStory site, please load the client first before logging in to avoid any connection issues.</pre>
	</p>
	<p>When logging into a character, all of your character's items, skills, equipment, and more will instantly be transferred to Mapler.me. You may then view your characters by <a href="//<?php echo $_loginaccount->GetUsername(); ?>.mapler.me">viewing Your Profile.</a><br/>
	<pre>Important: If you would to hide your character, go to Settings then click on the "Characters" tab.</pre></p>
	<hr/>
	<h2>You're all set!</h2>
	<p>Features will continue to be added during the beta period and will be posted on this page! As this is testing, errors or issues are bound to spring up from the depths of El Nath. You can discuss Mapler.me, as well as report bugs or issues in our <a href="//<?php echo $domain; ?>/chat/">Beta Chat</a></p>
	<p>If you prefer email, please contact support@mapler.me with any questions.</p>
	<br/>
	<p>- Mapler.me Team</p>
	<br/>
	<blockquote class="pull-right">P.S: Your main character will display at the right of the page when added!</blockquote>
	</div>

	<?php include('../inc/stream.sidebar.template.php'); ?>

	</div>

</p>
<?php
endif;

require_once '../inc/footer.php';

?>