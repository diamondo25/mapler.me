<?php 
require_once 'inc/header.php';

if (!$_loggedin):
?>

	<?php
	$q = $__database->query("
	SELECT 
		assigned_to
	FROM
		`beta_invite_keys`
	WHERE 
		invite_key = 'BETADQ3A'
	");

	$check = $q->fetch_assoc();

if (!isset($check['assigned_to'])) {
?>
<p class="lead alert alert-info">Contest: We're giving away one beta key! <a href="/contest/">Click here for more information.</a></p>
<?php
}
?>
	
      <div class="jumbotron">
      
      <div class="row">
		  <div class="span12">
            <h1>Mapler.me is a MapleStory community and service providing innovative features to enhance your gaming experience!</h1>
			<h2>Real-time avatars, character progress, and more is just a click away..</h2><br/>
			<p><a href="/signup/" class="btn btn-primary btn-action">Sign up now! (Beta Testers)</a></p> 
		  </div>
		</div>	  		  
		</div>
	
<?php
else:

	$char_config = $_loginaccount->GetConfigurationOption('character_config', array('characters' => array(), 'main_character' => null));

	$has_characters = !empty($char_config['main_character']);

?>
<p class="lead">
<?php 
	if ($has_characters):
		$main_character_name = $char_config['main_character'];
		$main_character_image = '//'.$domain.'/avatar/'.$main_character_name;

?>
	<center><img id="default_character" src="<?php echo $main_character_image; ?>" alt="<?php echo $main_character_name; ?>"/></center>
<?php
	endif;
?>
	<div class="row">
	<div class="span9">
	<h1>Welcome,<?php echo $_loginaccount->GetFullName(); ?>!</h1>
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
	<blockquote class="pull-right">P.S: Your main character will display at the top of the page when added!</blockquote>
	</div>
	
	<div class="span3">
		<h1>Known Issues:</h1>
		<ul>
		<h3>General:</h3>
		<li>There is not an option to hide individual items or inventories. This will be added soon.</li>
		<li>Hiding a character does not hide it's page (still viewable), although safe unless known. This will be fixed asap.</li>
		<li>Hiding a character does not disable it's image codes (avatar, card, and stats). This will be a toggle-able option.</li>
		
		<h3>Client:</h3>
		<li>Available is misspelled on the client.</li>
		
		<h3>Potential:</h3>
		<li>Potential on items isn't accurate.</li>
		<li>Bonus potential counts as normal potential.</li>
		</ul>
	</div>
	
	<div class="span3">
	<h1>Planned Features:</h1>
		<ul>
		<li>The ability to hide portions of a character page completely. (Equipment, Stats, or Skills).</li>
		</ul>
	</div>
	
	</div>

</p>
<?php
endif;

require_once 'inc/footer.php';

?>