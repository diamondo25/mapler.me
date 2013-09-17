<?php
$q = $__database->query("
SELECT
	*,
	TIMESTAMPDIFF(SECOND, added_on, NOW()) AS `added_on_secs`,
	TIMESTAMPDIFF(SECOND, accepted_on, NOW()) AS `accepted_on_secs`
FROM
	friend_list
WHERE
	friend_id = ".$_loginaccount->GetId()."
	AND
	accepted_on IS NULL

ORDER BY
	added_on DESC
");

if ($q->num_rows == 0) {
?>
<p class="lead alert-info alert">There are no friend requests pending.</p>
<?php
}
else {

	while ($row = $q->fetch_assoc()) {
		$account = Account::Load($row['account_id']);

		$main_char = $account->GetMainCharacterName();
		if ($main_char === null)
			$image_url = '//'.$domain.'/inc/img/no-character.gif';
		else
			$image_url = '//'.$main_char['locale'].'.'.$domain.'/ignavatar/'.$main_char['name'];
?>
		<div class="status">
			<div class="pull-left">
				<img src="<?php echo $image_url; ?>" />
			</div>
			<h3 style="margin-top: 10px;"><a href="//<?php echo $account->GetUsername(); ?>.<?php echo $domain; ?>/"><?php echo $account->GetNickname(); ?></a> <small>sent this request <?php echo time_elapsed_string($row['added_on_secs']); ?> ago...</small> <br/><button class="btn btn-mini btn-success" style="margin-top:10px;" onclick="AcceptFriend('<?php echo $account->GetUsername(); ?>')">Accept?</button> <button class="btn btn-mini btn-danger" style="margin-top:10px;" onclick="DenyFriend('<?php echo $account->GetUsername(); ?>')">Deny?</button></h3>
		</div>
<?php
	}
}
$q->free();
?>