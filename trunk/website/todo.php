<?php require_once 'inc/header.php'; ?>

<?php
if (!$_loggedin):
?>
     
     <p class="alert alert-error">This ain't for your eyes!</p>
	
<?php
else:
?>
<style type="text/css">
.done {
	text-decoration: line-through;
}
</style>

	<p class="lead">To-do List:</b>
	<ul class="span3">
	<b>General</b>
	<li>Add "progress" to character page once finished by Erwin.</li>
	<li>Panel (front-page while logged in)</li>
	<li>Create a download page for the Client / any other downloads. (Wallpapers? lol)</li>
	<li>Finish Settings page.</li>
	<li>Add email notifications (can be set on and off in settings)</li>
	<li>Contact site/community developers to use our service in place of Nexon's rankings. Form parnerships.</li>
	</ul>
	
	<ul class="span3">
	<b>Client</b>
	<li>Finalize the client for Beta members. Remove local logging, have any exceptions / issue create a remote log file.</li>
	</ul>
	
	<ul class="span3">
	<b>Features</b>
	<li>Display equipment worn</li>
	<li>Add friends on Mapler.me.</li>
	<li>Be able to post text / screenshots while in-game that is shown on Mapler.me (would send the screenshot + text as an API request, then the image would be saved on our servers or another CDN).</li>
	<li>Simple IM (instant messaging) system for Mapler.me. This could later be added to any furture apps / Web App</li>
	</ul>
	
	<ul class="span2">
	<b>Still needing to be stored. (OR SHOWN)</b>
	<li>Medals</li>
	<li>Traits</li>
	<li class="done">Enhancements on Items</li>
	<li>Married?</li>
	</ul>

<?php
endif;
?>
      </div>

<?php require_once 'inc/footer.php'; ?>
