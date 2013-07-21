<?php
require_once __DIR__.'/../inc/header.php';

if ($_loggedin && !$__url_useraccount) {
	echo '<META HTTP-EQUIV="Refresh" Content="0; URL=http://'.$_loginaccount->GetUsername().'.'.$domain.'/">';
	die;
}

require_once __DIR__.'/../inc/templates/me.header.template.php';
?>


<?php if ($__url_useraccount->GetBio() != null): ?>
	<div class="status span9 noclear">
		<p class="lead nomargin"><i class="icon-quote-left"></i> <?php echo $__url_useraccount->GetBio(); ?> <i class="icon-quote-right"></i></p>
	</div>
	
<?php
endif;
?>
	<div class="span9" id="statuslist"></div>

<p>
	<center><button onclick="syncer(true, true);" class="btn btn-large" type="button" id="syncbutton">Load more statuses..</button></center>
</p>
<script>
$(document).ready(function() { 
	$(window).scroll(function() {
		var offsetTillBottom = $(document).height() - ($(window).scrollTop() + $(window).height());
		if (offsetTillBottom <= 100) {
			syncer(true, true);
		}
	});
});
</script>
	
</div>
<?php require_once __DIR__.'/../inc/footer.php'; ?>