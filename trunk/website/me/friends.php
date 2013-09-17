<?php
require_once __DIR__.'/../inc/header.php';
require_once __DIR__.'/../inc/templates/me.header.template.php';

$q = $__database->query("
SELECT
	a.*
FROM
(
	(
		SELECT
			friend.account_id AS `account_id`,
			friend.accepted_on
		FROM
			friend_list friend
		WHERE
			friend.friend_id = ".$__url_useraccount->GetId()."
			AND
			friend.accepted_on IS NOT NULL
	)
	UNION
	(
		SELECT
			friend.friend_id AS `account_id`,
			friend.accepted_on
		FROM
			friend_list friend
		WHERE
			friend.account_id = ".$__url_useraccount->GetId()."
			AND
			friend.accepted_on IS NOT NULL
	)
) a

ORDER BY
	a.accepted_on DESC
");

?>
	<div class="span9">
<?php
$characters_per_row = 3;
$i = 0;
while ($row = $q->fetch_assoc()) {
	if ($i % $characters_per_row == 0) {
		if ($i > 0) {
?>
		</div>
<?php
		}
?>
		<div class="row">
<?php
	}
	$i++;
	$account = Account::Load($row['account_id']);
	
	$main_char = $account->GetMainCharacterName();
	if ($main_char === null)
		$image_url = '//'.$domain.'/inc/img/no-character.gif';
	else
		$image_url = '//'.$main_char['locale'].'.'.$domain.'/ignavatar/'.$main_char['name'];
		
?>
			<div class="character-brick profilec span3 clickable-brick" onclick="document.location = '//<?php echo $account->GetUsername(); ?>.<?php echo $domain; ?>/'">
				<div class="caption"><?php echo $account->GetNickname(); ?></div>
				<center>
					<br />
					<a href="//<?php echo $account->GetUsername(); ?>.<?php echo $domain; ?>/">
						<img src="<?php echo $image_url; ?>"/>
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