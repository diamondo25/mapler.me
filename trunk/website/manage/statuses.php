<?php
require_once __DIR__.'/../inc/header.php';

$statusses = new Statusses();
$statusses->Load('blog = 0');

foreach ($statusses->data as $status) {
		$status->PrintAsHTML('');
	}

require_once __DIR__.'/../inc/footer.php';
?>