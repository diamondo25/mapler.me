<?php include_once('../inc/header.php'); ?>

	<div class="row">
	<div class="span2">
	      <img src="http://msavatar1.nexon.net/Character/JAOLKKPKABLFHGLCGKHLEKMOBDJBKGEJOHFLABIFNENAOOFNHDBPHCDIOKEPKFKNDCCEBHGNHHAPKJDKMFECCHAPIBDBLBGE.gif" class="img-polaroid">
	</div>
	<div class="span10">
	<p class="lead"><?php echo $_logindata['full_name']; ?> <span class="muted">(<?php echo $_logindata['nickname']; ?>)</span></p>
	<?php echo $_logindata['bio']; ?>
	</div>
	</div>

<?php include_once('../inc/footer.php'); ?>