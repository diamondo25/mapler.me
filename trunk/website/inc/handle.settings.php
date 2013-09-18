<?php
require_once __DIR__.'/functions.php';

if (!IsLoggedin() || !isset($_GET['page'])) {
	header('Location: http://'.$domain.'/');
	die();
}

$page = stripslashes($_GET['page']);
$page = str_replace('/', '', $page);

$page = '../settings/'.$page.'.php';
if (!file_exists($page)) {
	header('Location: http://'.$domain.'/');
	die();
}

require_once __DIR__.'/templates/header.template.php';
?>
		<div class="row">
			<div class="span3">
				<ul class="nav nav-list sidebar">
					<?php require_once __DIR__.'/templates/additional.menu.php'; ?>
          		</ul>
          	<br />
        	</div>
		
			<div class="span9">
<?php require_once $page; ?>
			</div>
		</div>
</div>
<?php 
require_once __DIR__.'/footer.php';
?>