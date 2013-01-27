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
		<div id="header" class="span12" style="background: url('//<?php echo $domain; ?>/inc/img/back_panel.png') repeat top center">
        	<div id="meta-nav">
            	<div class="row">
                	<div class="span12">
                    	<ul id="nav-left">
                        	<li><a id="posts" href="#"><span class="sprite icon post"></span><span class="count">18</span> <span class="item">Characters</span></a></li>
                            <li><a id="likes" href="#"><span class="sprite icon badgestar"></span><span class="count">3</span> <span class="item">Achievements</span></a></li>
                            </ul>
                            
                        <ul id="nav-right">
                        </ul>
                   </div>
              </div>
          </div>
          
          <div id="profile-user-details">
          	 <div class="row">
            	<div class="span6 offset3" style="margin-bottom:70px;">
                	<div id="user-about" class="center">
                    	<h2><?php echo $__url_userdata['nickname']; ?></h2>
                        
                        <ul id="user-external">
                        	<li><span style="color: rgb(255, 255, 255); text-shadow: rgb(102, 102, 102) 1px 0px 3px;">
                        	<img src="//<?php echo $domain; ?>/inc/img/icons/comment.png" style="position: relative;
top: 4px;"/> <?php echo $__url_userdata['bio']; ?></span></li>
                            
                        </ul>
                   </div>
               </div>
           </div>
           
           <div class="row">
           		<div class="span2 offset5 center" style="margin-bottom: -70px;">
                	<a href=""> <img id="default" src="<?php
if (count($cache) > 0) {
?>
//mapler.me/avatar/<?php echo $cache[0][1]; ?>
<?php
}
?>" alt="<?php echo $cache[0][1]; ?>" style="display:inline-block;background: rgb(255, 255, 255);
border-radius: 150px;
margin-bottom: -30px;
box-sizing: border-box;
-webkit-box-shadow: rgba(0, 0, 0, 0.298039) 0px 0px 4px 0px;
box-shadow: rgba(0, 0, 0, 0.298039) 0px 0px 4px 0px;
border: 8px solid rgb(255, 255, 255);
border-image: initial;
text-align: center;"> </a>
</div> </div> </div> </div> </div>
	
	<!-- Character Display -->
	<div class="span12">

<?php


if (count($cache) == 0) {
?>
<p class="lead alert-error alert"><?php echo $__url_userdata['nickname']; ?> hasn't added any characters yet!</p>

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
</fieldset>
	
	</div>
	</div>

<?php include_once('../inc/footer.php'); ?>