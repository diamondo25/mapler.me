<?php
require_once __DIR__.'/../inc/header.php';

if (!$_loggedin):
?>

<p class="lead alert-error alert">Please login to view this page.</p>

<?php
else:
?>
	<div class="row">
		<div class="span6">
			<h1>Downloads and More</h1>
			<p>All official Mapler.me downloads including our client!</p>
			<br/>
			
			<h1>Mapler.me Client Installer</h1>
			<p>Simplistic, easy to use, and always there for you, the Mapler.me client is used to update your characters, items, and more.</p>
			<a href="//<?php echo $domain; ?>/downloads/client/setup.exe" class="btn btn-success btn-large"
			style="display: block;
width: auto;
padding: 19px 24px;
margin-bottom: 27px;
font-size: 30px;
line-height: 1;
text-align: center;
-webkit-border-radius: 6px;
-moz-border-radius: 6px;
border-radius: 6px;"
			>Download the latest client installer!</a>
			
			<p>Note: WinPcap is required for the client to function correctly.</p>
		</div>

		<div class="span4 offset2">
			<h1>Hello there,<br/> <?php echo $_loginaccount->GetFullName(); ?></h1>
			<p>Thank you for participating and helping out the site's development. Before downloading the client, make sure you've already <a href="//<?php echo $domain; ?>/settings/accounts/">added your account!</a>.</p>
			<p><img src="//<?php echo $domain; ?>/inc/img/no-character.gif" class="pull-right"/>As our client is built by a small team, we're depending on you to help us tackle those icky bugs and glitches that we may have not caught.</p>
		</div>
		
<?php
endif;
require_once __DIR__.'/../inc/footer.php';
?>