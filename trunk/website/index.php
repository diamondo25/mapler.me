<?php 
require_once 'inc/header.php';

if (!$_loggedin):
?>
      <div class="jumbotron">
      
      <div class="row">
		  <div class="span12">
            <h1>Mapler.me is a MapleStory community and service providing innovative features to enhance your gaming experience!</h1>
			<h2>Real-time avatars, character progress, and more is just a click away..</h2><br/>
			<p><a href="#" class="btn btn-primary btn-action">Beta testing is coming soon!</a></p> 
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
	<p>Features will continue to be added during the beta period and will be posted on this page! As this is testing, errors or issues are bound to spring up from the depths of El Nath. We will be adding a private chat shortly to allow for reporting of bugs, issues, or any other concerns.</p>
	<p>For the time being, please email support@mapler.me with any questions.</p>
	<br/>
	<p>- Mapler.me Team</p>
	<br/>
	<blockquote class="pull-right">P.S: Your main character will display at the top of the page when added!</blockquote>

</p>
<?php
endif;

require_once 'inc/footer.php';

?>