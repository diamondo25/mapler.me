<?php 
require_once __DIR__.'/../inc/header.php';
$statusid = intval($_GET['id']);
?>
	<div class="row">

<?php
$statusses = new Statusses();
$statusses->Load("s.id = ".$statusid);

if ($statusses->Count() == 0) {
?>
	<div class="span12">
		<center>
			<p class="lead status">404: Status not found.</p>
		</center>
	</div>
<?php
}
else {
	$status = $statusses->data[0]; // Get first
	
	$prestatuses = array();
	$curid = $status->reply_to;
	while (true) {
		// Oh god.

		$sss = new Statusses();
		$sss->Load("s.id = ".$curid);
		
		if ($sss->Count() == 0)
			break;
		$status_tmp = $sss->data[0];
		array_unshift($prestatuses, $status_tmp);
		$curid = $status_tmp->reply_to;
	}
	foreach ($prestatuses as $tmp)
		$tmp->PrintAsHTML(' span6');
	$status->PrintAsHTML(' span12');

	
	// Get replies
	$statusses = new Statusses();
	$statusses->Load('s.reply_to = '.$statusid);

	if ($statusses->Count() !== 0) {
		foreach ($statusses->data as $status) {
			$status->PrintAsHTML(' span6');
		}
	}
}

?>
	</div>
<?php
require_once __DIR__.'/../inc/footer.php';
?>