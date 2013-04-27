<?php
require_once __DIR__.'/functions.php';

if (!IsLoggedin() || $_loginaccount->GetAccountRank() < RANK_ADMIN || !isset($_GET['page'])) {
	header('Location: http://'.$domain.'/');
	die();
}

$page = stripslashes($_GET['page']);
$page = str_replace('/', '', $page);

$page = '../manage/'.$page.'.php';
if (!file_exists($page)) {
	header('Location: http://'.$domain.'/');
	die();
}

require_once __DIR__.'/templates/header.template.php';
require_once __DIR__.'/templates/additional.menu.php';
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