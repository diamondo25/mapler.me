<?php require_once __DIR__.'/inc/header.php'; ?>

<?php
if (!$_loggedin):
DisplayError('nopermission');
?>
<p>Mapler.me offers an extensive <b>{JSON}</b> API for developers to create applications crafted by Nexon America!</p>

<?php
else:
?>
<style>

.title {
	font-weight: bold;
	font-family: Helvetica, sans-serif;
	font-size: 24px;
	letter-spacing: 0px;
	color: #777;
}

.more {
	font-weight: 200;
	letter-spacing: normal;
	color: #999;
	font-size: 15px;
}

</style>
<div class="row">
	<div class="span3" style="height:100% !important; float: left;">
		<p class="title">Mapler.me {API}<br/>
			<small class="more" style="margin-top:10px;">
			<?php echo $_loginaccount->GetFullName(); ?> - <?php echo $_loginaccount->GetAccountRank(); ?>
			</small>
		</p>
	</div>
</div>
<?php
endif;
?>
      
<?php require_once __DIR__.'/inc/footer.php'; ?>