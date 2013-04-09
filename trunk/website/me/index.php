<?php
require_once __DIR__.'/../inc/header.php';
require_once __DIR__.'/../inc/me.header.template.php';


$q = $__database->query("
SELECT
	*,
	TIMESTAMPDIFF(SECOND, timestamp, NOW()) AS `secs_since`
FROM
	social_statuses
WHERE
	account_id = '".$__database->real_escape_string($__url_useraccount->GetID())."'
ORDER BY
	secs_since ASC
");

$statusses = new Statusses();
$statusses->FeedData($q);
$q->free();



if ($statusses->Count() == 0) {
?>
	<center>
		<img src="//<?php echo $domain; ?>/inc/img/no-character.gif"/>
		<p><?php echo $__url_useraccount->GetNickName(); ?> hasn't posted anything yet!</p>
	</center>
<?php
}
else {
?>
	<div class="span9">
<?php
	foreach ($statusses->data as $status) {
		$status->PrintAsHTML();
	}
?>
	</div>
<?php
}
?>
</div>
<?php require_once __DIR__.'/../inc/footer.php'; ?>