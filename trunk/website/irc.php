<?php
require_once 'inc/header.php';
?>

<iframe width="100%" height="480" scrolling="no" frameborder="0" src="http://widget.mibbit.com/?server=irc.rizon.net&channel=%23MaplerMe&autoConnect=true&delay=3&nick=<?php echo $_loginaccount->GetUsername(); ?>"></iframe>

<?php
require_once 'inc/footer.php';
?>