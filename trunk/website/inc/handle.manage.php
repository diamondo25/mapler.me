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
//gosh dang it
if (isset($_GET['ajax'])) {
	require_once $page;
}
if (isset($_GET['strings'])) {
	require_once $page;
}
if (isset($_GET['accounts'])) {
	require_once $page;
}
if (isset($_GET['characters'])) {
	require_once $page;
}
else {
	require_once __DIR__.'/templates/header.template.php';
?>
		<div class="row">
			<div class="span3">
				<ul class="nav nav-list sidebar">
					<?php require_once __DIR__.'/templates/additional.menu.php'; ?>
          		</ul>
        	</div>
			<div class="span9">
				<p class="lead">Mapler.me Administrative Panel :: <?php echo $_loginaccount->GetFullName(); ?> (id: <?php echo $_loginaccount->GetID(); ?>)</p>
				<?php require_once $page; ?>
			</div>
		</div>
</div>
<?php 
	require_once __DIR__.'/footer.php';
}
?>