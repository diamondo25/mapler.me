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
	$status = $statusses->data[0];
	$status->PrintAsHTML(' span12');

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