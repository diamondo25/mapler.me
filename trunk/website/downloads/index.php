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
			<a href="http://cdn.mapler.me/installers/setup_1.0.0.10.exe" class="btn btn-success btn-large download-button">Download the latest client installer (V 1.0.0.10)!</a>
			
			<p>Note: WinPcap is required for the client to function correctly.</p>
			<hr />
			<h1>Mapler.me Wordpress Plugin</h1>
			<p>Easily include your characters and their avatars in your posts, pages, and blog!</p>
			<a href="http://wordpress.org/plugins/maplerme/" class="btn btn-success btn-large download-button">View plugin on Wordpress.org!</a>
			<p>You may also download the plugin through your Plugin Manager by searching for "Mapler.me"!</p>
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