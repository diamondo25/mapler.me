		</div>
		<div style="clear:both;"></div>
		<footer>
			<p><span><i class="icon-asterisk"></i> 2012-<?php echo date('Y'); ?> Mapler.me</span></p>
			<p><a href="//status.mapler.me">Status</a> - <a href="//<?php echo $domain; ?>/faq/">FAQ</a> - <a href="//<?php echo $domain; ?>/contributions/">Contributions</a> - <a href="//<?php echo $domain; ?>/support/">Support</a></p>
			<br />
			
			<?php
				//Displays queries ran / APC caching status for admins only.
				if ($_loggedin && $_loginaccount->GetAccountRank() == RANK_ADMIN) {
			?>
			
			<p class="faded"><?php echo $__database->QueriesRan(); ?> queries ran - <?php echo function_exists('apc_fetch') ? 'Using APC caching' : 'NOT using APC caching'; ?></p>
			
			<?php
			}
			?>
		</footer>
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

</body>
</html>

