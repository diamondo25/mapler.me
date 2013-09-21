<?php 
require_once __DIR__.'/../inc/header.php';
$statusid = intval($_GET['id']);
?>
	<div class="row">

<?php
$statuses = new Statuses();
$statuses->Load("s.id = ".$statusid);

if ($statuses->Count() == 0) {
?>
	<div class="span12">
		<center>
			<p class="lead status">404: Status not found.</p>
		</center>
	</div>
<?php
}
else {
	$status = $statuses->data[0]; // Get first
	
	$prestatuses = array();
	$curid = $status->reply_to;
	while (true) {
		// Oh god.

		$sss = new Statuses();
		$sss->Load("s.id = ".$curid);
		
		if ($sss->Count() == 0)
			break;
		$status_tmp = $sss->data[0];
		array_unshift($prestatuses, $status_tmp);
		$curid = $status_tmp->reply_to;
	}
	foreach ($prestatuses as $tmp)
		$tmp->PrintAsHTML(' span8');
	$status->PrintAsHTML(' span12 selected-status');

	
	// Get replies
	$statuses = new Statuses();
	$statuses->Load('s.reply_to = '.$statusid);

	if ($statuses->Count() !== 0) {
		foreach ($statuses->data as $tmp) {
			$tmp->PrintAsHTML(' span8');
		}
	}
}

?>
	</div>
<script>
$('body').ready(function() {
	$('html, body').animate({
		scrollTop: $('div[status-id="<?php echo $status->id; ?>"]').offset().top - $('.sticky-nav').height()
	}, 2000);
});
</script>
<?php
require_once __DIR__.'/../inc/footer.php';
?>