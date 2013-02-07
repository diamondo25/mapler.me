<?php
require_once 'inc/header.php'; 

unset($_SESSION['username']);
?>
<p class="lead alert alert-success">You are now logged off!</p>
<a href="/">Return to the homepage?</a>
<?php require_once 'inc/footer.php'; ?>