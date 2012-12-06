<?php
include_once('inc/header.php');
if (!$_loggedin) {
?>
<p class="lead alert-error alert">Oops! Seems you're not logged in! Please login to load your characters.</p>
<?php
}
else {
	include_once("actions/load_characters.php");
}

include_once('inc/footer.php');
?>