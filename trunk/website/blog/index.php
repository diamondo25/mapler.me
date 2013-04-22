<?php
require_once __DIR__.'/../inc/header.php';
?>

<center>
	<p style="font-size:40px;"><img src="//<?php echo $domain; ?>/inc/img/icon.png" style="width:50px;position:relative;top:10px;"/>mapler.news</p>
	<p>Mapler.me's official staff blog!</p>
</center>
<hr />
<div class="row">
<div class="span8" id="statuslist"></div>

<div class="span3">
-place sidebar here-
</div>
</div>

<script>
$(document).ready(function() { GetBlogPosts(true, true); });
</script>
<?php
require_once __DIR__.'/../inc/footer.php';
?>