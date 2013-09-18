<?php
require_once __DIR__.'/../inc/header.php';
require_once __DIR__.'/../inc/job_list.php';
require_once __DIR__.'/../inc/exp_table.php';
require_once 'pagination.php';

// Feel free to completely clean this entire thing up.
// It's put together poorly.

$__char_db = ConnectCharacterDatabase(CURRENT_LOCALE);
$locale_domain = $domain;
if (GMS) $locale_domain = 'gms.'.$locale_domain;
elseif (EMS) $locale_domain = 'ems.'.$locale_domain;
elseif (KMS) $locale_domain = 'kms.'.$locale_domain;

$localepls = 'gms';
//how do support moar w/ MakePlayerAvatar.

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

// top 5
$q = $__char_db->query("
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
	0, 5
");
$pager = new PS_Pagination($__char_db, $sql, 5, 5, "");

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
}

table tr:hover {
	background: rgba(255,255,255,0.3) !important;
}

</style>

<div class="row">
		<div class="span7">
		<p class="alert alert-info lead"><b><i class="icon-reorder"></i> Rankings</b></p>
		<center>		
    		<?php
    			echo $pager->renderPrev();
    			echo '&nbsp;';
    			echo $pager->renderNext();
    		?>
		</center>
		<br />
		<table class="table table-hover">
		<thead>
			<tr>
				<th colspan="2">Name</th>
				<th>Level</th>
				<th>Class</th>
			</tr>
		</thead>
<?php
while ($row = $rs->fetch_assoc()) {
?>
	<tr class="span3" style="overflow:visible!important; cursor: pointer;" onclick="document.location = '//<?php echo $domain; ?>/player/<?php echo $row['name']; ?>'">
	
		<td style="vertical-align: middle">
			<?php MakePlayerAvatar($row['name'], $localepls, array('styleappend' => 'float: none;')); ?>
		</td>
		<td style="vertical-align: middle">
			<img src="//<?php echo $domain; ?>/inc/img/worlds/<?php echo $row['world_name']; ?>.png" style="vertical-align: sub" title="<?php echo $row['world_name']; ?>" /> <?php echo $row['name']; ?>
		</td>
		<td style="vertical-align: middle"><?php echo $row['level']; ?><br />(<?php echo $row['exp']; ?> exp)</td>
		<td style="vertical-align: middle"><?php echo GetJobname($row['job']); ?></td>
	</tr>
<?php
}
?>
		</table>
		</div>
			<div class="span5" style="height:100% !important; float: right;">
			<p class="alert alert-info lead"><b><i class="icon-star"></i> Top 5</b></p>
			<small class="more" style="margin-top:10px;">
<?php
while ($row = $q->fetch_assoc()) {
?>
			<div class="status">
				<?php MakePlayerAvatar($row['name'], $localepls); ?>
				<p class="lead"><img src="//<?php echo $domain; ?>/inc/img/worlds/<?php echo $row['world_name']; ?>.png" /> <?php echo $row['name']; ?><br/>
				<span class="faded">Level <?php echo $row['level']; ?> <?php echo GetJobname($row['job']); ?></span><br/>
				<small><i class="icon-heart"></i> <?php echo $row['fame']; ?> Fame</small></p>
			</div>
<?php
}
?>
			</small>
		</p>
		
	</div>

</div>
      
<?php require_once __DIR__.'/../inc/footer.php'; ?>