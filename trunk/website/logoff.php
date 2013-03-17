<?php
require_once __DIR__.'/inc/header.php'; 
unset($_SESSION['username']);
?>
	<div class="span12">
		<center>
			<!-- insert goodbye artwork here -->
			<h1>See you next time; you've been logged off.</h1>
			<p>Your characters, friends, and statuses will miss you!</p>
			<br/>
			<a href="/">Return to the homepage?</a>
		</center>
	</div>
<?php require_once __DIR__.'/inc/footer.php'; ?>