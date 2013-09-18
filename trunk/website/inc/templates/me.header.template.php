<?php
$main_character = $__url_useraccount->GetMainCharacterName();
$main_character_image = $main_character !== null ? '//'.$main_character['locale'].'.'.$domain.'/avatar/'.$main_character['name'] : '';

$is_self = $_loggedin && $__url_useraccount->GetID() == $_loginaccount->GetID();
$friend_status = $_loggedin ? GetFriendStatus($_loginaccount->GetID(), $__url_useraccount->GetID()) : '';

?>

<style type="text/css">

.avatar {
	width:200px;
}

.name {
	font-weight: bold;
	font-family: Helvetica, sans-serif;
	font-size: 24px;
	letter-spacing: 0px;
}

.name_extra {
	font-weight: 200;
	letter-spacing: normal;
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

.character_display {
    background-repeat: no-repeat;
    background-size: cover;
    height: 180px;
    border: 0px !important;
    box-shadow: none !important;
}

.modal-title h4, .modal, .modal-body {
    color: #000 !important;
}

</style>

<div class="row">
	<div class="span3" style="text-align:center;">
<?php
if ($main_character !== null):
?>
		<div class="invert-box character_display" style="background-image: url('<?php echo $main_character_image; ?>?flip')">
		</div>
<?php
endif;
?>
		<div class="invert-box">
			<p class="name">
				<?php echo $__url_useraccount->GetNickname(); ?> <span class="rank"><?php echo GetRankTitle($rank); ?></span><br />
			</p>
			<p class="name_extra">last seen <?php echo time_elapsed_string($__url_useraccount->GetLastLoginSeconds()); ?> ago...<br/></p>
<?php if ($_loggedin && $_loginaccount->GetAccountRank() >= RANK_ADMIN): ?>
			<button type="button" class="btn btn-info" data-toggle="modal" href="#myModal">Manage</button>
	<?php if ($__url_useraccount->IsMuted()): ?>
			<button type="button" class="btn btn-danger" onclick="UnMute('<?php echo $__url_useraccount->GetUsername(); ?>')">Unmute</button>
	<?php else: ?>
			<button type="button" class="btn btn-danger" onclick="Mute('<?php echo $__url_useraccount->GetUsername(); ?>')">Mute</button>
	<?php endif; ?>
<?php endif; ?>
		</div>
<?php
if ($_loggedin && !$is_self) {
	if ($friend_status == 'FRIENDS') {
?>

		<p class="name_extra invert-box">You are already friends! <button class="btn btn-mini btn-danger" onclick="RemoveFriend('<?php echo $__url_useraccount->GetUsername(); ?>')">Remove?</button></p>
<?php
	}
	elseif ($friend_status == 'NO_FRIENDS') {
?>

		<p class="name_extra invert-box"><button class="btn btn-mini btn-success" onclick="InviteFriend('<?php echo $__url_useraccount->GetUsername(); ?>')">Add as a friend?</button></p>
<?php
	}
	elseif ($friend_status == 'NOT_ACCEPTED_YOU') {
?>

		<p class="name_extra invert-box"><?php echo $__url_useraccount->GetNickname(); ?> is still waiting for your friend approval. <button class="btn btn-mini btn-success" onclick="AcceptFriend('<?php echo $__url_useraccount->GetUsername(); ?>')">Accept?</button></p>
<?php
	}
	elseif ($friend_status == 'NOT_ACCEPTED_FRIEND') {
?>

		<p class="name_extra invert-box">You are still waiting for <?php echo $__url_useraccount->GetNickname(); ?>'s friend approval. <button class="btn btn-mini btn-danger" onclick="RemoveFriend('<?php echo $__url_useraccount->GetUsername(); ?>')">Cancel?</button></p>
<?php
	}
}
?>

		<div class="invert-box">
<?php
$q = $__database->query("
SELECT
	(SELECT COUNT(*) FROM social_statuses WHERE account_id = ".$__url_useraccount->getID()."),
	(SELECT COUNT(*) FROM ".DB_GMS.".characters c LEFT JOIN ".DB_GMS.".users u ON u.id = c.userid WHERE u.account_id = ".$__url_useraccount->getID().") +
	(SELECT COUNT(*) FROM ".DB_EMS.".characters c LEFT JOIN ".DB_EMS.".users u ON u.id = c.userid WHERE u.account_id = ".$__url_useraccount->getID()."),
	(SELECT COUNT(*) FROM friend_list WHERE account_id = ".$__url_useraccount->getID()." AND accepted_on IS NOT NULL) +
	(SELECT COUNT(*) FROM friend_list WHERE friend_id = ".$__url_useraccount->getID()." AND accepted_on IS NOT NULL)
");

$stats = $q->fetch_row();
$q->free();

?>
			<p class="side"><i class="icon-comment faded"></i> <a href="//<?php echo $subdomain.".".$domain; ?>/"><?php echo $stats[0]; ?> Statuses</a></p>
			<p class="side"><i class="icon-book faded"></i> <a href="//<?php echo $subdomain.".".$domain; ?>/characters/"><?php echo $stats[1]; ?> Characters</a></p>
			<p class="side"><i class="icon-user faded"></i> <a href="//<?php echo $subdomain.".".$domain; ?>/friends/"><?php echo $stats[2]; ?> Friends</a></p>

		</div>
	</div>
<?php
if ($_loggedin && $_loginaccount->IsRankORHigher(RANK_ADMIN)) {
	if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['setrankpls'])) {
		$__url_useraccount->SetAccountRank(intval($_POST['setrankpls']));
		$__url_useraccount->Save();
		Logging('admin', $_loginaccount->GetUsername(), 'account_rank', 'Changed '.$__url_useraccount->GetUserName().'\'s rank.');
	}
	$currentrank = $__url_useraccount->GetAccountRank();
?>

	<div id="myModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			<h3 id="myModalLabel">Manage: @<?php echo $__url_useraccount->GetUsername(); ?> 
				<span style="font-size:15px !important;">[<?php echo $__url_useraccount->GetLastIP(); ?>]</span>
				<small>- <?php echo GetRankTitle($__url_useraccount->GetAccountRank()); ?></small>
			</h3>
        </div>
        <div class="modal-body" style="overflow-y:auto;height:400px;">
			<p class="alert alert-info"><?php echo $__url_useraccount->GetNickname(); ?> was last online <?php echo time_elapsed_string($__url_useraccount->GetLastLoginSeconds()); ?> ago!</p>

	
			<form class="form-horizontal" method="post">
				Rank: 
				<select name="setrankpls" style="height:35px !important;width: 150px !important;">
<?php foreach ($_account_ranks as $rankid => $rankname): ?>
					<option value="<?php echo $rankid; ?>"<?php echo $currentrank == $rankid ? ' selected="selected"' : ''; ?>><?php echo $rankname; ?></option>
<?php endforeach; ?>
				</select>
				<br />
				<button type="submit" class="btn btn-primary" style="margin-top:20px;">Save changes?</button>
			</form>
        </div>
	</div>
<?php
}
?>