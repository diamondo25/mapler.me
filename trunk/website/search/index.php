<?php
require_once __DIR__.'/../inc/header.php';
require_once __DIR__.'/../inc/job_list.php';
require_once __DIR__.'/../inc/templates/search.header.template.php';
?>
<div class="span9 search-results">
<div id="character_list">
<?php
if ($searching == '') {
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['type']) && $_POST['type'] == 'character') {
	$q = $__database->query("
SELECT 
	*,
	w.world_name,
	`GetCharacterAccountID`(id) AS account_id,
	TIMESTAMPDIFF(SECOND, last_update, NOW()) AS `secs_since`
FROM
	`characters` chr
LEFT JOIN 
	world_data w
	ON
		w.world_id = chr.world_id
ORDER BY
	last_update DESC
LIMIT
	0, 21
");

	if ($q->num_rows == 0) {
		$q->free();
?>
	<center>
		<img src="//<?php echo $domain; ?>/inc/img/no-character.gif" />
		<p>No characters were found matching your request!</p>
		</center>
	</div>
<?php
		require_once __DIR__.'/../inc/footer.php';
		die;
	}

	$characters_per_row = 3;
	$i = 0;
	while ($row = $q->fetch_assoc()) {
?>
<div class="character-brick clickable-brick span3 char <?php echo strtolower($row['world_name']); ?>" onclick="document.location = '//<?php echo $domain; ?>/player/<?php echo $row['name']; ?>'" style="width:210px !important;margin-bottom:10px;">
				<center>
					<br />
						<img src="//mapler.me/avatar/<?php echo $row['name']; ?>"/><br/>
						<p class="lead"><img src="//<?php echo $domain; ?>/inc/img/worlds/<?php echo $row['world_name']; ?>.png" /> <?php echo $row['name']; ?><br />
						<small>Level <?php echo $row['level']; ?> <?php echo GetJobname($row['job']); ?></small></p>
				</center>
			</div>
<?php
}
}
}
elseif (!$searching == '') {
$q = $__database->query("
SELECT 
	*,
	w.world_name,
	`GetCharacterAccountID`(id) AS account_id,
	TIMESTAMPDIFF(SECOND, last_update, NOW()) AS `secs_since`
FROM
	`characters` chr
LEFT JOIN 
	world_data w
	ON
		w.world_id = chr.world_id
WHERE 
	name = '".$__database->real_escape_string($searching)."'
");

	if ($q->num_rows == 0) {
		$q->free();
?>
	<center>
		<img src="//<?php echo $domain; ?>/inc/img/no-character.gif" />
		<p>No characters were found matching your request!</p>
		</center>
	</div>
<?php
		require_once __DIR__.'/../inc/footer.php';
		die;
	}
	while ($row = $q->fetch_assoc()) {
?>
		<div class="row" id="character_list">
<div class="status span9 char <?php echo strtolower($row['world_name']); ?>" onclick="document.location = '//<?php echo $domain; ?>/player/<?php echo $row['name']; ?>'">
		<div class="character" style="background: url('//mapler.me/avatar/<?php echo $row['name']; ?>') no-repeat center -17px #FFF;"></div>
		<p class="lead"><img src="//<?php echo $domain; ?>/inc/img/worlds/<?php echo $row['world_name']; ?>.png" /> <?php echo $row['name']; ?><br />
		<small>Level <?php echo $row['level']; ?> <?php echo GetJobname($row['job']); ?></small></p>
		<?php echo $row['name']; ?> was seen <?php echo time_elapsed_string($row['secs_since']); ?> ago.
</div>
<?php
}
}
?>
</div>
</div>
<?php require_once __DIR__.'/../inc/footer.php'; ?>