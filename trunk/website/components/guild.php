<?php
require_once __DIR__.'/../inc/header.php';
require_once __DIR__.'/../inc/job_list.php';
require_once __DIR__.'/../inc/exp_table.php';

$q = $__database->query("
SELECT
	world_data.world_id,
	world_data.world_name,
	guilds.*,
	characters.id
FROM
	`guilds`
LEFT JOIN 
	`world_data`
	ON
		world_data.world_id = guilds.world_id
LEFT JOIN
	`guild_members`
	ON
		guild_members.guild_id = guilds.id
LEFT JOIN
	`characters`
	ON
		characters.id = guild_members.character_id
	
WHERE 
	guilds.name = '".$__database->real_escape_string($_GET['name'])."' AND
	world_data.world_name = '".$__database->real_escape_string($_GET['world'])."'
");

$x = $__database->query("
SELECT
	world_data.world_id,
	world_data.world_name,
	guilds.*,
	guild_members.*
FROM
	`guild_members`
LEFT JOIN
	`guilds`
	ON
		guilds.id = guild_members.guild_id
LEFT JOIN 
	`world_data`
	ON
		world_data.world_id = guilds.world_id	
WHERE 
	guilds.name = '".$__database->real_escape_string($_GET['name'])."' AND
	world_data.world_name = '".$__database->real_escape_string($_GET['world'])."'
");

$cache = array();
while ($row = $x->fetch_assoc()) {
	$cache[] = $row;
}

if ($q->num_rows == 0) {
	$q->free();
?>
<center>
	<img src="//<?php echo $domain; ?>/inc/img/no-character.gif" />
	<img src="//<?php echo $domain; ?>/inc/img/no-character.gif" />
	<img src="//<?php echo $domain; ?>/inc/img/no-character.gif" />
	<p>Guild not found! The guild you are looking for was eaten by Horntail.</p>
</center>
<?php
	require_once __DIR__.'/../inc/footer.php';
	die;
}

$guild = $q->fetch_assoc();
?>
<div class="row">
	<div class="span12">
	<center><h1 class="name"><?php echo $guild['name']; ?><br/>
		<small><?php echo $guild['notice']; ?></small>
	</h1>
	<hr />
	<p class="name">
		<small class="name_extra" style="margin-top:10px;">Guild Leader: <?php echo $guild['rank1']; ?> -</small>
		<small class="name_extra" style="margin-top:10px;">Junior: <?php echo $guild['rank2']; ?> -</small>
		<small class="name_extra" style="margin-top:10px;">Rank: <?php echo $guild['rank3']; ?> -</small>
		<small class="name_extra" style="margin-top:10px;">Rank: <?php echo $guild['rank4']; ?> -</small>
		<small class="name_extra" style="margin-top:10px;">Rank: <?php echo $guild['rank5']; ?></small>
	</p></center>
	</div>

	
	<div class="span12">
<?php
// This has to be fixed.
$characters_per_row = 4;
$i = 0;
foreach ($cache as $row) {
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
?>
			<div class="character-brick profilec span3">
				<center>
					<br />
					<img src="//<?php echo $domain; ?>/inc/img/no-character.gif" />
					<br />
				</center>
			</div>

<?php
}
?>

	</div>
<?php
// $__database->GetRanQueries();
require_once __DIR__.'/../inc/footer.php';
?>