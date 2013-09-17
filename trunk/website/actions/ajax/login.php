<?php
require_once __DIR__.'/../../inc/functions.ajax.php';

CheckSupportedTypes('login', 'check_code');

require_once __DIR__.'/../../inc/classes/database.php';
require_once __DIR__.'/../../inc/functions.php';
require_once __DIR__.'/../../inc/functions.datastorage.php';


if ($request_type == 'login') {
	RetrieveInputPOST('email', 'password');
	$addr = $__database->real_escape_string($_SERVER['REMOTE_ADDR']);
	
	$q = $__database->query("SELECT COUNT(*) FROM login_requests WHERE ip = '".$addr."' AND DATE_ADD(NOW(), INTERVAL 1 DAY) > NOW()");
	$row = $q->fetch_row();
	$amount = $row[0];
	$q->free();
	
	if ($amount > 10) die('ERROR:Too many failed requests. Try again in a day');
	
	$q = $__database->query("SELECT id, password, salt FROM accounts WHERE email = '".$__database->real_escape_string($P['email'])."'");
	
	if ($q->num_rows == 0){
		$__database->query("INSERT INTO login_requests VALUES (NULL, '".$addr."', NOW(), 'login')");
		die('ERROR:Invalid username or password');
	}
	
	$row = $q->fetch_row();
	
	$encrypted = GetPasswordHash($P['password'], $row[2]);
	if ($encrypted != $row[1]) {
		$__database->query("INSERT INTO login_requests VALUES (NULL, '".$addr."', NOW(), 'login')");
		die('ERROR:Invalid username or password');
	}
		
	// Success! Now, lets get the cookie
	$query = $__database->query("
SELECT 
	at.`code`
FROM 
	".DB_ACCOUNTS.".account_tokens at
WHERE
	at.account_id = ".$row[0]."
	AND
	at.type = 'client_token'
	AND 
	at.till > NOW()
");
	$code = '';
	if ($query->num_rows == 0) {
		// Create new
		$code = md5(time().' --- '.$row[0].' - '.$P['email']);
		$__database->query("
INSERT INTO 
	".DB_ACCOUNTS.".account_tokens 
VALUES 
	(".$row[0].", 'client_token', '".$code."', DATE_ADD(NOW(), INTERVAL 1 YEAR))
ON DUPLICATE KEY UPDATE
	`code` = VALUES(`code`),
	`till` = VALUES(`till`)
");
	}
	else {
		// Use old one
		$row = $query->fetch_row();
		$code = $row[0];
	}
	$query->free();
	
	die('CORRE:'.$code);
}
elseif ($request_type == 'check_code') {
	RetrieveInputPOST('code');

	$addr = $__database->real_escape_string($_SERVER['REMOTE_ADDR']);
	
	
	$q = $__database->query("SELECT COUNT(*) FROM login_requests WHERE ip = '".$addr."' AND DATE_ADD(NOW(), INTERVAL 1 DAY) > NOW()");
	$row = $q->fetch_row();
	$amount = $row[0];
	$q->free();
	
	if ($amount > 10) die('ERROR:Too many failed requests. Try again in a day');
	
	$query = $__database->query("
SELECT 
	at.`code`
FROM 
	".DB_ACCOUNTS.".account_tokens at
WHERE
	at.`code` = '".$__database->real_escape_string($P['code'])."'
	AND
	at.type = 'client_token'
	AND 
	at.till > NOW()
");
	if ($query->num_rows == 0) {
		$__database->query("INSERT INTO login_requests VALUES (NULL, '".$addr."', NOW(), 'login')");

		die('INFO :Invalid key, please login.');
	}
	die('INFO :Okay');

}