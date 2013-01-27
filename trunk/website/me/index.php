<?php
include_once('../inc/header.php');

$q = $__database->query("
SELECT 
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
	acc.username = '".$__database->real_escape_string($__url_userdata['username'])."' 
ORDER BY 
	chr.world_id ASC,
	chr.level DESC");

// printing table rows

$cache = array();

while ($row = $q->fetch_row()) {
	$cache[] = $row;
}
$q->free();
?>

	<div id="profile" class="row">
		<div id="header" class="span4" style="background: url('//<?php echo $domain; ?>/inc/img/back_panel.png') repeat top center">
        	<div id="meta-nav">
            	<div class="row">
                	<div class="span4">
                    	<ul id="nav-left" class="span4" style="margin-left:40px;">
                        	<li><a id="posts" href="#"><span class="sprite icon post"></span><span class="count">18</span> <span class="item">Characters</span></a></li>
                            <li><a id="likes" href="#"><span class="sprite icon badgestar"></span><span class="count">3</span> <span class="item">Achievements</span></a></li>
                            </ul>
                            
                        <ul id="nav-right" style="display:none;">
                            	<li><a id="followers" href="#"><span class="sprite icon follower"></span><span class="item">Followers</span><span class="count">4</span></a></li>
                                <li><a id="following" href="#"><span class="sprite icon following"></span><span class="item">Following</span><span class="count">1</span></a></li>
                                <li><a id="badges" href="#"><span class="sprite icon badgestar"></span><span class="item">Badges</span><span class="count">4</span></a></li>
                        </ul>
                   </div>
              </div>
          </div>
          
          <div id="profile-user-details">
          	 <div class="row">
            	<div class="span4">
                	<div id="user-about" class="center">
                    	<h2><?php echo $__url_userdata['full_name']; ?> <span class="muted">(<?php echo $__url_userdata['nickname']; ?>)</span></h2>
                        
                        <ul id="user-external">
                        	<li><span style="color: rgb(255, 255, 255); text-shadow: rgb(102, 102, 102) 1px 0px 3px;">
                        	<img src="//<?php echo $domain; ?>/inc/img/icons/comment.png" style="position: relative;
top: 4px;"/> <?php echo $__url_userdata['bio']; ?></span></li>
                            
                        </ul>
                   </div>
               </div>
           </div>
         </div>
	
	<!-- Character Display -->
   <div class="row">
	<div class="span8">

<?php


if (count($cache) == 0) {
?>
	
	</div>
	</div>

<?php include_once('../inc/footer.php'); ?>
<p class="lead alert-error alert"><?php echo $__url_userdata['username']; ?> hasn't added any characters yet!</p>

<?php
}

	
$last_world = NULL;
$i = 0;
foreach ($cache as $row) {
	if ($last_world != $row[2]) {
		if ($last_world != NULL) {
			for ($i %= 5; $i < 5; $i++) {
?>
				<td width="200px">&nbsp;</td>
<?php
			}
			$i = 0;
?>
			</tr>
		</table>
	</div>
</fieldset>
<?php
		}
?>
<fieldset>
	<legend><button class="btn" data-toggle="collapse" data-target="#<?php echo $row[2]; ?>" href="#<?php echo $row[2]; ?>"><?php echo $row[2]; ?></button></legend>
	<div id="<?php echo $row[2]; ?>" class="collapse accordion-body">
		<table width="100%">
			<tr>
<?php
		$last_world = $row[2];
	}
	if ($i != 0 && $i % 5 == 0) {
?>
			</tr>
			<tr>
<?php
	}
?>
				<td width="200px">
					<center><img src="//<?php echo $domain; ?>/avatar/<?php echo $row[1]; ?>" class="img-polaroid" /></center>
					<br />
					<center><?php echo $row[1]; ?></center>
				</td>
<?php
	$i++;
}

for ($i %= 5; $i < 5; $i++) {
?>
				<td width="200px">&nbsp;</td>
<?php
}
?>
			</tr>
		</table>
	</div>
    </div>
</fieldset>