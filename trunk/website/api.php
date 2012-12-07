<?php include_once('inc/header.php'); ?>

<?php
if (!$_loggedin):
?>
<p class="lead alert-error alert">Opps! Seems you're not logged in or a <b>developer!</b> <a href="#" class="btn pull-right">Apply?</a></p>
<p>Mapler.me offers an extensive <b>{JSON}</b> API for developers to create applications crafted by Nexon America!</p>

<?php
else:
?>
hi
<?php
endif;
?>
      
<?php include_once('inc/footer.php'); ?>