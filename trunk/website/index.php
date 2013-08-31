<?php
require_once __DIR__.'/inc/header.php';

$s = $__database->query("
SELECT
	(SELECT COUNT(*) FROM accounts),
	(SELECT COUNT(*) FROM characters),
	(SELECT COUNT(*) FROM items)
");
$tmp = $s->fetch_row(); 
$s->free();

$statusses = new Statusses();
$statusses->Load("s.blog = 0","5");

if ($_loggedin) {
?>
<meta http-equiv="refresh" content="0;URL='/stream/'" />
<?php
	die;
}
?>
<style>
.woah {
	font-size:40px;
	line-height: 40px;
	margin-top: 20px;
}

.container {
    background: transparent;
}

</style>
<div class="row">
	<div class="span12 status">
		<center>
		<p class="lead woah">Start the next chapter of your Maple 'Story'!</p>
				<p class="lead">Mapler.me is a brand new social network dedicated to MapleStory that allows you to stay connected with your buddies, keep track of your characters' growth, and much more.</p>
		<br />
		<a href="//<?php echo $domain; ?>/login/" class="btn btn-info btn-large">Login or register!</a>
		</center>
		<br/><br/>
	</div>
</div>
</div>
<div class="container">
<div class="row">
	<div class="span5 hidemobile">
	    <div class="status">
    		<p class="lead">Ever feel disconnected from your friends, guild, or alliance whenever you're not on MapleStory? With Mapler.me, you can stay connected with <strong>over <?php echo $tmp[0]; ?> maplers</strong> already apart of Mapler.me.</p>
    		<p class="lead">Have a question? Check out our <a href="//<?php echo $domain; ?>/faq/">FAQ</a>.</p>
    		<p>
    			Stay up to date on the latest news by following us on <a href="http://twitter.com/maplerme">Twitter</a>, or like us on <a href="http://facebook.com/MaplerMe">Facebook</a>!<br />
    			<div class="fb-like" data-href="http://facebook.com/MaplerMe" data-send="false" data-layout="button_count" data-width="450" data-show-faces="true"></div> <a href="https://twitter.com/maplerme" class="twitter-follow-button" data-show-count="false">Follow @maplerme</a>
    			<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
    		</p>
	    </div>

<?php
	
	$serverinfo = GetMaplerServerInfo();
	
	foreach ($serverinfo as $servername => $data) {
?>
		<p mapler-locale="<?php echo $servername; ?>" class="lead alert alert-info">
			<span class="online-server"<?php echo ($data['state'] !== 'online' ? ' style="display: none;"' : ''); ?>><span><strong>MapleStory <?php echo $data['locale']; ?></strong> – </span></span> <span><span players><?php echo $data['players']; ?></span> maplers currently updating their character(s) in real-time.</span></span>
			<span class="offline-server"<?php echo ($data['state'] !== 'offline' ? ' style="display: none;"' : ''); ?>>Mapler.me server '<?php echo $servername; ?>' is offline...</span>
		</p>
<?php
	}
?>

	    
	    <div class="status">
	        <h1>Statistics</h1>
	        <p class="lead"><strong><?php echo $tmp[0]; ?></strong> maplers have joined Mapler.me.</p>
	        <p class="lead"><strong><?php echo $tmp[1]; ?></strong> characters have been added to Mapler.me and updated in real-time.</p>
	        <p class="lead"><strong><?php echo $tmp[2]; ?></strong> items stored between all characters, wow!</p>
	    </div>
	</div>
	
	<div class="span7 hidemobile">
<?php
	foreach ($statusses->data as $status) {
		$status->PrintAsHTML();
	}
?>
	</div>

</div>

		<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js" type="text/javascript"></script>
		<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js" type="text/javascript"></script>
<?php
_AddHeaderLink('js', 'scripts');
if (strpos($_SERVER['REQUEST_URI'], '/player/') !== FALSE) {
	_AddHeaderLink('js', 'script.player');
}
_AddHeaderLink('js', 'jquery.isotope.min');
_AddHeaderLink('js', 'maplerme.min');
_AddHeaderLink('js', 'keypress')
?>
	<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/modernizr/2.6.2/modernizr.min.js"></script>	

	<script type="text/javascript">

	  var _gaq = _gaq || [];
	  _gaq.push(['_setAccount', 'UA-36861298-1']);
	  _gaq.push(['_setDomainName', 'mapler.me']);
	  _gaq.push(['_trackPageview']);

	  (function() {
		var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	  })();

	</script>

	<div id="fb-root"></div>
	<script type="text/javascript">
	(function(d, s, id) {
	  var js, fjs = d.getElementsByTagName(s)[0];
	  if (d.getElementById(id)) return;
	  js = d.createElement(s); js.id = id;
	  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=270232299659650";
	  fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));
	</script>