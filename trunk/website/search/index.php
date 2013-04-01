<?php
require_once __DIR__.'/../inc/header.php';
require_once __DIR__.'/../inc/search.header.template.php';

?>
<div class="span9 search-results">
<?php
if (GetSearch() == 'Search?') {
	echo '<p class="lead alert alert-danger">Nothing was included in your search! Try again?</p>
	</div>';
	require_once __DIR__.'/../inc/footer.php';
	die;
}
?>

<p class="lead">You searched for <i><?php echo GetSearch(); ?></i>!</p>
<?php
$searchfix = GetSearch();
$q = $__database->query("
SELECT
	*,
	TIMESTAMPDIFF(SECOND, timestamp, NOW()) AS `secs_since`
FROM
	social_statuses
WHERE
	content LIKE '%$searchfix%'
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
	<p>No statuses were found containing <?php echo GetSearch(); ?>!</p>
</center>
<?php
	require_once __DIR__.'/inc/footer.php';
	die;
}
?>

<?php
foreach ($cache as $row) {
		$content = $row['content'];
		//@replies
		$content1 = preg_replace('/(^|[^a-z0-9_])@([a-z0-9_]+)/i', '$1<a href="http://$2.mapler.me/">@$2</a>', $content);
		//#hashtags (no search for the moment)
		$content2 = preg_replace('/(^|[^a-z0-9_])#([a-z0-9_]+)/i', '$1<a href="#">#$2</a>', $content1);
?>
<div class="status <?php if ($row['override'] == 1): ?> notification<?php endif; ?><?php if ($row['account_id'] == $_loginaccount->GetID()): ?> postplox<?php endif; ?> statuss" style="margin:10px;">
				<div class="header" style="background: url('http://mapler.me/avatar/<?php echo $row['character']; ?>') no-repeat right -30px #FFF;">
					<a href="//<?php echo $row['username'];?>.<?php echo $domain; ?>/"><?php if ($row['account_id'] == $_loginaccount->GetID()): ?>You<?php else: echo $row['nickname']; endif; ?></a> said:
				</div>
				<br />
				<?php $parser->parse($content2); echo $parser->getAsHtml(); ?>
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