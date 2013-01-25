<?php
$subdomain = "";
$domain = "";
if (strpos($_SERVER['SERVER_NAME'], "direct.mapler.me") !== false) {
	// SOMETHING.direct.mapler.me
	$subdomain = substr($_SERVER['SERVER_NAME'], 0, strrpos($_SERVER['SERVER_NAME'], ".direct.mapler.me"));
	$domain = "direct.mapler.me";
}
elseif (strpos($_SERVER['SERVER_NAME'], "mapler.me") !== false) {
	// SOMETHING.mapler.me
	$subdomain = substr($_SERVER['SERVER_NAME'], 0, strrpos($_SERVER['SERVER_NAME'], ".mapler.me"));
	$domain = "mapler.me";
}
elseif (strpos($_SERVER['SERVER_NAME'], "mplrtest.craftnet.nl") !== false) {
	// SOMETHING.mplrtest.craftnet.nl << Test Case Server
	$subdomain = substr($_SERVER['SERVER_NAME'], 0, strrpos($_SERVER['SERVER_NAME'], ".mplrtest.craftnet.nl"));
	$domain = "mplrtest.craftnet.nl";
}
elseif (strpos($_SERVER['SERVER_NAME'], "mplr.e.craftnet.nl") !== false) {
	// SOMETHING.mplr.e.craftnet.nl << Test Case Erwin
	$subdomain = substr($_SERVER['SERVER_NAME'], 0, strrpos($_SERVER['SERVER_NAME'], ".mplr.e.craftnet.nl"));
	$domain = "mplr.e.craftnet.nl";
}

elseif (strpos($_SERVER['SERVER_NAME'], "maplerme") !== false) {
	// SOMETHING.maplerme << Local Testing Tyler
	$subdomain = substr($_SERVER['SERVER_NAME'], 0, strrpos($_SERVER['SERVER_NAME'], ".maplerme"));
	$domain = "maplerme";
}

$subdomain = trim($subdomain);

?>