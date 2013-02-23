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

		<div class="span2">
			<ul class="nav nav-tabs nav-stacked">
				<?php BuildURL('General', '/panel/settings/general/'); ?>
				<?php BuildURL('Privacy', '/panel/settings/privacy/'); ?>
				<?php BuildURL('Accounts', '/panel/settings/accounts/'); ?>
				<?php BuildURL('Characters', '/panel/settings/characters/'); ?>
				<?php //BuildURL('Notifications', '/panel/settings/notifications/'); ?>
				<?php //BuildURL('Memberships', '/panel/settings/memberships/'); ?>
			</ul>
		</div>