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
</style>

	<div class="row">
		<div class="span12">
			<p class="lead">Mapler.me Completion List</b><br/>
			<small>Please note this is a very generalized todo list and many additions or information is left out.</small>
			</p>
		</div>
	</div>
	
	<hr/>
	<div class="row">
	<ul class="status">
	<b>General</b><br/><br/>
	<li>Add "progress" to character page once finished..</li>
	<li>Search for players.</li>
	</ul>
	</div>
	<div class="row">
	<ul class="status">
	<b>Features</b><br/><br/>
	<li>Be able to post text / screenshots while in-game that is shown on Mapler.me (would send the screenshot + text as an API request, then the image would be saved on our servers or another CDN).</li>
	<li>Email notifications for new additions, content, mentions, etc.</li>
	<li>Notifications for replies / mentions. (Stream)</li>
	</ul>
	
	<ul class="status">
	<b>Information still needed to be displayed:</b><br/><br/>
	<li>Medals</li>
	<li>Traits</li>
	<li>Guild / Alliance</li>
	<li>Married?</li>
	</ul>
	</div>
	
	<hr/>
	
	<div class="row">
	<ul class="status">
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
