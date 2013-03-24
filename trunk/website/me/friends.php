<?php
require_once __DIR__.'/../inc/header.php';
require_once __DIR__.'/../inc/me_header.template.php';

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
			friend.friend_id = ".$__url_useraccount->GetId()."
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
			friend.account_id = ".$__url_useraccount->GetId()."
	)
) a

ORDER BY
	a.accepted_on DESC
");

while ($row = $q->fetch_assoc()) {
	$account = Account::Load($row['account_id']);
	
	$main_char = $__url_useraccount->GetMainCharacterName();
	if ($main_char == null)
		$main_char = 'inc/img/no-character.gif';
	else
		$main_char = 'avatar/'.$main_char;
		
	$did_add = $row['accepted_on'] != NULL;

?>

	<!-- Character Display -->

<?php
if (count($row) == 0) {
?>
	<center>
		<img src="//<?php echo $domain; ?>/inc/img/no-character.gif"/>
		<p><?php echo $__url_useraccount->GetNickName(); ?> hasn't added friends yet!</p>
	</center>
<?php
}
?>

<?php if ($did_add): ?>
			You have been friends with <?php echo $account->GetNickname(); ?> for <?php echo time_elapsed_string($row['accepted_on_secs']); ?>! <button class="btn btn-danger pull-right" onclick="RemoveFriend('<?php echo $account->GetUsername(); ?>')">Unfriend!</button>
<?php endif; ?>

		<div class="row" style="float: right;">
			<div class="character-brick profilec span3 clickable-brick" onclick="document.location = '//<?php echo $account->GetUsername(); ?>.<?php echo $domain; ?>/'">
			<div class="caption"><?php echo $account->GetNickname(); ?></div>
				<center>
					<br />
					<a href="//<?php echo $account->GetUsername(); ?>.<?php echo $domain; ?>/" style="text-decoration: none !important; font-weight: 300; color: inherit;">
						<img src="//<?php echo $domain; ?>/<?php echo $main_char; ?>"/>
					</a>
					<br />
				</center>
			</div>

<?php
}
?>
		</div>
</div>

<?php require_once __DIR__.'/../inc/footer.php'; ?>