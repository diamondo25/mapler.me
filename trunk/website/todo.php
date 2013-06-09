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
			<p class="lead">Known Issues:</p>
		</div>
	</div>
	<div class="row">
	<ul class="status">
	<h2>General:</h2>
	<li>- When your first character is added, it is not automatically considered a 'main character' requiring manual selection.</li>
	<li>- There is not an option to request a password reset without being logged in.</li>
	</ul>
	<ul class="status">
	<h2>Statuses:</h2>
	<li>- When replying to another mapler, the status is shown on all of your friend's streams (lack of privacy and spammy).</li>
	<li>- The option to block users so they don't appear in your stream / mentions as well as view your characters.</li>
	<li>- Clicking on a status exposes all replies, although you can't click on those replies to view more statuses.</li>
	</ul>
	<ul class="status">
	<h2>Privacy:</h2>
	<li>- An account can't be set as private (have to be friends to view anything).</li>
	<li>- The option to block users so they don't appear in your stream / mentions as well as view your characters.</li>
	<li>- There is no "View as Public" option on character and user pages.</li>
	</ul>
	</div>
<?php
endif;
?>

<?php require_once __DIR__.'/inc/footer.php'; ?>
