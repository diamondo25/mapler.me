<?php 
require_once __DIR__.'/../inc/header.php';
$pollid = intval($_GET['id']);

$poll = $__database->query("
SELECT *
FROM
	`polls`
WHERE
	polls.id = ".$pollid."
LIMIT
0, 1
");
$total = $poll->fetch_row();
$amount = $poll->fetch_array();
$opt = explode("|",$amount['options']);
$all = count($opt)-2;
$options = array($opt);

echo $options;
echo $all;

$votes = $__database->query("
SELECT COUNT(*)
FROM
	`polls_votes`
WHERE
	poll_id = ".$pollid."
GROUP BY
option_id
");

?>
	<div class="row">

<?php
while ($row = $poll->fetch_assoc()) {
$author = Account::Load($row['author']);
$maincharacter = $author->GetMainCharacterName();
?>
			<div class="span12 status">
				<?php MakePlayerAvatar($maincharacter); ?>
				<p class="lead"><?php echo $row['title']; ?><br/>
				<span class="faded"><i>Posted by: @<?php echo $author->GetUsername(); ?></span><br/></p>
			</div>
<?php
}
while ($row = $poll->fetch_assoc()) {
?>
			<div class="span12 status">
				<p class="lead"><?php echo $row['content']; ?></p>
				<div class="progress progress-striped active">
					<div class="bar" style="width: 100%;"></div>
				</div>
			</div>
<?php
}
?>
	</div>
<?php
require_once __DIR__.'/../inc/footer.php';
?>