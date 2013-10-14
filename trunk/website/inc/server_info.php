<?php

$maplerme_servers = array();
$maplerme_servers['global'] = array('direct.mapler.me', 23711);
$maplerme_servers['europe'] = array('direct.mapler.me', 23721);
//$maplerme_servers['korea'] = array('direct.mapler.me', 23731);

if (strpos($_SERVER['DOCUMENT_ROOT'], '/var/www/maplestats_svn/') !== FALSE) {
	define('SERVER_MYSQL_ADDR', '127.0.0.1');
	define('SERVER_MYSQL_PORT', 3306);
}
else {
	if (strpos($_SERVER['DOCUMENT_ROOT'], 'Mapler.me\\trunk') !== FALSE) define('SERVER_MYSQL_ADDR', '127.0.0.1');
	else define('SERVER_MYSQL_ADDR', 'mc.craftnet.nl');
	define('SERVER_MYSQL_PORT', 3306);
}

define('DB_ACCOUNTS', 'maplestats_main');
define('DB_GMS', 'maplestats');
define('DB_EMS', 'maplestats_ems');
//define('DB_KMS', 'maplestats_kms');

$_supported_locales = array('gms', 'ems');