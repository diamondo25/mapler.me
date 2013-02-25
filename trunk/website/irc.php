<?php
require_once 'inc/header.php';
if (!$_loggedin):
?>

<p class="lead alert alert-danger">Please login to view this page.</p>

<?php
else:
?>

<iframe width="100%" height="480" scrolling="no" frameborder="0" src="http://widget.mibbit.com/?server=irc.rizon.net&channel=%23MaplerMe&autoConnect=true&delay=3&nick=mplr_<?php echo $_loginaccount->GetUsername(); ?>"></iframe>

If asked for a password, use 'betatest'. This is to keep conversations confidential.

<?php
	endif;
?>
<?php
require_once 'inc/footer.php';
?>