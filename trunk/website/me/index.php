<?php
require_once '../inc/header.php';

$char_config = $__url_useraccount->GetConfigurationOption('character_config', array('characters' => array(), 'main_character' => null));

$q = $__database->query("
SELECT
	*,
	TIMESTAMPDIFF(SECOND, timestamp, NOW()) AS `secs_since`
FROM
	social_statuses
WHERE 
	account_id = '".$__database->real_escape_string($__url_useraccount->GetID())."' 
ORDER BY
	secs_since ASC
");

$fixugh = '0';
	
$cache = array();
while ($row = $q->fetch_assoc()) {
	if (isset($fixugh)) {
		if ($fixugh == 2) { // Always hide... :)
			continue;
		}
	}
	$cache[] = $row;
}

$q->free();

function time_elapsed_string($etime) {
   if ($etime < 1) {
       return '0 seconds';
   }
   
   $a = array( 12 * 30 * 24 * 60 * 60  =>  'year',
               30 * 24 * 60 * 60       =>  'month',
               24 * 60 * 60            =>  'day',
               60 * 60                 =>  'hour',
               60                      =>  'minute',
               1                       =>  'second'
               );
   
   foreach ($a as $secs => $str) {
       $d = $etime / $secs;
       if ($d >= 1) {
           $r = round($d);
           return $r . ' ' . $str . ($r > 1 ? 's' : '');
       }
   }
}

?>
	<div id="profile" class="row">
		<div id="header" class="span12" style="background: url('//<?php echo $domain; ?>/inc/img/back_panel.png') repeat top center">
        	<div id="meta-nav">
            	<div class="row">
                	<div class="span12">
                    	<ul id="nav-left">
                        	<li><a id="posts" href="//<?php echo $subdomain.".".$domain; ?>/characters"><span class="sprite icon post"></span><span class="count">
                        	
                        	<?php
                        	$x = $__database->query("
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
	world_data w 
	ON 
		w.world_id = chr.world_id 
WHERE 
	usr.account_id = '".$__database->real_escape_string($__url_useraccount->GetID())."' 
ORDER BY 
	chr.world_id ASC,
	chr.level DESC
");

$caches = array();

$selected_main_character = $char_config['main_character'];
$character_display_options = $char_config['characters'];

while ($row = $x->fetch_assoc()) {
	if (isset($character_display_options[$row['name']])) {
		if ($character_display_options[$row['name']] == 2) { // Always hide... :)
			continue;
		}
	}
	$caches[] = $row;
}
$x->free();

$has_characters = count($caches) != 0;
$main_character_info = $has_characters ? $caches[0] : null;
$main_character_name = $has_characters ? ($selected_main_character != null ? $selected_main_character : $main_character_info['name']) : '';
$main_character_image = $has_characters ? '//'.$domain.'/avatar/'.$main_character_name : '';
                        	?>
                        	
                        	<?php echo count($caches); ?></span> <span class="item">Characters</span></a></li>
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
                    	<h2><?php echo $__url_useraccount->GetNickname(); ?></h2>
<?php if ($__url_useraccount->GetBio() != null): ?>
                        <ul id="user-external">
                        	<li><span style="color: rgb(255, 255, 255); text-shadow: rgb(102, 102, 102) 1px 0px 3px;">
                        	<img src="//<?php echo $domain; ?>/inc/img/icons/comment.png" style="position: relative; top: 4px;"/> <?php echo $__url_useraccount->GetBio(); ?></span></li>
                            
                        </ul>
<?php endif; ?>
                   </div>
               </div>
           </div>
           
           <div class="row">
           		<div class="span2 offset5 center" style="margin-bottom: -70px;">
                	<a href="//<?php echo $domain; ?>/player/<?php echo $main_character_name; ?>"><img id="default_character" src="<?php echo $main_character_image; ?>" alt="<?php echo $main_character_name; ?>" style="display:inline-block;background: rgb(255, 255, 255);
border-radius: 150px;
margin-bottom: -30px;
box-sizing: border-box;
-webkit-box-shadow: rgba(0, 0, 0, 0.298039) 0px 0px 4px 0px;
box-shadow: rgba(0, 0, 0, 0.298039) 0px 0px 4px 0px;
border: 8px solid rgb(255, 255, 255);
border-image: initial;
text-align: center;" /> </a>
				</div>
			</div>
		</div>
	</div>
</div>
	
	<!-- Character Display -->
	<div id="character-wall">

<?php
if (count($cache) == 0) {
?>
		<p class="lead alert-error alert"><?php echo $__url_useraccount->GetNickName(); ?> hasn't posted anything!</p>
<?php
}
?>
		</div>
		<div class="row">
	<?php

// printing table rows

foreach ($cache as $row) {

?>
			<div class="status span4">
			<div class="header">
			<?php
			echo $row['nickname'];
			$badgepls = $row['account_id'];
			?> said: <span class="pull-right">		
				<?php echo time_elapsed_string($row['secs_since']); ?> ago
			</span></div>
				<br/><img src="http://mapler.me/avatar/<?php echo $row['character']; ?>" class="pull-right"/>
					<?php echo $row['content']; ?>
			</div>
        
<?php       
}
?>

	</div>

<?php require_once '../inc/footer.php'; ?>