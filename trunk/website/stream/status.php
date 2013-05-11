<?php 
require_once __DIR__.'/../inc/header.php';
$statusid = intval($_GET['id']);
?>
	<div class="row">

<?php
$q = $__database->query("
SELECT
	*,
	TIMESTAMPDIFF(SECOND, timestamp, NOW()) AS `secs_since`
FROM
	social_statuses
WHERE
	id = ".$statusid);


if ($q->num_rows == 0) {
?>
	<div class="span12">
		<center>
			<p class="lead status">404: Status not found.</p>
		</center>
	</div>
<?php
}
else {
	$status = new Status($q->fetch_assoc());
	$status->PrintAsHTML(' span12');

	$statusses = new Statusses();
	$statusses->Load('reply_to = '.$statusid);

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