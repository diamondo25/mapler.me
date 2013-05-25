<?php require_once __DIR__.'/../../inc/header.php'; ?>

<?php $statusid = $_GET['id']; 
	
$q = $__database->query("
SELECT
	*
FROM
	social_statuses
WHERE
	id = '$statusid'	
");

$reportedstatus = $q->fetch_assoc();
$account = Account::Load($reportedstatus['account_id']);
$q->free();
?>

<?php
if (!$_loggedin):
?>
<center>
	<p class="lead">Sorry! You must be logged in to report this!</p>
</center>
<?php
else:

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['status_report'])) {
	$statusid = $_GET['id'];
	$reporterusername = $_loginaccount->GetUsername();
	$reportedusername = $account->GetUsername();
	
	$to = 'support@mapler.me';
	
	// subject
	$subject = '[Report] Status (#'.$statusid.') by @'.$reportedusername.'';

	// message
	
	$message = 'Hello Mapler.me Staff, the following status has been reported by the mapler @'.$reporterusername.'<br /><br />
	'.$reportedstatus['content'].'<br /><br/>Manage this report by <a href="http://mapler.me/report/status/'.$statusid.'">clicking here</a>.';

	// To send HTML mail, the Content-type header must be set
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$headers .= 'From: '.$reporterusername.' <report@mapler.me>' . "\r\n";

	// Mail it
	mail($to, $subject, $message, $headers);
?>
<p class="status">The status was successfully reported!</p>
<?php
}
?>
<style>
.lead {
	font-size:30px;
}
</style>

<script type="text/javascript">
$.get('http://mapler.me/api/status/3333/',function (msg){
    $('#status_contents').html(msg);
});
</script>

<div class="row">
	<div class="span12">
		<center>
			<p class="lead"><img src="//<?php echo $domain; ?>/inc/img/icon.png" width="40px"/> You are reporting this status by @<?php echo $account->GetUsername(); ?>:</p><br />
		</center>
			<pre><?php echo $reportedstatus['content']; ?></pre>
		<hr />
		You may view the original status by <a href="//<?php echo $domain; ?>/stream/status/<?php echo $statusid; ?>">clicking here.</a>
		<hr />
		<p>Please review our <a href="http://<?php echo $domain; ?>/terms" target="_blank">Code of Conduct</a> to confirm this status is against Mapler.me's rules before reporting it. False-reporting or spamming reports can lead to account restrictions!</p>
	</div>
</div>
	<form method="POST">
		<input type="hidden" name="status_report"/>
		<input type="submit" class="span12 btn btn-large" value="Report this status!">
	</form>

<?php
endif;
?>
      
<?php require_once __DIR__.'/../../inc/footer.php'; ?>