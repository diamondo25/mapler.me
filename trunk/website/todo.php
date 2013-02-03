<?php require_once 'inc/header.php'; ?>

<?php
if (!$_loggedin):
?>
     
     <p class="alert alert-error">This ain't for your eyes!</p>
	
<?php
else:
?>
	<p class="lead">To-do List:</b>
	<ul class="span3">
	<b>General</b>
	<li>Perfect the front-page while logged out.</li>
	<li>Complete characters page.</li>
	<li>Add "progress" to character page once finished by Erwin.</li>
	<li>Panel (front-page while logged in)</li>
	<li>Create a download page for the Client / any other downloads. (Wallpapers? lol)</li>
	<li>Finish Settings page.</li>
	<li>Add email notifications (can be set on and off in settings)</li>
	<li>Contact site/community developers to use our service in place of Nexon's rankings. Form parnerships.</li>
	</ul>
	
	<ul class="span3">
	<b>Client</b>
	<li>Allow in-game logging into a Mapler.me account OR assign a Nexon account on the site/client.</li>
	<li>Be able to start MapleStory from the Mapler.me client.</li>
	<li>Finalize the client for Beta members. Remove local logging, have any exceptions / issue create a remote log file.</li>
	</ul>
	
	<ul class="span3">
	<b>Features</b>
	<li>Displaying all items on the site.</li>
	<li>Display equipment worn + stats.</li>
	<li>Add friends on Mapler.me.</li>
	<li>Be able to post text / screenshots while in-game that is shown on Mapler.me (would send the screenshot + text as an API request, then the image would be saved on our servers or another CDN).</li>
	<li>Simple IM (instant messaging) system for Mapler.me. This could later be added to any furture apps / Web App</li>
	</ul>
	
	<ul class="span2">
	<b>Still needing to be stored.</b>
	<li>Medals</li>
	</ul>

<?php
endif;
?>
      </div>

<?php require_once 'inc/footer.php'; ?>
