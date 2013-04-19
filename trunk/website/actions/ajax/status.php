<?php
require_once __DIR__.'/../../inc/functions.ajax.php';

CheckSupportedTypes('responses');

require_once __DIR__.'/../../inc/database.php';
require_once __DIR__.'/../../inc/classes/statusses.php';

if ($request_type == 'responses') {
	RetrieveInput('statusid');
	
	$q = $__database->query("
SELECT
	*,
	TIMESTAMPDIFF(SECOND, timestamp, NOW()) AS `secs_since`
FROM
	social_statuses
WHERE
	reply_to = ".intval($P['statusid'])."

LIMIT 10");
	
	$statuses = new Statusses();
	$statuses->FeedData($q);
	$q->free();
	
	// Buffer all results
	ob_start();
	foreach ($statuses->data as $status)
		$status->PrintAsHTML();
	
	$data = ob_get_clean();
	
	JSONAnswer(array('result' => $data));
}

?>