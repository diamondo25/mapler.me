<?php
require_once __DIR__.'/../inc/header.php';
require_once __DIR__.'/../inc/classes/guild.php';

$guild = new Guild();
$name = $_GET['name'];
$world = $_GET['world'];

if (!$guild->LoadByName($name, $world)) {
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

?>
<div class="row">
	<div class="span12">
	<center>
		<h1 class="name"><span class="faded"><?php echo $guild->world_name; ?> / </span><?php echo $guild->name; ?> <span class="faded capacity">(<?php echo count($guild->members); ?>/<?php echo $guild->capacity; ?>)</span><br/>
			<small>"<?php echo $guild->notice; ?>"</small>
		</h1>
		<hr />
		<p class="name"><small class="name_extra" style="margin-top:10px;"><b>Ranks:</b></small>
			<small class="name_extra" style="margin-top:10px;">Guild Leader: <?php echo $guild->ranks[0]; ?> /</small>
			<small class="name_extra" style="margin-top:10px;">Junior: <?php echo $guild->ranks[1]; ?> /</small>
			<small class="name_extra" style="margin-top:10px;">Rank: <?php echo $guild->ranks[2]; ?> /</small>
			<small class="name_extra" style="margin-top:10px;">Rank: <?php echo $guild->ranks[3]; ?> /</small>
			<small class="name_extra" style="margin-top:10px;">Rank: <?php echo $guild->ranks[4]; ?></small>
		</p>
	</center>
	</div>
	
	<div class="span12">
<?php
// This has to be fixed.
$characters_per_row = 4;
$i = 0;
foreach ($guild->members as $character) {
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
	
	$img = !isset($character['name']) ? '/inc/img/no-character.gif' : '/avatar/'.$character['name'];
	
?>
			<div class="character-brick profilec span3 clickable-brick" onclick="document.location = '//<?php echo $domain; ?>/player/<?php echo $character['name']; ?>'">
				<div class="caption"><img src="//mapler.me/inc/img/worlds/<?php echo $character['world_name']; ?>.png"> <?php echo $character['name']; ?></div>
				<center>
					<br />
					<img src="//<?php echo $domain.$img; ?>" />
					<br />
					<br />
					<?php echo $guild->ranks[$character['rank'] - 1]; ?>
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