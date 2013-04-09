<?php 
require_once __DIR__.'/../inc/header.php';
$statusid = intval($_GET['id']);
?>
	<div class="row">
		<div class="span12">

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
		<center>
			<img src="http://mapler.me/inc/img/icon.png"/>
			<p>404: Status not found.</p>
		</center>
<?php
}
else {
	$status = new Status($q->fetch_assoc());
	$status->PrintAsHTML();
}
?>
		</div>
	</div>
<?php
require_once __DIR__.'/../inc/footer.php';
?>