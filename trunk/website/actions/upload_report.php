<?php
// Report - Upload File

if (!isset($_SERVER['X-Report-ID'], $_FILES['file']['tmp_name'])) die('Invalid Headers');
if (!is_uploaded_file($_FILES['file']['tmp_name'])) die('Shoo!');

include_once('../inc/database.php');
include_once('../inc/domains.php');

$id = intval($_SERVER['X-Report-ID']);
$screenshot_location = 'reports/';

$q = $__database->query("SELECT id FROM reports WHERE id = ".$id." AND screenshot IS NULL");

if ($q->num_rows != 0) die('Already reported');

$uploadfile = uniqid(rand(0, 9)).'.png';

if (!move_uploaded_file($_FILES['file']['tmp_name'], $screenshot_location.$uploadfile)) die('Failure in move-it move-it');

$__database->query("UPDATE reports SET screenshot = '".$__database->real_escape_string($uploadfile)."' WHERE id = ".$id);
?>