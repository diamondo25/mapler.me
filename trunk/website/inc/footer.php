		<div style="clear:both;"></div>
		<footer>
			<p>
				<span style="color:#333;">&copy; 2012-2013 Mapler.me</span> – <?php echo $__database->QueriesRan(); ?> queries ran.
			</p>
			<p><a href="//<?php echo $domain; ?>/terms/">Terms of Service</a> - <a href="//<?php echo $domain; ?>/support/">Request Support</a></p>
		</footer>

<?php if (!$_loggedin && $_SERVER['REQUEST_URI'] !== '/logoff') {
?>
		<div id="plzjoin" style="display:none;">
			<div class="row">
				<div class="span8">
					<div class="row">
						<div class="span6">
							<div class="join">
                            Mapler.me is a social network, service, and community dedicated to MapleStory players!
                            </div>
                        </div>
                        
                        <div class="span2" style="position:relative;bottom:5px;">
                        	<a href="/signup/" id="join-btn" class="btn btn-large" style="margin-top: 14px">Join now!</a>
                        </div>
                        	
                    </div>
               </div>

               <div class="span4" style="border-left:1px solid #CFC7BE">
               		<div style="float:left;width: 200px">
               		<iframe src="//www.facebook.com/plugins/likebox.php?href=http%3A%2F%2Fwww.facebook.com%2Fmaplerme&amp;width=250&amp;height=62&amp;show_faces=false&amp;colorscheme=light&amp;stream=false&amp;border_color&amp;header=false&amp;appId=256278267725729" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:250px; height:62px;" allowtransparency="true"></iframe>
               		</div>
               		
               		<div id="twitter">
               			<iframe allowtransparency="true" frameborder="0" scrolling="no" src="http://platform.twitter.com/widgets/follow_button.1363148939.html#_=1363508178252&amp;id=twitter-widget-1&amp;lang=en&amp;screen_name=maplerme&amp;show_count=true&amp;show_screen_name=false&amp;size=m" class="twitter-follow-button twitter-follow-button" style="width: 159px; height: 20px;" title="Twitter Follow Button" data-twttr-rendered="true"></iframe> <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
               		</div>
         
                 </div>
             </div>
         </div>
	</div>
<?php
}
?>

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

