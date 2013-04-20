<?php
session_start();

require_once __DIR__.'/classes/account.php';
require_once __DIR__.'/ranks.php';

function IsLoggedin() {
	return isset($_SESSION['username']);
}

function IsOwnAccount() {
	global $subdomain, $_loginaccount;
	return (IsLoggedin() && (strtolower($subdomain) == strtolower($_loginaccount->GetUsername()) || $_loginaccount->GetAccountRank() >= RANK_MODERATOR));
}


// Initialize Login Data
$_loggedin = false;
if (isset($_SESSION['username']) && strpos($_SERVER['REQUEST_URI'], '/logoff') === FALSE) {
	$username = $_SESSION['username'];
	$_loggedin = true;
	$_loginaccount = Account::Load($username);
	
	if ($_loginaccount->GetAccountRank() >= RANK_DEVELOPER) {
		error_reporting(E_ALL);
	}
}
?>