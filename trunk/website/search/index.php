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
$q = $__database->query("
SELECT
	*,
	TIMESTAMPDIFF(SECOND, timestamp, NOW()) AS `secs_since`
FROM
	social_statuses
LEFT JOIN
	accounts
	ON
		social_statuses.account_id = accounts.id
WHERE
	content LIKE '%".$__database->real_escape_string($searching)."%'
ORDER BY
	secs_since ASC
LIMIT
	0, 10
");

$cache = array();
while ($row = $q->fetch_assoc()) {
	$cache[] = $row;
}

if ($q->num_rows == 0) {
	$q->free();
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
?>

<?php
foreach ($cache as $row) {
		$content = $row['content'];
		//@replies
		$content = preg_replace('/(^|[^a-z0-9_])@([a-z0-9_]+)/i', '$1<a href="http://$2.mapler.me/">@$2</a>', $content);
		//#hashtags (no search for the moment)
		$content = preg_replace('/(^|[^a-z0-9_])#([a-z0-9_]+)/i', '$1<a href="#">#$2</a>', $content);
?>
<div class="status <?php if ($row['override'] == 1): ?> notification<?php endif; ?><?php if ($row['account_id'] == $_loginaccount->GetID()): ?> postplox<?php endif; ?> statuss" style="margin:10px;">
				<div class="header" style="background: url('http://mapler.me/avatar/<?php echo $row['character']; ?>') no-repeat right -30px #FFF;">
					<a href="//<?php echo $row['username'];?>.<?php echo $domain; ?>/"><?php if ($row['account_id'] == $_loginaccount->GetID()): ?>You<?php else: echo $row['nickname']; endif; ?></a> said:
				</div>
				<br />
				<?php $parser->parse($content); echo $parser->getAsHtml(); ?>
				<div class="status-extra">
					<?php if ($row['comments_disabled'] == '0'): ?>
					<a href="//<?php echo $domain; ?>/stream/status/<?php echo $row['id']; ?>#disqus_thread"></a>
					<img src="//<?php echo $domain; ?>/inc/img/icons/comment.png"/> – <?php endif; ?><a href="//<?php echo $domain; ?>/stream/status/<?php echo $row['id']; ?>"><?php echo time_elapsed_string($row['secs_since']); ?> ago</a>
				</div></div>
<?php
}
?>
</div>
</div>
</div>
<?php require_once __DIR__.'/../inc/footer.php'; ?>