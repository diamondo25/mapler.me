<?php
require_once 'inc/header.php';
if (!$_loggedin) {
?>
<p class="lead alert-error alert">Oops! Seems you're not logged in! Please login to load your characters.</p>
<?php
}
else {
	require_once "actions/load_characters.php";
}

require_once 'inc/footer.php';
?>