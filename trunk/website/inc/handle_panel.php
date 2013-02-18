<?php
require_once 'functions.php';

if (!IsLoggedin() || !isset($_GET['page'], $_GET['type'])) {
	header('Location: http://'.$domain.'/');
}

$page = '../panel/'.($_GET['type'] == '' ? '' : stripslashes($_GET['type']).'/').stripslashes($_GET['page']).'.php';
if (!file_exists($page)) {
	header('Location: http://'.$domain.'/');
	die();
}

require_once 'header.template.php';

if ($_GET['type'] == 'settings') {
	require_once 'panel_settings_menu.php';
}

require_once $page;

require_once 'footer.php';
?>