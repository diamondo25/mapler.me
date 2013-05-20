<?php
if (!$_loggedin) return;
?>

<script type="text/javascript">
function RemoveFriend(id) {
	if (confirm("Are you sure you want to unfriend this person?")) {
		document.location.href = '?remove=' + id;
	}
}
function DenyFriend(id) {
	if (confirm("Are you sure you want to deny this friend request?")) {
		document.location.href = '?deny=' + id;
	}
}
function AcceptFriend(id) {
	document.location.href = '?accept=' + id;
}
function InviteFriend(id) {
	document.location.href = '?invite=' + id;
}
</script>
<?php

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
	if (isset($_GET['accept'])) {
		$name = $_GET['accept'];
		$id = GetAccountID($name);

		if ($id != NULL) {
			$__database->query("
			UPDATE
				friend_list
			SET
				accepted_on = NOW()
			WHERE
				accepted_on IS NULL
				AND
				account_id = ".$id."
				AND
				friend_id = ".$_loginaccount->GetId());
			if ($__database->affected_rows == 1) {
				// Send mail?
?>
<p class="alert-info alert fademeout">Successfully accepted <?php echo $name; ?>'s request!<p>
<?php
			}
		}
	}
	elseif (isset($_GET['remove'])) {
		$name = $_GET['remove'];
		$id = GetAccountID($name);
		if ($id != NULL) {
			$__database->query("
			DELETE FROM
				friend_list
			WHERE
				(
					account_id = ".$id."
					AND
					friend_id = ".$_loginaccount->GetId()."
				)
				OR
				(
					friend_id = ".$id."
					AND
					account_id = ".$_loginaccount->GetId()."
				)");
			if ($__database->affected_rows == 1) {
			// Send mail?
?>
<p class="alert-info alert fademeout">Successfully unfriended <?php echo $name; ?><p>
<?php
			}
		}
	}
	elseif (isset($_GET['deny'])) {
		$name = $_GET['deny'];
		$id = GetAccountID($name);
		if ($id != NULL) {
			$__database->query("
			DELETE FROM
				friend_list
			WHERE
				(
					account_id = ".$id."
					AND
					friend_id = ".$_loginaccount->GetId()."
				)
				OR
				(
					friend_id = ".$id."
					AND
					account_id = ".$_loginaccount->GetId()."
				)");
			if ($__database->affected_rows == 1) {
			// Send mail?
?>
<p class="alert-info alert fademeout">Successfully denied <?php echo $name; ?>'s friend request!<p>
<?php
			}
		}
	}
	elseif (isset($_GET['invite'])) {
		$name = $_GET['invite'];
		$id = GetAccountID($name);

		if ($id != NULL && GetFriendStatus($_loginaccount->GetID(), $id) == 'NO_FRIENDS') {
			$__database->query("
			INSERT INTO
				friend_list
			VALUES
			(
				".$_loginaccount->GetID().",
				".$id.",
				NOW(),
				NULL
			)");
			if ($__database->affected_rows == 1) {
				// Send mail?
?>
<p class="alert-info alert fademeout">Successfully sent <?php echo $name; ?> a friend request!<p>
<?php
			}
		}
	}
}
?>

<div id="post" class="collapse poster" data-spy="affix" data-offset-top="10">
	<form id="statusposter" method="post" style="padding-bottom:10px;border-bottom:1px solid rgba(0,0,0,0.2);">
		<textarea name="content" class="post-resize" id="post-status" placeholder="Type your status here!"></textarea>
		<input type="hidden" name="reply-to" value="-1" />
		<button type="submit" class="btn">Post!</button>
		<button type="button" class="btn" onclick="$('#post-toggle-button').click();">Close</button>
<?php if ($_loginaccount->GetAccountRank() >= RANK_MODERATOR):?>
		Blog post? <input type="checkbox" name="blog" value="Yes" />
<?php endif; ?>
	</form>
</div>