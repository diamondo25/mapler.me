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
			<p class="lead">Known Issues:</p><br/>
		</div>
	<ul class="status">
	<h2>General:</h2>
	<li>- When your first character is added, it is not automatically considered a 'main character' requiring manual selection.</li>
	<li>- There is not an option to request a password reset without being logged in.</li>
	<li>- There is not an FAQ. (in the works)</li>
	<li>- Profession and special skills do not display correctly (broken icon)</li>
	</ul>
	<ul class="status">
	<h2>Statuses:</h2>
	<li>- When replying to another mapler, the status is shown on all of your friend's streams (lack of privacy and spammy).</li>
	<li>- Posting statuses directly to Twitter, Facebook, Tumblr, etc. (social media)</li>
	<li>- The option to block users so they don't appear in your stream / mentions as well as view your characters.</li>
	</ul>
	<ul class="status">
	<h2>Privacy:</h2>
	<li>- Characters set to "hidden" appear at the login screen, although are still safe. (clicking one will simply show the expected character not found page).</li>
	<li>- An account can't be set as private (have to be friends to view anything).</li>
	<li>- The option to block users so they don't appear in your stream / mentions as well as view your characters.</li>
	</ul>
	</div>
	<br/>
	<div class="row">
		<div class="span12">
			<p class="lead">Completion List:</h2><br/>
			</p>
		</div>
	</div>
	<div class="row">
	<ul class="status">
	<h2>General</h2>
	<li>Character leveling progress (awaiting public)</li>
	<li>Complete item database? (awaiting public)</li>
	</ul>
	</div>
	<div class="row">
	<ul class="status">
	<h2>Features</h2>
	<li>Posting screenshots and recordings directly from MapleStory? (awaiting public)</li>
	<li>Tutorial on how to use our RESTful APIs? (awaiting public)</li>
	<li>Official panel for Nexon employees? (awaiting public)</li>
	<li>Badges or achievements.</li>
	<li>Email notifications for new additions, content, mentions, etc.</li>
	<li>Notifications for replies / mentions. (Stream)</li>
	<li>Ability to search through your entire inventory(s) for items.</li>
	<li>Ability to hide items, equipment, or inventories.</li>
	</ul>
	
	<ul class="status">
	<h2>Information still needed to be displayed:</h2>
	<li>Traits</li>
	<li>Familars</li>
	<li>Guild Information</li>
	<li>Alliance Information</li>
	</ul>
	</div>

<?php
endif;
?>

<?php require_once __DIR__.'/inc/footer.php'; ?>
