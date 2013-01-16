<?php include_once('inc/header.php'); ?>

<?php
if (!$_loggedin):
?>
      <div class="jumbotron">
        <div id="myCarousel" class="carousel slide">
		  <!-- Carousel items -->
		  <div class="carousel-inner">
			<div class="active item"><h1>Are you ready to become a better mapler?</h1>
				<p class="lead"><img src="https://dl.dropbox.com/u/22875564/mapler.me.resources/front1.gif" class="pull-right"/>Join hundreds of other maplers and keep track of your progress in-game, as well as socialize with your buddies in-game!</p>
				</div>
			<div class="item"><h1>Keep track of your own Maple 'Story'!</h1>
				<p class="lead"><img src="https://dl.dropbox.com/u/22875564/mapler.me.resources/front3.gif" class="pull-left"/>Mapler.me records everything from your experience, stats, and even past looks!<br/>Never forget a past look again!</p>
			 </div>
			<div class="item"><h1>Safe, fast, and secure.<br/>It just <a href="//<?php echo $domain; ?>/intro/">works.</a></h1>
				<p class="lead"><img src="https://dl.dropbox.com/u/22875564/mapler.me.resources/front2.gif" class="pull-right"/>Mapler.me keeps all player information confidential and secure!<br/>
					We are partnered with Nexon America, and is built with perfection.
				</p></div>
		  </div>
		  <!-- Carousel nav -->
		  <a class="carousel-control left" href="#myCarousel" data-slide="prev">&lsaquo;</a>
		  <a class="carousel-control right" href="#myCarousel" data-slide="next">&rsaquo;</a>
		</div>

	<a class="btn btn-small" href="/register" role="button" style="display:none;">What are you waiting for? Sign up!</a>
	
<?php
else:
?>

<?php
endif;
?>
      </div>

<?php include_once('inc/footer.php'); ?>
