<?php
function IsActive($name) {
	echo strpos($_SERVER['REQUEST_URI'], $name) !== false ? ' class="active"' : '';
}

function BuildURL($name, $url) {
?>
				<li<?php IsActive($url); ?>><a href="<?php echo $url; ?>"><?php echo $name; ?> <i class="icon-chevron-right"></i></a></li>
<?php
}
?>

<?php
if (strpos($_SERVER['REQUEST_URI'], '/settings/') !== FALSE) {
?>
				<?php BuildURL('Profile', '/settings/profile/'); ?>
				<?php BuildURL('Privacy', '/settings/privacy/'); ?>
				<?php BuildURL('Accounts', '/settings/accounts/'); ?>
				<?php BuildURL('Characters', '/settings/characters/'); ?>
				<?php BuildURL('Friends', '/settings/friends/'); ?>
				<?php //BuildURL('Notifications', '/panel/settings/notifications/'); ?>
				<?php //BuildURL('Memberships', '/panel/settings/memberships/'); ?>
<?php
}
elseif (strpos($_SERVER['REQUEST_URI'], '/manage/') !== FALSE) {
?>
				<?php BuildURL('General', '/manage/general/'); ?>
				<?php BuildURL('Revisions', '/manage/revisions/'); ?>
				<?php BuildURL('PHP Info', '/manage/php/'); ?>
				<?php BuildURL('Log', '/manage/serverlog/'); ?>
<?php
}
?>

<style>
</style>