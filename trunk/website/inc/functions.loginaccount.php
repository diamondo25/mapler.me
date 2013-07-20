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


if (strpos($_SERVER['REQUEST_URI'], '/logoff') === FALSE && !isset($_SESSION['username']) && GetMaplerCookie('login_session') !== null) {
	$code = $__database->real_escape_string(GetMaplerCookie('login_session'));
	$query = $__database->query("
SELECT 
	a.username
FROM 
	account_tokens at
LEFT JOIN
	accounts a
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
		$_SESSION['username'] = $row[0];
	}
	else {
		// Session expired.
		SetMaplerCookie('login_session', '', -100);
	}
	
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
}
?>