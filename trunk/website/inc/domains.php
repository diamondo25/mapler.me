<?php
$subdomain = '';
$domain = '';

if (strpos($_SERVER['SERVER_NAME'], 'direct.mapler.me') !== false) {
	// SOMETHING.direct.mapler.me
	$subdomain = substr($_SERVER['SERVER_NAME'], 0, strrpos($_SERVER['SERVER_NAME'], '.direct.mapler.me'));
	$domain = 'direct.mapler.me';
}
elseif (strpos($_SERVER['SERVER_NAME'], 'mapler.me') !== false) {
	// SOMETHING.mapler.me
	$subdomain = substr($_SERVER['SERVER_NAME'], 0, strrpos($_SERVER['SERVER_NAME'], '.mapler.me'));
	$domain = 'mapler.me';
}
elseif (strpos($_SERVER['SERVER_NAME'], 'mplrtest.craftnet.nl') !== false) {
	// SOMETHING.mplrtest.craftnet.nl << Test Case Server
	$subdomain = substr($_SERVER['SERVER_NAME'], 0, strrpos($_SERVER['SERVER_NAME'], '.mplrtest.craftnet.nl'));
	$domain = 'mplrtest.craftnet.nl';
}
elseif (strpos($_SERVER['SERVER_NAME'], 'mplr.e.craftnet.nl') !== false) {
	// SOMETHING.mplr.e.craftnet.nl << Test Case Erwin
	$subdomain = substr($_SERVER['SERVER_NAME'], 0, strrpos($_SERVER['SERVER_NAME'], '.mplr.e.craftnet.nl'));
	$domain = 'mplr.e.craftnet.nl';
}
elseif (strpos($_SERVER['SERVER_NAME'], 'it.craftnet.nl') !== false) {
	// SOMETHING.it.craftnet.nl << Localhost testing! points to 127.0.0.1
	$subdomain = substr($_SERVER['SERVER_NAME'], 0, strrpos($_SERVER['SERVER_NAME'], '.it.craftnet.nl'));
	$domain = 'it.craftnet.nl';
}
elseif (strpos($_SERVER['SERVER_NAME'], 'maplerme') !== false) {
	// SOMETHING.maplerme << Local Testing Tyler
	$subdomain = substr($_SERVER['SERVER_NAME'], 0, strrpos($_SERVER['SERVER_NAME'], '.maplerme'));
	$domain = 'maplerme';
}
elseif (strpos($_SERVER['SERVER_NAME'], 'mapler.us.to') !== false) {
	// SOMETHING.maplerme << Local Testing Tyler (Remote Access)
	$subdomain = substr($_SERVER['SERVER_NAME'], 0, strrpos($_SERVER['SERVER_NAME'], '.mapler.us.to'));
	$domain = 'mapler.us.to';
}

$subdomain = strtolower(trim($subdomain));
if (isset($_SERVER['HTTP_ORIGIN']) && strpos($_SERVER['HTTP_ORIGIN'], $domain) !== FALSE) {
	header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']);
}

?>