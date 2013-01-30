<?php include_once('inc/header.php'); ?>

<?php
if (!$_loggedin):
?>
      <div class="jumbotron">
      	<h1 style="font-size:60px;font-weight:200;" class="span12">What is Mapler.me?</h1>
        <br/><br/>
			<div class="row">
			<div class="span4"><img src="https://dl.dropbox.com/u/22875564/mapler.me.resources/front1.gif" class="pull-right"/><h1>Are you ready to become a better mapler?</h1>
				<p class="lead">Join hundreds of other maplers and keep track of your progress in-game, as well as socialize with your buddies in-game!</p>
				</div>
			<div class="span4"><img src="https://dl.dropbox.com/u/22875564/mapler.me.resources/front3.gif" class="pull-right"/><h1>Keep track of your own Maple 'Story'!</h1>
				<p class="lead">Mapler.me records everything from your experience, stats, and even past looks!<br/>Never forget a past look again!</p>
			 </div>
			<div class="span4"><img src="https://dl.dropbox.com/u/22875564/mapler.me.resources/front2.gif" class="pull-right"/><h1>Safe, fast, and secure.<br/>It just <a href="//<?php echo $domain; ?>/intro/">works.</a></h1>
				<p class="lead">Mapler.me keeps all player information confidential and secure!<br/>
				</p>
			</div>
       </div>

	<a class="btn btn-small" href="/register" role="button" style="display:none;">What are you waiting for? Sign up!</a>
	
<?php
else:
?>
<p class="lead">
<?php

	$q = $__database->query("SELECT 
		chr.id, 
		chr.name, 
		w.world_name 
	FROM 
		characters chr 
	LEFT JOIN 
		users usr 
		ON 
			usr.ID = chr.userid 
	LEFT JOIN 
		accounts acc 
		ON 
			acc.id = usr.account_id 
	LEFT JOIN 
		world_data w 
		ON 
			w.world_id = chr.world_id 

	WHERE 
		acc.username = '".$__database->real_escape_string($_logindata['username'])."' 
	ORDER BY 
		chr.world_id ASC,
		chr.level DESC
	LIMIT 0, 1");

	// printing table rows
	while ($row = $q->fetch_row()) {
?>
<img src="//<?php echo $domain; ?>/avatar/<?php echo $row['1'];?>" class="img-polaroid"/>
<?php
	}
?>
<?php echo $_logindata['full_name']; ?>'s Stream – <b>Work In Progress</b>

</p>
<?php
endif;
?>
<?php include_once('inc/footer.php'); ?>