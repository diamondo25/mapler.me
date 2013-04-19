<?php
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

$y = $__database->query("
SELECT
	a.*
FROM
(
	(
		SELECT
			friend.account_id AS `account_id`,
			friend.added_on,
			friend.accepted_on,
			TIMESTAMPDIFF(SECOND, friend.added_on, NOW()) AS `added_on_secs`,
			TIMESTAMPDIFF(SECOND, friend.accepted_on, NOW()) AS `accepted_on_secs`,
			0 AS `added_by_yourself`
		FROM
			friend_list friend
		WHERE
			friend.friend_id = ".$__url_useraccount->GetId()."
	)
	UNION
	(
		SELECT
			friend.friend_id AS `account_id`,
			friend.added_on,
			friend.accepted_on,
			TIMESTAMPDIFF(SECOND, friend.added_on, NOW()) AS `added_on_secs`,
			TIMESTAMPDIFF(SECOND, friend.accepted_on, NOW()) AS `accepted_on_secs`,
			1 AS `added_by_yourself`
		FROM
			friend_list friend
		WHERE
			friend.account_id = ".$__url_useraccount->GetId()."
	)
) a

ORDER BY
	a.accepted_on DESC
");

$cachey = array();
while ($rowy = $y->fetch_assoc()) {
	$cachey[] = $rowy;
}

$y->free();

$z = $__database->query("
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

$cachez = array();
while ($rowz = $z->fetch_assoc()) {
	$cachez[] = $rowz;
}

$has_characters = count($cache) != 0;
$main_character_info = $has_characters ? $cache[0] : null;
$main_character_name = $has_characters ? ($selected_main_character != null ? $selected_main_character : $main_character_info['name']) : '';
$main_character_image = $has_characters ? '//'.$domain.'/avatar/'.$main_character_name : '';

$rank = $__url_useraccount->GetAccountRank();

if ($_loggedin) {

	if ($__url_useraccount->GetID() == $_loginaccount->GetID()) {
		$is_self = true;
	}
	else {
		$is_self = false;
		$friend_status = GetFriendStatus($_loginaccount->GetID(), $__url_useraccount->GetID());
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
		<p class="name"><?php echo $__url_useraccount->GetNickname(); ?><br />
			<small class="name_extra" style="margin-top:10px;">	
			<?php if ($__url_useraccount->GetBio() != null): ?>
				<?php echo $__url_useraccount->GetBio(); ?>
			<?php endif; ?>
			</small>
		</p>
		<p class="rank"><?php echo GetRankTitle($rank); ?></p>
		<hr/>
		<p class="name_extra">last seen <?php echo time_elapsed_string($__url_useraccount->GetLastLoginSeconds()); ?> ago...<br/></p>
		<hr/>
<?php
if ($_loggedin && !$is_self) {
	if ($friend_status == 'FRIENDS') {
?>

		<p class="name_extra">You are already friends! <button class="btn btn-mini btn-danger" onclick="RemoveFriend('<?php echo $__url_useraccount->GetUsername(); ?>')">Remove?</button></p>
		<hr/>
<?php
	}
	elseif ($friend_status == 'NO_FRIENDS') {
?>

		<p class="name_extra"><button class="btn btn-mini btn-success" onclick="InviteFriend('<?php echo $__url_useraccount->GetUsername(); ?>')">Add as a friend?</button></p>
		<hr/>
<?php
	}
	elseif ($friend_status == 'NOT_ACCEPTED_YOU') {
?>

		<p class="name_extra"><?php echo $__url_useraccount->GetNickname(); ?> is still waiting for your friend approval. <button class="btn btn-mini btn-success" onclick="AcceptFriend('<?php echo $__url_useraccount->GetUsername(); ?>')">Accept?</button></p>
		<hr/>
<?php
	}
	elseif ($friend_status == 'NOT_ACCEPTED_FRIEND') {
?>

		<p class="name_extra">You are still waiting for <?php echo $__url_useraccount->GetNickname(); ?>'s friend approval. <button class="btn btn-mini btn-danger" onclick="RemoveFriend('<?php echo $__url_useraccount->GetUsername(); ?>')">Cancel?</button></p>
		<hr/>
<?php
	}
}

if (count($cache) > 0) {
?>
		<p class="side"><i class="icon-comment faded"></i> <a href="//<?php echo $subdomain.".".$domain; ?>/friends" style="color:gray;"><?php echo count($cachez); ?> Statuses</a></p>
		<p class="side"><i class="icon-book faded"></i> <a href="//<?php echo $subdomain.".".$domain; ?>/characters" style="color:gray;"><?php echo count($cache); ?> Characters</a></p>
		<p class="side"><i class="icon-user faded"></i> <a href="//<?php echo $subdomain.".".$domain; ?>/friends" style="color:gray;"><?php echo count($cachey); ?> Friends</a></p>
<?php
}
?>
	</div>
