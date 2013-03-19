<?php
require_once __DIR__.'/../inc/header.php';

$char_config = $__url_useraccount->GetConfigurationOption('character_config', array('characters' => array(), 'main_character' => null));


$x = $__database->query("
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
	chr.level DESC
");

$cache = array();

$selected_main_character = $char_config['main_character'];
$character_display_options = $char_config['characters'];

while ($row = $x->fetch_assoc()) {
	if (isset($character_display_options[$row['name']])) {
		if ($character_display_options[$row['name']] == 2) { // Always hide... :)
			continue;
		}
	}
	$cache[] = $row;
}
$x->free();

$has_characters = count($cache) != 0;
$main_character_info = $has_characters ? $cache[0] : null;
$main_character_name = $has_characters ? ($selected_main_character != null ? $selected_main_character : $main_character_info['name']) : '';
$main_character_image = $has_characters ? '//'.$domain.'/avatar/'.$main_character_name : '';

require_once __DIR__.'/../inc/me_header.template.php';

?>

<?php


$q = $__database->query("
SELECT
	*,
	TIMESTAMPDIFF(SECOND, timestamp, NOW()) AS `secs_since`
FROM
	social_statuses
WHERE
	account_id = '".$__database->real_escape_string($__url_useraccount->GetID())."'
ORDER BY
	secs_since ASC
");

$social_cache = array();
while ($row = $q->fetch_assoc()) {
	$social_cache[] = $row;
}

$q->free();



if (count($social_cache) == 0) {
?>
	<center>
		<img src="//<?php echo $domain; ?>/inc/img/no-character.gif"/>
		<p><?php echo $__url_useraccount->GetNickName(); ?> hasn't posted anything yet!</p>
	</center>
<?php
}
?>
<div class="span9">
<?php
// printing table rows

foreach ($social_cache as $row) {
?>
		<div class="status">
			<div class="header"><?php echo $row['nickname'];?> said:
				<span class="pull-right">
					<a href="//<?php echo $domain; ?>/stream/status/<?php echo $row['id']; ?>"><?php echo time_elapsed_string($row['secs_since']); ?> ago</a>

<?php
	if ($_loggedin) {
		if (IsOwnAccount()) {
?>
					- <a href="#" onclick="RemoveStatus(<?php echo $row['id']; ?>)">delete?</a>
<?php
		}
		else {
			// Report button
?>
					- <a href="#"></a>
<?php
		}
	}
?>
				</span>
			</div>
			<br />
			<img src="http://mapler.me/avatar/<?php echo $row['character']; ?>" class="pull-right" />
			<?php $parser->parse($row['content']); echo $parser->getAsHtml(); ?>
		</div>

<?php
}
?>
</div>
</div>
<?php require_once __DIR__.'/../inc/footer.php'; ?>