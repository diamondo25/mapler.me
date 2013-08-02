<?php

$maplerme_servers = array();
$maplerme_servers['global'] = array('direct.mapler.me', 23711);
//$maplerme_servers['europe'] = array('ems.s.mapler.me', 23711);

if (strpos($_SERVER['DOCUMENT_ROOT'], '/var/www/maplestats_svn/') !== FALSE) {
	define('SERVER_MYSQL_ADDR', '127.0.0.1');
	define('SERVER_MYSQL_PORT', 3306);
}
else {
	define('SERVER_MYSQL_ADDR', 'mc.craftnet.nl');
	define('SERVER_MYSQL_PORT', 3306);
}

