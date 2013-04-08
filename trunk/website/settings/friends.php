<script type="text/javascript">
function RemoveFriend(id) {
	if (confirm("Are you sure you want to unfriend this person?")) {
		document.location.href = '?removeid=' + id;
	}
}
function AcceptFriend(id) {
	document.location.href = '?acceptid=' + id;
}
</script>
<?php

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
	if (isset($_GET['acceptid'])) {
		$name = $_GET['acceptid'];
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
	elseif (isset($_GET['removeid'])) {
		$name = $_GET['removeid'];
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


$q = $__database->query("
SELECT
	a.*
FROM
(
	(
		SELECT
			friend.account_id AS `account_id`,
			friend.added_on,
			friend.accepted_on,
			TIMESTAMPDIFF(SECOND, friend.added_on, NOW()) AS `added_on_secs`,
			TIMESTAMPDIFF(SECOND, friend.accepted_on, NOW()) AS `accepted_on_secs`,
			0 AS `added_by_yourself`
		FROM
			friend_list friend
		WHERE
			friend.friend_id = ".$_loginaccount->GetId()."
	)
	UNION
	(
		SELECT
			friend.friend_id AS `account_id`,
			friend.added_on,
			friend.accepted_on,
			TIMESTAMPDIFF(SECOND, friend.added_on, NOW()) AS `added_on_secs`,
			TIMESTAMPDIFF(SECOND, friend.accepted_on, NOW()) AS `accepted_on_secs`,
			1 AS `added_by_yourself`
		FROM
			friend_list friend
		WHERE
			friend.account_id = ".$_loginaccount->GetId()."
	)
) a

ORDER BY
	a.accepted_on DESC
");

while ($row = $q->fetch_assoc()) {
	$account = Account::Load($row['account_id']);
	
	$main_char = $account->GetMainCharacterName();
	if ($main_char == null)
		$main_char = 'inc/img/no-character.gif';
	else
		$main_char = 'avatar/'.$main_char;
		
	$did_add = $row['accepted_on'] != NULL;
	
?>
		<div class="row">
			<div class="pull-left">
				<img src="//<?php echo $domain; ?>/<?php echo $main_char; ?>" style="padding: 17px;" />
			</div>
			<h3 style="margin-top: 40px;"><a href="//<?php echo $account->GetUsername(); ?>.<?php echo $domain; ?>/"><?php echo $account->GetNickname(); ?></a></h3>
			<div>
<?php if ($did_add): ?>
			You have been friends with <?php echo $account->GetNickname(); ?> for <?php echo time_elapsed_string($row['accepted_on_secs']); ?>! <button class="btn btn-danger pull-right" onclick="RemoveFriend('<?php echo $account->GetUsername(); ?>')">Unfriend!</button>
<?php elseif ($row['added_by_yourself'] == 1): ?>
			You are still awaiting a response for <?php echo time_elapsed_string($row['added_on_secs']); ?>... <button class="btn btn-danger pull-right" onclick="RemoveFriend('<?php echo $account->GetUsername(); ?>')">Cancel?</button>
<?php elseif ($row['added_by_yourself'] == 0): ?>
			<?php echo $account->GetNickname(); ?> sent this request <?php echo time_elapsed_string($row['added_on_secs']); ?> ago... <button class="btn btn-danger pull-right" onclick="AcceptFriend('<?php echo $account->GetUsername(); ?>')">Accept now?</button>
<?php endif; ?>
			</div>
		</div>
<?php
}




?>