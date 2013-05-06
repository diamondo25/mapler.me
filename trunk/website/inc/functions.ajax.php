<?php
error_reporting(E_ALL);
header('Content-type: application/json');

function JSONDie($msg, $statusCode = 200) {
	die(json_encode(array('errormsg' => $msg, 'statuscode' => $statusCode)));
}

function JSONAnswer($data, $statusCode = 200) {
	header(':', true, $statusCode);
	$data['statuscode'] = $statusCode;
	die(json_encode($data));
}

function CheckSupportedTypes($types) {
	global $request_type;
	$types = array_values(func_get_args());
	if (!in_array($request_type, $types)) JSONDie('Unknown Request');
}

function RetrieveInputGET($placeholder) {
	global $P;
	
	// Check if all get params are set
	$input_needed = array_values(func_get_args());
	$get_keys = array_keys($_GET);
	$diff = array_diff($input_needed, $get_keys);
	if (count($diff) != 0) JSONDie('Missing Argument(s): '.implode('; ', array_values($diff)));
	
	foreach ($input_needed as $name)
		$P[$name] = $_GET[$name];
}

function RetrieveInputPOST($placeholder) {
	global $P;
	
	// Check if all get params are set
	$input_needed = array_values(func_get_args());
	$get_keys = array_keys($_POST);
	$diff = array_diff($input_needed, $get_keys);
	if (count($diff) != 0) JSONDie('Missing Argument(s): '.implode('; ', array_values($diff)));
	
	foreach ($input_needed as $name)
		$P[$name] = $_POST[$name];
}


if (!isset($_GET['type'])) JSONDie('Invalid Request');
$request_type = $_GET['type'];

$P = array();
?>