<?php
require_once __DIR__.'/../inc/header.php';
require_once __DIR__.'/../inc/templates/me.header.template.php';
?>

<?php if ($__url_useraccount->GetBio() != null): ?>
	<div class="status span9 noclear">
		<p class="lead nomargin"><i class="icon-quote-left"></i> <?php echo $__url_useraccount->GetBio(); ?> <i class="icon-quote-right"></i></p>
	</div>
	
<?php
endif;

/*
$statusses = new Statusses();
$statusses->Load("s.account_id = '".$__database->real_escape_string($__url_useraccount->GetID())."' AND s.blog = 0");


if ($statusses->Count() == 0) {
?>
	<center>
		<img src="//<?php echo $domain; ?>/inc/img/no-character.gif"/>
		<p><?php echo $__url_useraccount->GetNickName(); ?> hasn't posted anything yet!</p>
	</center>
<?php
}
else {
?>
	<div class="span9">
<?php
	foreach ($statusses->data as $status) {
		$status->PrintAsHTML();
	}
?>
	</div>
<?php
}
*/
?>
	<div class="span9" id="statuslist"></div>
</div>
<?php require_once __DIR__.'/../inc/footer.php'; ?>