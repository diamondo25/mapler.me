<?php
function IsActive($name) {
	echo strpos($_SERVER['REQUEST_URI'], $name) !== false ? ' class="active"' : '';
}
?>

		<div class="span2">
			<ul class="nav nav-tabs nav-stacked">
				<li<?php IsActive('/panel/settings/general/'); ?>><a href="/panel/settings/general/">General</a></li>
				<li<?php IsActive('/panel/settings/notifications/'); ?>><a href="/panel/settings/notifications/">Notifications</a></li>
				<li<?php IsActive('/panel/settings/memberships/'); ?>><a href="/panel/settings/memberships/">Memberships</a></li>
				<li<?php IsActive('/panel/settings/privacy/'); ?>><a href="/panel/settings/privacy/">Privacy</a></li>
			</ul>
		</div>