These are just scripts / examples that have been removed or are not currently being used.

Display main character's image.
<?php
else:

	$char_config = $_loginaccount->GetConfigurationOption('character_config', array('characters' => array(), 'main_character' => null));

	$has_characters = !empty($char_config['main_character']);

?>
<p class="lead">
<?php 
	if ($has_characters):
		$main_character_name = $char_config['main_character'];
		$main_character_image = '//'.$domain.'/avatar/'.$main_character_name;

?>
	<img id="default_character" src="<?php echo $main_character_image; ?>" alt="<?php echo $main_character_name; ?>"/>
<?php
	endif;
?>

Contest display / check if something is null.
<?php
$q = $__database->query("
SELECT 
	assigned_to
FROM
	`beta_invite_keys`
WHERE 
	invite_key = 'BETADQ3A'
");

$check = $q->fetch_assoc();

if (!isset($check['assigned_to'])) {
?>
<p class="lead alert alert-info">Contest: We're giving away one beta key! <a href="/contest/">Click here for more information.</a></p>
<?php
}
?>

notifications menu I'll eventually add.
<?php if ($_loggedin): ?>
					<ul class="nav hidden-phone pull-right">
						<li class="dropdown">
							<a data-toggle="dropdown" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="100" data-close-others="true" href="#">notifications</b></a>
							<ul class="dropdown-menu">
							</ul>
			</li>
					</ul>
<?php endif; ?>
