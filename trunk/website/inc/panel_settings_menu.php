<?php
function IsActive($name) {
	echo strpos($_SERVER['REQUEST_URI'], $name) !== false ? ' class="active"' : '';
}

function BuildURL($name, $url) {
?>
				<li<?php IsActive($url); ?>><a href="<?php echo $url; ?>"><?php echo $name; ?></a></li>
<?php
}
?>

				<?php BuildURL('Profile', '/settings/profile/'); ?>
				<?php BuildURL('Privacy', '/settings/privacy/'); ?>
				<?php BuildURL('Accounts', '/settings/accounts/'); ?>
				<?php BuildURL('Characters', '/settings/characters/'); ?>
				<?php BuildURL('Friends', '/settings/friends/'); ?>
				<?php //BuildURL('Notifications', '/panel/settings/notifications/'); ?>
				<?php //BuildURL('Memberships', '/panel/settings/memberships/'); ?>