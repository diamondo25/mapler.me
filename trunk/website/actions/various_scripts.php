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

If Contributions actually had descriptions.

<div class="row" id="people">
	
	<div class="span6 status person">
		<div class="header">
		<i class="icon-star"></i> Maryse (@Maryse)
		</div>
		Maryse helped us tackle several bugs with our client during private beta, as well as reporting numerous issues across the site to our team. It's a pleasure having her as a member.
	</div>
	<div class="span6 status person">
		<div class="header">
		<i class="icon-star"></i> Katie (@Katie)
		</div>
		As one of the first members of our private beta, there were many issues that had to be addressed regarding our client. Katie worked together with our team to go through all sixty of her characters to help us find any items or values that were causing saving to fail. Thankfully because of her and Maryse, our client is functioning wonderfully now.
	</div>
	<div class="span6 status person">
		<div class="header">
		<i class="icon-star"></i> Xparasite9 (@xparasite9)
		</div>
		During the early days of Mapler.me, Xparasite9 generiously spent time to hunt for coding issues with our site, and actively reported them to us. This would include fixes to potential, character privacy, caching flaws, and much more.
	</div>
	<div class="span6 status person">
		<div class="header">
		<i class="icon-star"></i> Sue (@Goates)
		</div>
		Sue, a web designer within the MapleStory community was one of the first to contribute content to Mapler.me. She was responsible for one of the layouts of Mapler.me which lasted for over a month before we began work on our final layout. We appreciate her time as it helped inspire the design for how Mapler.me is today.
	</div>
	<div class="span6 status person">
		<div class="header">
		<i class="icon-star"></i> Jessica (@CraftyPixel on Twitter)
		</div>
		Jessica designed and drew our current backgrounds throughout our site, as well as many other graphical elements. We appreciate her time and amazing artwork she's contributed to Mapler.me.
	</div>
</div>

