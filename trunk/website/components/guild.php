<?php
require_once __DIR__.'/../inc/header.php';
require_once __DIR__.'/../inc/classes/guild.php';

$guild = new Guild();


if (!$guild->LoadByName($_GET['name'], $_GET['world'])) {
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
		<h1 class="name"><?php echo $guild->name; ?><br/>
			<small><?php echo $guild->notice; ?></small>
		</h1>
		<hr />
		<p class="name">
			<small class="name_extra" style="margin-top:10px;">Guild Leader: <?php echo $guild->ranks[0]; ?> -</small>
			<small class="name_extra" style="margin-top:10px;">Junior: <?php echo $guild->ranks[1]; ?> -</small>
			<small class="name_extra" style="margin-top:10px;">Rank: <?php echo $guild->ranks[2]; ?> -</small>
			<small class="name_extra" style="margin-top:10px;">Rank: <?php echo $guild->ranks[3]; ?> -</small>
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
	
	$img = !isset($character['name']) ? '/inc/img/no-character.gif' : '/ignavatar/'.$character['name'];
	
?>
			<div class="character-brick profilec span3">
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