<?php
require_once __DIR__.'/../inc/header.php';
require_once __DIR__.'/../inc/job_list.php';
require_once __DIR__.'/../inc/exp_table.php';
require_once 'pagination.php';

// Feel free to completely clean this entire thing up.
// It's put together poorly.

$sql = 'SELECT *
FROM
	`polls`
WHERE
	closed = 0
ORDER BY 
	`starred` DESC, `id` DESC';

// top 3
$q = $__database->query("
SELECT *
FROM
	`polls`
WHERE
	`starred` = '1'
ORDER BY 
	`starred` DESC, `id` DESC
LIMIT
0, 3
");
$pager = new PS_Pagination($__database, $sql, 5, 5, "");

$pager->setDebug(true);
$rs = $pager->paginate();
?>

<style>

.title {
	font-weight: bold;
	font-family: Helvetica, sans-serif;
	font-size: 34px;
	letter-spacing: 0px;
}

.more {
	font-weight: 200;
	letter-spacing: normal;
	font-size: 15px;
	color: #FFF;
}

table tr:hover {
	background: rgba(255,255,255,0.2) !important;
}

</style>

<div class="row">
		<div class="span7">	
		<table class="table table-hover">
		<thead>
			<tr>
				<th colspan="1">Name</th>
				<th>Creator</th>
				<th>Results</th>
			</tr>
		</thead>
<?php
while ($row = $rs->fetch_assoc()) {
$author = Account::Load($row['author']);
?>
	<tr class="span3" style="overflow:visible!important; cursor: pointer;" onclick="document.location = '//<?php echo $domain; ?>/player/<?php echo $row['name']; ?>'">
	
		<td style="vertical-align: middle">
			<?php echo $row['title']; ?>
		</td>
		<td style="vertical-align: middle">@<?php echo $author->GetUsername(); ?></td>
		<td style="vertical-align: middle"><a href="#">View results?</a></td>
	</tr>
<?php
}
?>
		</table>
		</div>
			<div class="span5" style="height:100% !important; float: right;">
			<small class="more" style="margin-top:10px;">
<?php
while ($row = $q->fetch_assoc()) {
$author = Account::Load($row['author']);
$maincharacter = $author->GetMainCharacterName();
?>
			<div class="status">
				<?php MakePlayerAvatar($maincharacter); ?>
				<p class="lead"><?php echo $row['title']; ?><br/>
				<span class="faded"><i>Posted by: @<?php echo $author->GetUsername(); ?></span><br/></p>
			</div>
<?php
}
?>
			</small>
		</p>
		
	</div>

</div>
      
<?php require_once __DIR__.'/../inc/footer.php'; ?>