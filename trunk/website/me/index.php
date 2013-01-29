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
                        	<li><a id="posts" href="#"><span class="sprite icon post"></span><span class="count">#</span> <span class="item">Characters</span></a></li>
                            <li><a id="likes" href="#"><span class="sprite icon badgestar"></span><span class="count">#</span> <span class="item">Achievements</span></a></li>
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
?>

<?php

mysql_connect("stats.craftnet.nl", "maplestats", "maplederp") or die(mysql_error());
mysql_select_db("maplestats") or die(mysql_error());

$result = mysql_query("SELECT 
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
	chr.level DESC")
or die(mysql_error());

 if (!isset($result)) { 
  $error = "MySQL error ".mysql_errno().": ".mysql_error()."\n<br>When executing:<br>\n$query\n<br>"; 
 } 
 $fields_num = mysql_num_fields($result);

// printing table rows
while($row = mysql_fetch_array($result))
{
    foreach($row as $cell)
?>
		<div class="span2"><center><a href="//<?php echo $domain; ?>/stats/<?php echo $row['name']; ?>" style="text-decoration:none!important;font-weight:300;color:inherit;">
        <img src="//mapler.me/avatar/<?php echo $row['name']; ?>"/>
        <p><img src="//<?php echo $domain; ?>/inc/img/worlds/<?php echo $row['world_name']; ?>.png"/>&nbsp;<?php echo $row['name']; ?></p></center></a>
        </div>
        
<?php       
}
?>

	</div>

<?php include_once('../inc/footer.php'); ?>