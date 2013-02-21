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

.span10 > b, .span2 > b {
	font-weight: 500;
	background:#CCC;
	padding:3px;
	border-radius: 5px;
	font-size: 20px;
}
</style>

	<div class="row">
	<p class="lead">Mapler.me Completion List</b>
	
	<ul class="span12">
	<b>Before Beta:</b><br/><br/>
	<li>Make sure email notification works when signing up.</li>
	<li>Add password change in settings.</li>
	<li>Add temporary content to front page when logged in until stream is actively being worked on.</li>
	<li>Add a private chat for beta testers to chat in as well as gathering info on bugs / glitches. Make all messages logged. (Will be removed after testing).</li>
	<li>Change usernames -> email, and profile username -> nickname.</li>
	</ul>
	
	<ul class="span10">
	<b>General</b><br/><br/>
	<li>Add "progress" to character page once finished by Erwin.</li>
	<li>Stream (front-page while logged in)</li>
	<li class="done">Create a download page for the Client / any other downloads. (Wallpapers? lol)</li>
	<li class="done">Finish Settings page.</li>
	<li>Add email notifications (can be set on and off in settings)</li>
	<li>Contact site/community developers to use our service in place of Nexon's rankings. Form parnerships.</li>
	</ul>
	
	<ul class="span2">
	<b>Client</b><br/><br/>
	<li>Finalize the client for Beta members. Remove local logging, have any exceptions / issue create a remote log file.</li>
	</ul>
	</div>
	
	<hr/>
	
	<div class="row">
	<ul class="span10">
	<b>Features</b><br/><br/>
	<li class="done">Display equipment worn</li>
	<li>Add friends on Mapler.me.</li>
	<li>Be able to post text / screenshots while in-game that is shown on Mapler.me (would send the screenshot + text as an API request, then the image would be saved on our servers or another CDN).</li>
	<li>Simple IM (instant messaging) system for Mapler.me. This could later be added to any furture apps / Web App</li>
	</ul>
	
	<ul class="span2">
	<b>Info</b><br/><br/>
	<li>Medals</li>
	<li>Traits</li>
	<li class="done">Enhancements on Items</li>
	<li>Married?</li>
	</ul>
	</div>
	
	<hr/>
	
	<div class="row">
	<ul class="span10">
	<b>Guild / Alliance System (Pages)</b><br/><br/>
	<li>Add a check for if a player is a guild master or junior.</li>
	<li>Create page for guilds.<br/>
		Guilds will be located like this: /guild/scania/MPLRME
	</li>
	<li>Create page for alliances.<br/>
		Guilds will be located like this: /alliance/scania/MPLRME</li>
	</ul>
	</div>

<?php
endif;
?>
      </div>

<?php require_once 'inc/footer.php'; ?>
