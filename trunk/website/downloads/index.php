<?php require_once '../inc/header.php'; ?>
<?php
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
			
			<h1>Mapler.me Client</h1>
			<p>Simplistic, easy to use, and always there for you, the Mapler.me client is used to update your characters, items, and more.</p>
			<a href="//<?php echo $domain; ?>/downloads/client/MaplerMe.exe" class="btn btn-success btn-large"
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
			>Download the latest client!</a>
		</div>

		<div class="span4 offset2">
			<h1>Hello there Beta Tester!</h1>
			<p>Thank you for participating and helping out the site's development. Before downloading the client, make sure you've already <a href="//<?php echo $domain; ?>/panel/settings/accounts/">added your account!</a>.</p>
			<p><img src="//<?php echo $domain; ?>/inc/img/no-character.gif" class="pull-right"/>As our client is built by a small team, we're depending on you to help us tackle those icky bugs and glitches that we may have not caught. We've set up a <a href="#">discussion group (chat)</a> for all of you to discuss the site, bugs, and more.</p>
			<br/>
			<h1>Client Updates:</h1>
			<p><?php include('info.txt'); ?></p>
		</div>
		
<?php
endif;
?>
      
<?php require_once '../inc/footer.php'; ?>