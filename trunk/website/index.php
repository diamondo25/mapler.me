<?php 
require_once __DIR__.'/inc/header.php';

if (!$_loggedin):
?>	
      <div class="jumbotron">
      
      <div class="row">
		  <div class="span12">
            <h1>Mapler.me is a MapleStory community and service providing innovative features to enhance your gaming experience!</h1>
			<h2>Real-time avatars, character progress, and more is just a click away..</h2><br/>
			<p><a href="/signup/" class="btn btn-primary btn-action">Sign up now! (Beta Testers)</a></p> 
		  </div>
		</div>	  		  
		</div>
	
<?php
else:
?>

<meta http-equiv="refresh" content="0;URL='/stream/'" />

<?php
endif;

require_once __DIR__.'/inc/footer.php';

?>