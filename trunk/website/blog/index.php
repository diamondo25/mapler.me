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
			<h2>Player Spotlight</h2>
			<img src="//mapler.me/ignavatar/Marissaurus"/><br/>
			(<a href="//maryse.mapler.me">Maryse</a>)<hr />
			Maryse, one of our Mapler+ members, joined Mapler.me early on during it's private beta. Since the very beginning she helped give suggestions for additions, helped discover and tackle some early bugs with our client, as well as being an active member.
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