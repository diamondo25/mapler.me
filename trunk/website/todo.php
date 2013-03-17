<?php require_once __DIR__.'/inc/header.php'; ?>

<?php
if (!$_loggedin):
?>
     
     <p class="alert alert-error">Please login to view this page.</p>
	
<?php
else:
?>
<style type="text/css">
.done {
	text-decoration: line-through;
}

.span10 > b, .span12 > b, .span2 > b {
	font-weight: 500;
	background:#CCC;
	padding:3px;
	border-radius: 5px;
	font-size: 20px;
}
</style>

	<div class="row">
	<p class="lead">Mapler.me Completion List</b>
	</div>
	
	<hr/>
	<div class="row">
	<ul class="span10">
	<b>General</b><br/><br/>
	<li>Add "progress" to character page once finished by Erwin.</li>
	<li class="done">Stream (front-page while logged in)</li>
	<li class="done">Create a download page for the Client / any other downloads. (Wallpapers? lol)</li>
	<li class="done">Finish Settings page.</li>
	<li class="done">Add email notifications (can be set on and off in settings)</li>
	<li>Contact site/community developers to use our service in place of Nexon's rankings. Form partnerships.</li>
	</ul>
	
	<ul class="span2">
	<b>Client</b><br/><br/>
	<li class="done">Finalize the client for Beta members. Remove local logging, have any exceptions / issue create a remote log file.</li>
	</ul>
	</div>
	
	<hr/>
	
	<div class="row">
	<ul class="span10">
	<b>Features</b><br/><br/>
	<li class="done">Display equipment worn</li>
	<li>Add friends on Mapler.me.</li>
	<li>Be able to post text / screenshots while in-game that is shown on Mapler.me (would send the screenshot + text as an API request, then the image would be saved on our servers or another CDN).</li>
	<li class="done">Simple IM (instant messaging) system for Mapler.me. This could later be added to any future apps / Web App</li>
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

<?php require_once __DIR__.'/inc/footer.php'; ?>
