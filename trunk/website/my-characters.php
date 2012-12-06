<?php
include('inc/header.php');
if (!$_loggedin) {
?>
<p class="lead alert-error alert">Oops! Seems you're not logged in!</p>
<?php
}
else {
	include("actions/get_characters.php");
}

include('inc/footer.php');
?>