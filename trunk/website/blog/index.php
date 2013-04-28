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

<div class="span4">
	<div class="status">
		<center>
			<h2>Our Friends</h2>
		</center>
	</div>
	
		<div class="status">
		<center>
			<h2><img src="//cdn.mapler.me/etc/leafre.png"/></h2>
			Leafre.net is a community dedicated on KMS (Korean MapleStory) owned by <a href="http://kremechoco.mapler.me/" target="_blank">KremeChoco</a> that offers maplers the ability to play with other korean maplers. <a href="http://leafre.net/" target="_blank">Check them out!</a>
		</center>
	</div>
</div>

<script>
$(document).ready(function() { GetBlogPosts(true, true); });
</script>
<?php
require_once __DIR__.'/../inc/footer.php';
?>