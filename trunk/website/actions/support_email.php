<?php require_once __DIR__.'/../inc/header.php'; ?>

<?php
if (!$_loggedin):
?>
<center>
	<p class="lead status">Opps! To send a support ticket you must be logged in!</p>
	<p>For all legal or confidential inquiries please email support[at]mapler.me!</p>
</center>
<?php
else:

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email_content'])) {
	
	$theirname = $_loginaccount->GetFullName();
	$theiremail = $_loginaccount->GetEmail();
	$theirusername = $_loginaccount->GetUsername();
	
	$to = 'support@mapler.me';
	
	// subject
	$subject = '[Mapler.me Support] '.$theirname.' ('.$theirusername.')';

	// message
	
	$contentpls = nl2br(htmlentities(strip_tags(trim($_POST['email_content']))));
	
	$message = $contentpls;

	// To send HTML mail, the Content-type header must be set
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$headers .= 'From: '.$theirname.' <'.$theiremail.'>' . "\r\n";

	// Mail it
	mail($to, $subject, $message, $headers);
?>
<p class="status">Your support request was sent successfully!</p>
<?php
}
?>

<style>

.lead {
	font-size:30px;
}

</style>
<div class="row">
	<div class="span12">
		<center>
			<p class="lead">Hello <?php echo $_loginaccount->GetFullName(); ?>, need support? <img src="//<?php echo $domain; ?>/inc/img/icon.png" width="40px"/></p>
			<p>Our team of highly trained NPCs will get you assistance.</p>
			<hr />
		</center>
		<p>Your Name: <b><?php echo $_loginaccount->GetFullName(); ?></b></p>
		<p>Your Email: <b><?php echo $_loginaccount->GetEmail(); ?></b> (you will get a response here)</p>
		<p>Your Username: <b>@<?php echo $_loginaccount->GetUsername(); ?></b></p>
		<hr />
		<p>At Mapler.me, we are always here to assist <b>you</b>. In most cases, you'll receive assistance in <i>less than a day</i>. However, keep in mind repeated abuse of the support system or spamming tickets can result in <i>account restrictions.<i></p>
	</div>
</div>
	<form method="POST">
		<textarea class="span12" style="height:200px;" name="email_content" placeholder="Describe your issue or request. Provide any details such as error messages, screenshots, or URL(s)."></textarea>
		<input type="submit" class="span12 btn btn-large" value="Request support!">
	</form>

<?php
endif;
?>
      
<?php require_once __DIR__.'/../inc/footer.php'; ?>