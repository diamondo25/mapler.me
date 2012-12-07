<?php
include_once('inc/header.php'); 

unset($_SESSION['login_data']);
?>
<p class="lead alert alert-success">You are now logged off!</p>
<a href="/">Return to the homepage?</a>
<?php include_once('inc/footer.php'); ?>