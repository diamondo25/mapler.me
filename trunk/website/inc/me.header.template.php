<?php
$q = $__database->query("
SELECT
	accounts.*,
	TIMESTAMPDIFF(SECOND, last_login, NOW()) AS `secs_since`
FROM
	accounts
WHERE
	accounts.id = ".$__url_useraccount->GetID()."
ORDER BY
	last_login ASC
");

$char_config = $__url_useraccount->GetConfigurationOption('character_config', array('characters' => array(), 'main_character' => null));


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

$cache = array();

$selected_main_character = $char_config['main_character'];
$character_display_options = $char_config['characters'];

while ($row = $x->fetch_assoc()) {
	if (isset($character_display_options[$row['name']])) {
		if ($character_display_options[$row['name']] == 2) { // Always hide... :)
			continue;
		}
	}
	$cache[] = $row;
}
$x->free();

$has_characters = count($cache) != 0;
$main_character_info = $has_characters ? $cache[0] : null;
$main_character_name = $has_characters ? ($selected_main_character != null ? $selected_main_character : $main_character_info['name']) : '';
$main_character_image = $has_characters ? '//'.$domain.'/avatar/'.$main_character_name : '';

$lastonline = array();
while ($row = $q->fetch_assoc()) {
	$lastonline[] = $row;
}
$q->free();

if ($_loggedin) {

	if ($__url_useraccount->GetID() == $_loginaccount->GetID()) {
		$is_self = true;
	}
	else {
		$is_self = false;
		$q = $__database->query("SELECT FriendStatus(".$__url_useraccount->GetID().", ".$_loginaccount->GetID().")");
		$row = $q->fetch_row();
		$friend_status = $row[0];
	}
}
?>

<style type="text/css">

.avatar {
	padding: 5px;
	background: #fff;
	box-shadow: 0 1px 2px rgba(0,0,0,0.15);
	border: 1px solid #ddd;
	margin-bottom: 20px;
	width: 96%;
}

.name {
	font-weight: bold;
	font-family: Helvetica, sans-serif;
	font-size: 24px;
	letter-spacing: 0px;
	color: #777;
}

.name_extra {
	font-weight: 200;
	letter-spacing: normal;
	color: #999;
	font-size: 15px;
}

hr {
	margin: 0 auto;
	border: 0;
	border-top: 1px solid #eee;
	border-bottom: 1px solid #CCC;
	width: 95%;
	margin-bottom: 15px;
}

.side {
	font-weight: 200;
	letter-spacing: normal;
	color: #999;
	font-size: 15px;
}

.rank {
	white-space: nowrap;
	background: #f7921e;
	color: #fff;
	padding: 2px 4px;
	font-size: 11px;
	-webkit-border-radius: 3px;
	-moz-border-radius: 3px;
	border-radius: 3px;
	margin-bottom: 10px;
}

</style>

<div class="row">
	<div class="span3" style="height:100% !important; float: left;">
<?php
if ($has_characters):
?>
	<a href="//<?php echo $domain; ?>/player/<?php echo $main_character_name; ?>">
		<img id="default_character" class="avatar" src="<?php echo $main_character_image; ?>" alt="<?php echo $main_character_name; ?>"/>
	</a>
<?php
endif;
?>
		
		<br />
		<p class="name"><?php echo $__url_useraccount->GetNickname(); ?><br/>
			<small class="name_extra" style="margin-top:10px;">	
			<?php if ($__url_useraccount->GetBio() != null): ?>
				<?php echo $__url_useraccount->GetBio(); ?>
			<?php endif; ?>
			</small>
		</p>
		<p class="rank">Member</p>
		<hr/>
<?php
foreach ($lastonline as $row) {
?>
		<p class="name_extra">last seen <?php echo time_elapsed_string($row['secs_since']); ?> ago...<br/></p>
<?php
}
unset($lastonline);
?>
		<hr/>
<?php
if ($_loggedin && !$is_self) {
	if ($friend_status == 'FRIENDS') {
?>

		<p class="name_extra">You are already friends!</p>
		<hr/>
<?php
	}
	elseif ($friend_status == 'NO_FRIENDS') {
?>

		<p class="name_extra"><a href="//<?php echo $domain; ?>/settings/friends/?invite=<?php echo $__url_useraccount->GetUsername(); ?>">Add as a friend?</a></p>
		<hr/>
<?php
	}
	elseif ($friend_status == 'NOT_ACCEPTED_YOU') {
?>

		<p class="name_extra"><?php echo $__url_useraccount->GetNickname(); ?> is still waiting for your friend approval. <a href="//<?php echo $domain; ?>/settings/friends/?acceptid=<?php echo $__url_useraccount->GetUsername(); ?>">Accept?</a></p>
		<hr/>
<?php
	}
	elseif ($friend_status == 'NOT_ACCEPTED_FRIEND') {
?>

		<p class="name_extra">You are still waiting for <?php echo $__url_useraccount->GetNickname(); ?>'s friend approval.</p>
		<hr/>
<?php
	}
}
?>
		<p class="side"><i class="icon-book faded"></i> <a href="//<?php echo $subdomain.".".$domain; ?>/characters" style="color:gray;"><?php echo count($cache); ?> Characters</a></p>
	</div>
