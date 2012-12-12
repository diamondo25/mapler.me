<form action="" method="post">
<input type="password" name="ihewfihewfewf" />
<input type="submit" />
</form>
<pre>
<?php
//echo shell_exec('ls -lart');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['ihewfihewfewf'] == 'HURR1312') {
	$username = "maplerme-website";
	$password = "#FMO@JF)JNRWGO$@Ngf9hwref923@R#@";

	system('svn up --non-interactive --username '.escapeshellarg($username).' --password '.escapeshellarg($password).' /var/www/maplestats_svn/ 2>&1');
}
?>
</pre>