<?php if ($_loggedin && $_loginaccount->IsRankOrHigher(RANK_ADMIN)): ?>
<script type="text/javascript">
function Mute(id) {
	if (confirm("Are you use you want to mute this member?")) {
		document.location.href = '?mute=' + id;
	}
}
function Ban(id) {
	if (confirm("Are you sure you want to ban this member?")) {
		document.location.href = '?ban=' + id;
	}
}
function IpBan(id) {
	if (confirm("Are you sure you want to IP ban this member?")) {
		document.location.href = '?ipban=' + id;
	}
}
</script>
<?php

require_once __DIR__.'/../ranks.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
	if (isset($_GET['mute'])) {
		$name = $_GET['mute'];
		$id = GetAccountID($name);

		if ($id != NULL) {
			$account = Account::Load($id);
			$account->SetMute(1);
			$account->Save();
?>
<p class="alert-info alert fademeout">Successfully muted @<?php echo $name; ?>.<p>
<?php
		}
	}
	elseif (isset($_GET['ban'])) {
		$name = $_GET['ban'];
		$id = GetAccountID($name);
		if ($id != NULL) {
			$account = Account::Load($id);
			$account->SetAccountRank(RANK_BANNED);
			$account->Save();
?>
<p class="alert-info alert fademeout">Successfully banned @<?php echo $name; ?>.<p>
<?php
		}
	}
	elseif (isset($_GET['ipban'])) {
		$name = $_GET['ipban'];
		$id = GetAccountID($name);
		if ($id != NULL) {
			$account = Account::Load($id);
			$__database->query("
		INSERT IGNORE INTO
			`ip_ban`
		VALUES
		(
			'".$account->GetLastIP()."'
		)");
			if ($__database->affected_rows == 1) {
			// Send mail?
?>
<p class="alert-info alert fademeout">Successfully IP banned @<?php echo $name; ?>.<p>
<?php
			}
		}
	}
}
?>
<?php endif; ?>