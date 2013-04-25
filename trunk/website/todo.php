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
	<li>- The login button on mobile devices is hard to click for front page (splash page).</li>
	</ul>
	<ul class="status">
	<h2>Statuses:</h2>
	<li>- When replying to another mapler, the status is shown on all of your friend's streams (lack of privacy and spammy).</li>
	<li>- You are unable to include any hearts (<3) in statuses. Doing so will remove all content after it.</li>
	</ul>
	<ul class="status">
	<h2>Equipment:</h2>
	<li>- Items that are anvil'd display their original item look.</li>
	</ul>
	</div>
	<br/>
	<div class="row">
		<div class="span12">
			<p class="lead">Completion List:</b><br/>
			</p>
		</div>
	</div>
	<div class="row">
	<ul class="status">
	<b>General</b><br/><br/>
	<li>Add "progress" to character page once finished..</li>
	<li>Complete item/mob/npc database? (updated in real-time)</li>
	</ul>
	</div>
	<div class="row">
	<ul class="status">
	<b>Features</b><br/><br/>
	<li>Email notifications for new additions, content, mentions, etc.</li>
	<li>Notifications for replies / mentions. (Stream)</li>
	<li>Ability to search through your entire inventory(s) for items.</li>
	<li>Ability to hide items, equipment, or inventories.</li>
	</ul>
	
	<ul class="status">
	<b>Information still needed to be displayed:</b><br/><br/>
	<li>Traits</li>
	<li>Married?</li>
	</ul>
	</div>

<?php
endif;
?>

<?php require_once __DIR__.'/inc/footer.php'; ?>
