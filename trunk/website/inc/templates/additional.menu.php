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
				<?php BuildURL('General', '/settings/general/'); ?>
				<?php BuildURL('Privacy', '/settings/privacy/'); ?>
				<?php BuildURL('Characters', '/settings/characters/'); ?>
				<?php BuildURL('Friend Requests', '/settings/friends/'); ?>
                <?php BuildURL('Connections', '/settings/connections/'); ?>
				<?php //BuildURL('Notifications', '/panel/settings/notifications/'); ?>
				<?php //BuildURL('Memberships', '/panel/settings/memberships/'); ?>
<?php
}
elseif (strpos($_SERVER['REQUEST_URI'], '/manage/') !== FALSE) {
?>
				<?php BuildURL('General', '/manage/general/'); ?>
				<?php BuildURL('Statuses', '/manage/statuses/'); ?>
				<?php BuildURL('Revisions', '/manage/revisions/'); ?>
				<?php BuildURL('Statistics', '/manage/statistics/'); ?>
				<?php BuildURL('Server Log', '/manage/serverlog/'); ?>
				<?php BuildURL('Search', '/manage/findstring/'); ?>
<?php
}
?>