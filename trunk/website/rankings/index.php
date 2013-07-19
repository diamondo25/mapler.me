<?php
require_once __DIR__.'/../inc/header.php';
require_once __DIR__.'/../inc/job_list.php';
require_once __DIR__.'/../inc/exp_table.php';
require_once 'pagination.php';

// Feel free to completely clean this entire thing up.
// It's put together poorly.

$sql = 'SELECT *,
	w.world_name,
	`GetCharacterAccountID`(id) AS account_id
FROM
	`characters` chr
LEFT JOIN 
	world_data w
	ON
		w.world_id = chr.world_id
WHERE NOT job BETWEEN 800 AND 1000 ORDER BY `level` DESC, `exp` DESC';

// top 3
$q = $__database->query("
SELECT *,
	w.world_name,
	`GetCharacterAccountID`(id) AS account_id
FROM
	`characters` chr
LEFT JOIN 
	world_data w
	ON
		w.world_id = chr.world_id
WHERE NOT
	job BETWEEN 800 AND 1000
ORDER BY
	level DESC
LIMIT
	0, 3
");
$pager = new PS_Pagination($__database_traditional, $sql, 5, 5, "");

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
		<img src="http://i.imm.io/1c51p.png" style="border-radius:3px;"/>
		<table class="table table-hover">
		<thead><tr><th>Name</th> <th>Level</th> <th>Class</th></tr></thead>
		<?php
			
	if(!$rs) die(mysql_error());
	while($row = mysql_fetch_assoc($rs)) {
	?>
	<tr class="span3" style="overflow:visible!important;" onclick="document.location = '//<?php echo $domain; ?>/player/<?php echo $row['name']; ?>'">
	
		<td>
			<div class="character" style="background: url('//mapler.me/avatar/<?php echo $row['name']; ?>?size=small') no-repeat center -2px rgba(0,0,0,0.5);"></div>
			<img src="//<?php echo $domain; ?>/inc/img/worlds/<?php echo $row['world_name']; ?>.png" /> <?php echo $row['name']; ?>
		</td>
		<td>
			<?php echo $row['level']; ?>
		</td>
		<td>
		<?php echo GetJobname($row['job']); ?>
		</td>
	</tr>
	<?php
	}
	?>
		</table>
		</div>
			<div class="span5" style="height:100% !important; float: right;">
		<p class="title"><img src="http://i.imm.io/1c53T.png" style="border-radius:3px;"/><br/>
			<small class="more" style="margin-top:10px;">
			<?php
			while ($row = $q->fetch_assoc()) {
			?>
			<div class="status">
			<div class="character" style="background: url('//mapler.me/avatar/<?php echo $row['name']; ?>?size=small') no-repeat center -2px rgba(0,0,0,0.5);"></div>
			<p class="lead"><img src="//<?php echo $domain; ?>/inc/img/worlds/<?php echo $row['world_name']; ?>.png" /> <?php echo $row['name']; ?><br/>
			<span class="faded">Level <?php echo $row['level']; ?> <?php echo GetJobname($row['job']); ?></span><br/>
			<small><i class="icon-heart"></i> <?php echo $row['fame']; ?></small></p>
			</div>
			<?php
			}
			?>
			</small>
		</p>
		<?php
			echo $pager->renderPrev();
			echo '&nbsp;';
			echo $pager->renderNext();
		?>
		
	</div>

</div>
      
<?php require_once __DIR__.'/../inc/footer.php'; ?>