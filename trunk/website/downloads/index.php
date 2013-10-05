<?php
require_once __DIR__.'/../inc/header.php';
if (!$_loggedin):
?>
	<p class="lead alert-warn alert">You need to have an account before you can use the Mapler.me client.</p>
<?php
endif;
?>
	<div class="row">
		<div class="span12">
			<center>
				<img src="//<?php echo $domain; ?>/inc/img/logo.new.png"/>
			<h1>Client</h1>
			<p>Simplistic, easy to use, and always there for you, the Mapler.me client is used to update your characters, items, and more.</p>
			<a href="http://cdn.mapler.me/installers/setup_2.0.2.2.exe" class="btn btn-info btn-large download-button">Download latest version!</a>
			<br /><br />
				<p class="status">
				<b><i class="icon-check"></i> Requirements:</b><br />
				 .NET Framework 4.0 (Client Profile): <a href="http://www.microsoft.com/en-us/download/details.aspx?id=24872">Download</a><br />
				 WinPcap (will be installed with Mapler.me)
				</p>
			<br /><br />
			<img src="//<?php echo $domain; ?>/inc/img/logo.new.png" style="width:200px;"/>
			<img src="//<?php echo $domain; ?>/inc/img/wordpress.png" style="width:150px;"/>
			<h1>Mapler.me Wordpress Plugin</h1>
			<p>Easily include your characters and their avatars in your posts, pages, and blog!</p>
			<a href="http://wordpress.org/plugins/maplerme/" class="btn btn-success btn-large download-button">View plugin on Wordpress.org!</a>
			<br /><br />
			(You may also download the plugin through your Plugin Manager by searching for "Mapler.me"!)
			</center>
		</div>
	</div>
<?php
require_once __DIR__.'/../inc/footer.php';
?>