<?php
require_once __DIR__.'/../inc/header.php';
require_once __DIR__.'/../inc/templates/search.header.template.php';

$searching = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search'])) {
	$searchback = nl2br(htmlentities(strip_tags(trim($_POST['search']))));
	if (!empty($searchback)) {
		$searching = $searchback;
	}
}
?>
<div class="span9 search-results">
<?php
if ($searching == '') {
?>
<p class="lead alert alert-danger">Nothing was included in your search! Try again?</p>
</div>
<?php
	require_once __DIR__.'/../inc/footer.php';
	die;
}
?>

<p class="lead">You searched for <i><?php echo $searching; ?></i>!</p>
<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['type']) && $_POST['type'] == 'status') {

	$statusses = new Statusses();
	$statusses->Load("s.content LIKE '%".$__database->real_escape_string($searching)."%'", '0, 10');

	if ($statusses->Count() == 0) {
?>
<center>
	<img src="//<?php echo $domain; ?>/inc/img/no-character.gif" />
	<p>No statuses were found containing <?php echo $searching; ?>!</p>
</center>
</div>
<?php
		require_once __DIR__.'/../inc/footer.php';
		die;
	}

	foreach ($statusses->data as $status) {
		$status->PrintAsHTML('');
	}
} //end of $_GET['type'] == 'status'


elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['type']) && $_POST['type'] == 'player') {
	$q = $__database->query("
SELECT
	*
FROM
	accounts
WHERE
	username LIKE '%".$__database->real_escape_string($searching)."%'
ORDER BY
	last_login DESC
LIMIT
	0, 99
");

	if ($q->num_rows == 0) {
		$q->free();
?>
	<center>
		<img src="//<?php echo $domain; ?>/inc/img/no-character.gif" />
		<p>No maplers were found containing <?php echo $searching; ?>!</p>
		</center>
	</div>
<?php
		require_once __DIR__.'/../inc/footer.php';
		die;
	}

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
		$account = Account::Load($row['id']);
		$main_char = $account->GetMainCharacterName();
		if ($main_char == null)
			$main_char = 'inc/img/no-character.gif';
		else
			$main_char = 'avatar/'.$main_char;
?>
<div class="character-brick profilec span3 clickable-brick" onclick="document.location = '//<?php echo $account->GetUsername(); ?>.<?php echo $domain; ?>/'">
				<div class="caption"><?php echo $account->GetNickname(); ?></div>
				<center>
					<br />
					<a href="//<?php echo $account->GetUsername(); ?>.<?php echo $domain; ?>/" style="text-decoration: none !important; font-weight: 300; color: inherit;">
						<img src="//mapler.me/<?php echo $main_char; ?>"/>
					</a>
					<br />
				</center>
			</div>
<?php
	}
}
?>
</div>
</div>
</div>
<?php require_once __DIR__.'/../inc/footer.php'; ?>