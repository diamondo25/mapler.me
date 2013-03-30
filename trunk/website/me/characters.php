<?php
require_once __DIR__.'/../inc/header.php';

$char_config = $__url_useraccount->GetConfigurationOption('character_config', array('characters' => array(), 'main_character' => null));

$q = $__database->query("
SELECT
	chr.id,
	chr.name,
	w.world_name
FROM
	characters chr
LEFT JOIN
	users usr
	ON
		usr.ID = chr.userid
LEFT JOIN
	world_data w
	ON
		w.world_id = chr.world_id
WHERE
	usr.account_id = '".$__database->real_escape_string($__url_useraccount->GetID())."'
ORDER BY
	chr.world_id ASC,
	chr.last_update DESC
");

$cache = array();

$selected_main_character = $char_config['main_character'];
$character_display_options = $char_config['characters'];

while ($row = $q->fetch_assoc()) {
	if (isset($character_display_options[$row['name']])) {
		if ($character_display_options[$row['name']] == 2) { // Always hide... :)
			continue;
		}
	}
	$cache[] = $row;
}
$q->free();

$has_characters = count($cache) != 0;
$main_character_info = $has_characters ? $cache[0] : null;
$main_character_name = $has_characters ? ($selected_main_character != null ? $selected_main_character : $main_character_info['name']) : '';
$main_character_image = $has_characters ? '//'.$domain.'/avatar/'.$main_character_name : '';


require_once __DIR__.'/../inc/me.header.template.php';

?>

	<!-- Character Display -->

<?php
if (count($cache) == 0) {
?>
	<center>
		<img src="//<?php echo $domain; ?>/inc/img/no-character.gif"/>
		<p><?php echo $__url_useraccount->GetNickName(); ?> hasn't added any characters yet!</p>
	</center>
<?php
}

// printing table rows

$characters_per_row = 3;
$i = 0;
foreach ($cache as $row) {
	if ($i % $characters_per_row == 0) {
		if ($i > 0) {
?>
		</div>
<?php
		}
?>
		<div class="row" style="float: right;">
<?php
	}
	$i++;
?>
			<div class="character-brick profilec span3 clickable-brick" onclick="document.location = '//<?php echo $domain; ?>/player/<?php echo $row['name']; ?>'">
			<div class="caption"><img src="//<?php echo $domain; ?>/inc/img/worlds/<?php echo $row['world_name']; ?>.png" />&nbsp;<?php echo $row['name']; ?></div>
				<center>
					<br />
					<a href="//<?php echo $domain; ?>/player/<?php echo $row['name']; ?>" style="text-decoration: none !important; font-weight: 300; color: inherit;">
						<img src="//<?php echo $domain; ?>/avatar/<?php echo $row['name']; ?>"/>
					</a>
					<br />
				</center>
			</div>

<?php
}
?>
		</div>
</div>

<?php require_once __DIR__.'/../inc/footer.php'; ?>