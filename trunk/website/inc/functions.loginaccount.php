<?php
session_start();

require_once __DIR__.'/classes/database.php';
require_once __DIR__.'/classes/account.php';
require_once __DIR__.'/functions.php';
require_once __DIR__.'/ranks.php';

function IsLoggedin() {
	return isset($_SESSION['username']);
}

function IsOwnAccount() {
	global $subdomain, $_loginaccount;
	return (IsLoggedin() && (strtolower($subdomain) == strtolower($_loginaccount->GetUsername()) || $_loginaccount->GetAccountRank() >= RANK_MODERATOR));
}


if (strpos($_SERVER['REQUEST_URI'], '/logoff') === FALSE && GetMaplerCookie('login_session') !== null) {
	$code = $__database->real_escape_string(GetMaplerCookie('login_session'));
	$query = $__database->query("
SELECT 
	a.username
FROM 
	maplestats.account_tokens at
LEFT JOIN
	maplestats.accounts a
	ON
		a.id = at.account_id
WHERE 
	at.code = '".$code."' 
	AND 
	at.type = 'login_token'
	AND 
	at.till > NOW()
");

	if ($query->num_rows > 0) {
		// Valid session
		$row = $query->fetch_row();
		if (!isset($_SESSION['username']))
			$_SESSION['username'] = $row[0];
	}
	else {
		// Session expired.
		SetMaplerCookie('login_session', '', -100);
	}
	$query->free();
}

// Initialize Login Data
$_loggedin = false;
if (strpos($_SERVER['REQUEST_URI'], '/logoff') === FALSE && isset($_SESSION['username'])) {
	$username = $_SESSION['username'];
	$_loggedin = true;
	$_loginaccount = Account::Load($username);
	
	if ($_loginaccount->GetAccountRank() >= RANK_DEVELOPER) {
		error_reporting(E_ALL);
		ini_set('display_errors', 1);
	}
	
	if (GetMaplerCookie('login_session') === null) {
		$query = $__database->query("
SELECT 
	at.`code`
FROM 
	maplestats.account_tokens at
WHERE
	at.account_id = ".$_loginaccount->GetID()."
	AND
	at.type = 'login_token'
	AND 
	at.till > NOW()
");
		$code = '';
		if ($query->num_rows == 0) {
			// Create new
			$code = md5(time().' -- -- -- -- - '.$_loginaccount->GetID().' - '.$_loginaccount->GetUsername());
			$__database->query("
INSERT INTO 
	maplestats.account_tokens 
VALUES 
	(".$_loginaccount->GetID().", 'login_token', '".$code."', DATE_ADD(NOW(), INTERVAL 10 YEAR))
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
	
		SetMaplerCookie('login_session', $code, 10 * 365);
	}
}
?>