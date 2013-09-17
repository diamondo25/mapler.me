<?php
require_once __DIR__.'/../../inc/functions.ajax.php';

CheckSupportedTypes('description', 'name', 'nebuliteinfo', 'potentialinfo');

require_once __DIR__.'/../../inc/classes/database.php';
require_once __DIR__.'/../../inc/functions.php';
require_once __DIR__.'/../../inc/functions.datastorage.php';

RetrieveInputGET('id');
if (!is_numeric($P['id'])) JSONDie('Error');
$id = $P['id'];

if ($request_type == 'description') {
	$result = IGTextToWeb(GetMapleStoryString('item', $id, 'desc', CURRENT_LOCALE));
	JSONAnswer(array('result' => $result));
}
elseif ($request_type == 'name') {
	$result = IGTextToWeb(GetMapleStoryString('item', $id, 'name', CURRENT_LOCALE));
	JSONAnswer(array('result' => $result));
}
elseif ($request_type == 'nebuliteinfo') {
	$result = GetNebuliteInfo($id, CURRENT_LOCALE);
	JSONAnswer(array('result' => $result));
}
elseif ($request_type == 'potentialinfo') {
	$result = GetPotentialInfo($id, CURRENT_LOCALE);
	JSONAnswer(array('result' => $result));
}