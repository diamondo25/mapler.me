<?php require_once 'inc/header.php'; ?>

<?php
if (!$_loggedin):
?>
      <div class="jumbotron">
      
      <div class="row">
		  <div class="span12">
		  	<img src="//<?php echo $domain; ?>/inc/img/tempwelcome.png"/>
            <h1>Mapler.me is a MapleStory community and service providing innovative features to enhance your gaming experience!</h1>
			<h2>Real-time avatars, character progress, and more is just a click away..</h2><br/>
			<p><a href="#" class="btn btn-primary btn-action">Beta testing is coming soon!</a></p> 
		  </div>
		</div>	  		  
		</div>
		
			<div class="row" style="display:none;">
			<div class="span4"><h1>Are you ready to become a better mapler?</h1>
				<p class="lead"></p>
				</div>
			<div class="span4"><img src="https://dl.dropbox.com/u/22875564/mapler.me.resources/front3.gif" class="pull-right"/><h1>Keep track of your own Maple 'Story'!</h1>
				<p class="lead">Mapler.me records everything from your experience, stats, and even past looks!<br/>Never forget a past look again!</p>
			 </div>
			<div class="span4"><img src="https://dl.dropbox.com/u/22875564/mapler.me.resources/front2.gif" class="pull-right"/><h1>Safe, fast, and secure.<br/>It just <a href="//<?php echo $domain; ?>/intro/">works.</a></h1>
				<p class="lead">Mapler.me keeps all player information confidential and secure!<br/>
				</p>
			</div>
       </div>
	
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
		acc.username = '".$__database->real_escape_string($_loginaccount->GetUsername())."' 
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
<?php echo $_loginaccount->GetUsername(); ?>'s Stream – <b>Work In Progress</b>

</p>
<?php
endif;
?>
<?php require_once 'inc/footer.php'; ?>