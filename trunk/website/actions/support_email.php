<?php require_once __DIR__.'/../inc/header.php'; ?>

<?php
if (!$_loggedin):
?>
<p class="lead alert-error alert">Opps! To send a support ticket you must be logged in!</p>
<?php
else:

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email_content'])) {
	
	$theirname = $_loginaccount->GetFullName();
	$theiremail = $_loginaccount->GetEmail();
	$theirrank = GetRankTitle($rank);
	
	$to = 'support@mapler.me';
	
	// subject
	$subject = '[Mapler.me Support] '.$theirname.'-'.$theirrank;

	// message
	
	$contentpls = $_POST['email_content'];
	
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
			<p class="lead">Hello, need support?</p>
			<p>Our team of highly trained NPCs will get you assistance.</p>
			<hr />
		</center>
		<p>Your Name: <b><?php echo $_loginaccount->GetFullName(); ?></b></p>
		<p>Your Email: <b><?php echo $_loginaccount->GetEmail(); ?></b> (you will get a response here)</p>
		<p>Mapler.me Rank: <b><?php echo GetRankTitle($rank); ?></b></p>
	</div>
</div>
	<form method="POST">
		<textarea class="span12" style="height:200px;" name="email_content" placeholder="Describe your issue. Provide any details such as error messages, screenshots, or URL(s)."></textarea>
		<input type="submit" class="span11 btn btn-large" value="Request support! (note: you can send one a day)">
	</form>

<?php
endif;
?>
      
<?php require_once __DIR__.'/../inc/footer.php'; ?>