<?php
require_once __DIR__.'/functions.php';

if (!IsLoggedin() || !isset($_GET['page'], $_GET['type'])) {
	header('Location: http://'.$domain.'/');
}

$page = '../manage/'.($_GET['type'] == '' ? '' : stripslashes($_GET['type']).'/').stripslashes($_GET['page']).'.php';
if (!file_exists($page)) {
	header('Location: http://'.$domain.'/');
	die();
}

require_once __DIR__.'/header.template.php';
if ($_GET['type'] == '') {
	require_once __DIR__.'/additional.menu.php';
}

// SHOO
if (!$_loggedin || $_loginaccount->GetAccountRank() < RANK_ADMIN) {
	header('Location: /');
	die();
}
?>
		<div class="row">
			<div class="span12">
				<p class="lead">Mapler.me Administrative Panel :: <?php echo $_loginaccount->GetFullName(); ?> (id: <?php echo $_loginaccount->GetID(); ?>)</p>
				<?php require_once $page; ?>
			</div>
		</div>
<?php 
require_once __DIR__.'/footer.php';
?>