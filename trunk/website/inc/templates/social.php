<?php
if ($_loggedin) {
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
<p class="alert-info alert">Successfully accepted <?php echo $name; ?>'s request!<p>
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
<p class="alert-info alert">Successfully unfriended <?php echo $name; ?><p>
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
<p class="alert-info alert">Successfully denied <?php echo $name; ?>'s friend request!<p>
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
<p class="alert-info alert">Successfully sent <?php echo $name; ?> a friend request!<p>
<?php
			}
		}
	}
}
?>

<script type="text/javascript">
function RemoveStatus(id) {
	if (confirm("Are you sure you want to delete this status?")) {
		document.location.href = '?removestatus=' + id;
	}
}
</script>

<?php

// Preventing spamming of form. [disabled]
//$antispam = true;
//if ($_loginaccount->GetConfigurationOption('last_status_sent', 0) != 0) {
//
//}

// If antispam passes, push status
	if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['content'])) {
		$content = nl2br(htmlentities(strip_tags(trim($_POST['content'])), ENT_COMPAT, 'UTF-8'));
		$reply_to = intval($_POST['reply-to']);
		$error = '';
		if ($content == '') {
			$error = 'That status was left blank, retry?';
		}
		else {
			// Check for duplicate
			$q = $__database->query("
SELECT 
	1
FROM 
	social_statuses 
WHERE 
	account_id = ".$_loginaccount->GetId()." 
	AND 
	content = '".$__database->real_escape_string($content)."'
	AND
	DATE_ADD(`timestamp`, INTERVAL 24 HOUR) >= NOW()
				");
			if ($q->num_rows != 0) {
				$error = 'You already said that!';
			}
			$q->free();
		}
		
		if ($error == '') {
			$blog = $_loginaccount->GetAccountRank() >= RANK_MODERATOR && isset($_POST['blog']) ? 1 : 0;

			$char_config = $_loginaccount->GetConfigurationOption('character_config', array('characters' => array(), 'main_character' => null));
			$has_characters = !empty($char_config['main_character']);

			// set internally
			$nicknm = $_loginaccount->GetNickname();
			$chr = $has_characters ? $char_config['main_character'] : '';
		
			$_loginaccount->SetConfigurationOption('last_status_sent', date("Y-m-d H:i:s"));

			$__database->query("
			INSERT INTO 
				social_statuses 
			VALUES 
				(
					NULL, 
					".$_loginaccount->GetId().",
					'".$__database->real_escape_string($nicknm)."', 
					'".$__database->real_escape_string($chr)."', 
					'".$__database->real_escape_string($content)."', 
					".$blog.", 
					NOW(), 
					0,
					".($reply_to == -1 ? 'NULL' : $reply_to)."
				)
			");	
			
			if ($__database->affected_rows == 1) {
?>
<p class="lead alert-success alert">The status was successfully posted!</p>
<?php
			}
			else {
				$error = 'The Maple Admin was not able to deliver your status update because of angry ribbon pigs! Retry?';
			}
		}
		
		if ($error != '') {
?>
<p class="lead alert-danger alert">Error: <?php echo $error; ?></p>
<?php
		}
	}
	elseif ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['removestatus'])) {
		// Removing status
		$id = intval($_GET['removestatus']);
		
		if ($_loginaccount->GetAccountRank() > RANK_DEVELOPER) {
			$__database->query("DELETE FROM social_statuses WHERE id = ".$id);
		}
		else {
			$__database->query("DELETE FROM social_statuses WHERE id = ".$id." AND account_id = ".$_loginaccount->GetId());
		}
		
		if ($__database->affected_rows == 1) {
?>
<p class="lead alert-info alert">The status was successfully deleted.</p>
<?php
		}
		else {
?>
<p class="lead alert-info alert">Unable to delete the status.</p>
<?php
		}
	}

?>

<div id="post" class="collapse poster" data-spy="affix" data-offset-top="10">
	<form method="post" style="padding-bottom:10px;border-bottom:1px solid rgba(0,0,0,0.2);">
		<h3 id="myModalLabel">Post a status?</h3>
		<textarea name="content" class="post-resize" id="post-status" placeholder="Type your status here!"></textarea>
		<input type="hidden" name="reply-to" value="-1" />
		<button type="submit" class="btn btn-large">Post!</button>
		<button type="button" class="btn btn-large btn-warn" onclick="$('#post-toggle-button').click();">Close</button>
<?php if ($_loginaccount->GetAccountRank() >= RANK_MODERATOR):?>
		Is this a blog post? <input type="checkbox" name="blog" value="Yes" />
<?php endif; ?>
	</form>
</div>

<?php
}
?>