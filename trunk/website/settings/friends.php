<?php
$q = $__database->query("
SELECT
	*,
	TIMESTAMPDIFF(SECOND, friend_list.added_on, NOW()) AS `added_on_secs`,
	TIMESTAMPDIFF(SECOND, friend_list.accepted_on, NOW()) AS `accepted_on_secs`
FROM
	friend_list
WHERE
	friend_id = ".$_loginaccount->GetId()." AND
	accepted_on IS NULL

ORDER BY
	accepted_on DESC
");

$cache = array();

while ($row = $q->fetch_assoc()) {
	$cache[] = $row;
}

if (count($cache) == 0) {
?>
	<center>
		<img src="//<?php echo $domain; ?>/inc/img/no-character.gif"/>
		<p>You don't have any friend requests!</p>
	</center>
	</div>
<?php
}

while ($row = $q->fetch_assoc()) {
	$account = Account::Load($row['account_id']);
	
	$main_char = $account->GetMainCharacterName();
	if ($main_char == null)
		$main_char = 'inc/img/no-character.gif';
	else
		$main_char = 'avatar/'.$main_char;
?>
		<div class="status">
			<div class="pull-left">
				<img src="//<?php echo $domain; ?>/<?php echo $main_char; ?>" />
			</div>
			<h3 style="margin-top: 10px;"><a href="//<?php echo $account->GetUsername(); ?>.<?php echo $domain; ?>/"><?php echo $account->GetNickname(); ?></a> <small>sent this request <?php echo time_elapsed_string($row['added_on_secs']); ?> ago...</small> <br/><button class="btn btn-mini btn-success" style="margin-top:10px;" onclick="AcceptFriend('<?php echo $account->GetUsername(); ?>')">Accept?</button> <button class="btn btn-mini btn-danger" style="margin-top:10px;" onclick="DenyFriend('<?php echo $account->GetUsername(); ?>')">Deny?</button></h3>
<?php
}
$q->free();
?>