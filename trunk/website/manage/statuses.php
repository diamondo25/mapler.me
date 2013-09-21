<?php
require_once __DIR__.'/../inc/header.php';

$statuses = new Statuses();
$statuses->Load('blog = 0', '30');

foreach ($statuses->data as $status) {
	$status->PrintAsHTML('');
}

require_once __DIR__.'/../inc/footer.php';
?>