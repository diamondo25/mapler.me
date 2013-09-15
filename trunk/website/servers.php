<?php
$servers = array();
$servers[] = array(8, '8.31.9', '217.23.1.172', 23710); // GMS
$servers[] = array(9, '109.234.77', '217.23.1.172', 23720); // EMS
//$servers[] = array(2, '220.90.204', '217.23.1.172', 23730); // KMS

foreach ($servers as $info) {
	echo implode($info, '|')."\n";
}
