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
		
			<div class="row" style="display:none;">
			<div class="span4"><h1>Are you ready to become a better mapler?</h1>
				<p class="lead"></p>
				</div>
			<div class="span4"><img src="https://dl.dropbox.com/u/22875564/mapler.me.resources/front3.gif" class="pull-right"/><h1>Keep track of your own Maple 'Story'!</h1>
				<p class="lead">Mapler.me records everything from your experience, stats, and even past looks!<br/>Never forget a past look again!</p>
			 </div>
			<div class="span4"><img src="https://dl.dropbox.com/u/22875564/mapler.me.resources/front2.gif" class="pull-right"/><h1>Safe, fast, and secure.<br/>It just <a href="//<?php echo $domain; ?>/intro/">works.</a></h1>
				<p class="lead">Mapler.me keeps all player information confidential and secure!<br/>
				</p>
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
	<img id="default_character" src="<?php echo $main_character_image; ?>" alt="<?php echo $main_character_name; ?>"/>
<?php
	endif;
?>
	<?php echo $_loginaccount->GetUsername(); ?>'s Stream

</p>
<?php
endif;

require_once 'inc/footer.php';

?>