<?php
require_once __DIR__.'/../inc/header.php';
require_once __DIR__.'/../inc/job_list.php';
require_once __DIR__.'/../inc/exp_table.php';

$q = $__database->query("
SELECT 
	*,
	w.world_name,
	`GetCharacterAccountID`(id) AS account_id,
	TIMESTAMPDIFF(SECOND, last_update, NOW()) AS `secs_since`
FROM
	`characters` chr
LEFT JOIN 
	world_data w
	ON
		w.world_id = chr.world_id
WHERE 
	chr.name = '".$__database->real_escape_string($_GET['name'])."'");

if ($q->num_rows == 0) {
	$q->free();
?>
<center>
	<img src="//<?php echo $domain; ?>/inc/img/no-character.gif" />
	<p>Character not found! The character may have been removed or hidden.</p>
</center>
<?php
	require_once __DIR__.'/../inc/footer.php';
	die;
}

$character_info = $q->fetch_assoc();
$character_account_id = GetCharacterAccountId($character_info['id']);

// Check character status
$friend_status = $_loggedin ? ($character_account_id == $_loginaccount->GetID() ? 'FOREVER_ALONE' : GetFriendStatus($_loginaccount->GetID(), $character_account_id)) : 'NO_FRIENDS';
$status = GetCharacterStatus($character_info['id']);

if ($status == 1 && (!$_loggedin || ($_loggedin && $friend_status != 'FRIENDS' && $friend_status != 'FOREVER_ALONE' && $_loginaccount->GetAccountRank() < RANK_MODERATOR))) {
?>
<center>
	<img src="//<?php echo $domain; ?>/inc/img/no-character.gif" />
	<p>Only friends are allowed to view this character!</p>
</center>
<?php
	require_once __DIR__.'/../inc/footer.php';
	die;
}
elseif ($status == 2 && ($_loggedin && $friend_status != 'FOREVER_ALONE' && $_loginaccount->GetAccountRank() < RANK_MODERATOR)) {
	// displays the same error as not found to not tell if exists or not.
?>
<center>
	<img src="//<?php echo $domain; ?>/inc/img/no-character.gif" />
	<p>Character not found! The character may have been removed or hidden.</p>
</center>
<?php
    require_once __DIR__.'/../inc/footer.php';
	die;
}

elseif ($status == 2 && !$_loggedin) {
	// displays the same error as not found to not tell if exists or not.
?>
<center>
	<img src="//<?php echo $domain; ?>/inc/img/no-character.gif" />
	<p>Character not found! The character may have been removed or hidden.</p>
</center>
<?php
    require_once __DIR__.'/../inc/footer.php';
	die;
}


else {
	$account = Account::Load($character_info['account_id']);
	$internal_id = $character_info['internal_id'];
	$stat_addition = GetCorrectStat($internal_id);
	$__is_viewing_self = $friend_status == 'FOREVER_ALONE';
	
	$channelid = $character_info['channel_id'];
	if ($channelid == -1) $channelid = 'Unknown';
	else $channelid++; // 1 = 0
	
	$__hidden_objects = array();
	
	function IsHiddenObject($optionName, $no_override = false) {
		global $__database, $internal_id, $__hidden_objects, $_loggedin, $_loginaccount;
		
		if ($_loggedin && !$no_override && $_loginaccount->IsRankOrHigher(RANK_MODERATOR))
			return false;
		
		if (isset($__hidden_objects[$optionName]))
			return $__hidden_objects[$optionName];
		$q = $__database->query("
SELECT
	option_value
FROM
	character_options
WHERE
	character_id = ".$internal_id."
	AND
	option_key = 'display_".$__database->real_escape_string($optionName)."'
");

		if ($q->num_rows == 0) {
			$__hidden_objects[$optionName] = false;
			return false;
		}
		else {
			$row = $q->fetch_row();
			$q->free();
			$__hidden_objects[$optionName] = ($row[0] == 1);
			return $__hidden_objects[$optionName];
		}
	}
	
	function MakeHideToggleButton($optionName) {
		global $character_info, $__is_viewing_self, $__hidden_objects;
		if (!$__is_viewing_self) return;
		
		$hidden = IsHiddenObject($optionName, true);
?>
		<input type="checkbox" class="visibility-toggler" style="display: none;" name="<?php echo $character_info['name']; ?>" option="<?php echo $optionName; ?>" <?php echo (!$hidden ? 'checked' : ''); ?> />
<?php
	}
	
	
	// Some quick count queries
	$qcount = $__database->query("
SELECT
	(SELECT COUNT(DISTINCT a.questid) FROM (SELECT questid FROM quests_done WHERE character_id = ".$internal_id." UNION ALL SELECT questid FROM quests_done_party WHERE character_id = ".$internal_id.") a) as `quests_done`,
	(SELECT COUNT(DISTINCT a.questid) FROM (SELECT questid FROM quests_running WHERE character_id = ".$internal_id." UNION ALL SELECT questid FROM quests_running_party WHERE character_id = ".$internal_id.") a) as `quests_left`,
	(SELECT COUNT(*) FROM skills WHERE character_id = ".$internal_id.") as `skills`
");
	$statistics = $qcount->fetch_assoc();
	$qcount->free();
	
$avatarurl = 'http://'.$domain.'/ignavatar/' . $character_info['name'].'?size=big&flip';
?>
<div class="row">
	<div class="span12">
	<?php if ($character_info['account_id'] !== '2') { ?>
		<a href="//<?php echo $account->GetUsername(); ?>.<?php echo $domain; ?>/" class="btn btn-mini pull-right" style="margin-bottom: 10px">Return to <?php echo $account->GetNickName(); ?>'s Profile</a>
	<?php } else { ?>
		<p class="alert alert-warning">Important: This character was incorrectly added to Mapler.me. (Logged in to an account before connecting to Mapler.me). To gain ownership of this character, please try re-adding the account, or send a request through Support with a screenshot of your character in-game saying your username.</p>
	<?php } ?>
	</div>
</div>

<div class="row">
	<div class="span3" style="text-align:center;">
<?php if ($__is_viewing_self): ?>
		<button class="btn" style="margin-bottom:10px;" onclick="ToggleTogglers()">Display/hide Visibility</button>
<?php endif; ?>
		<div class="location">
			<img src="<?php echo $avatarurl ?>" class="h" /><br />
		</div>
		<p class="name"><?php echo $character_info['name']; ?><br/>
			<small class="name_extra" style="margin-top:10px;">Level <?php echo $character_info['level']; ?> <?php echo GetJobname($character_info['job']); ?></small>
			<div class="progress progress-striped">
				<div class="bar" style="width: <?php echo GetExpPercentage($character_info['level'], $character_info['exp']); ?>%;"></div>
			</div>
		</div>
		</p>
<?php if ($_loggedin && $_loginaccount->GetAccountRank() >= RANK_MODERATOR): ?>
		<hr />
		<p class="side"> Internal ID: <?php echo $internal_id; ?></p>
<?php endif; ?>
		<hr />
		<p class="side"><i class="icon-home faded"></i> <?php echo GetMapname($character_info['map']); ?></p>
		<p class="side"><i class="icon-globe faded"></i> <?php echo $character_info['world_name']; ?></p>
		<p class="side"><i class="icon-map-marker faded"></i> Channel <?php echo $channelid; ?></p>
<?php if (isset($character_info['married_with']) && $character_info['married_with'] != $character_info['name']): ?>
<?php if ($__is_viewing_self || !IsHiddenObject('marriage')): ?>
<?php MakeHideToggleButton('marriage'); ?>
		<p class="side"><i class="icon-heart faded"></i> Married to <a href="//<?php echo $domain; ?>/player/<?php echo $character_info['married_with']; ?>"><?php echo $character_info['married_with']; ?></a></p>
<?php endif; ?>
<?php endif; ?>
		<p class="side"><i class="icon-eye-open faded"></i> Last seen <?php echo time_elapsed_string($character_info['secs_since']); ?> ago</p>
		<hr />
		<p class="side"><i class="icon-tasks"></i> <?php echo $statistics['quests_done']; ?> quests completed</p>
		<p class="side"><i class="icon-tasks faded"></i> <?php echo $statistics['quests_left']; ?> quests in progress</p>
		<p class="side"><i class="icon-briefcase faded"></i> <?php echo $statistics['skills']; ?> skills learned</p>
		<hr />
		
		<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
		
		<a href="https://twitter.com/share" class="twitter-share-button" data-text="Check out the character <?php echo $character_info['name']; ?> on #maplerme!" data-dnt="true"></a>
				
		<div class="fb-like" style="position:relative;right:20px;" data-href="http://<?php echo $domain; ?>/player/<?php echo $character_info['name']; ?>" data-send="false" data-layout="button_count" data-width="450" data-show-faces="false"></div>
				
		<hr />
		<p class="side"><i class="icon-user faded"></i> <a href="//<?php echo $domain; ?>/avatar/<?php echo $character_info['name']; ?>">Avatar</a></p>
		<p class="side"><i class="icon-heart faded"></i> <a href="//<?php echo $domain; ?>/card/<?php echo $character_info['name']; ?>">Player Card</a></p>
		<p class="side"><i class="icon-th-list faded"></i> <a href="//<?php echo $domain; ?>/infopic/<?php echo $character_info['name']; ?>">Statistics</a></p>
	</div>
	
<?php	
if (!$_loggedin) {
?>
	<div class="span9" style="margin-left:10px;">
		<p class="status noclear" style="margin-top:0px;"><i class="icon-ban-circle faded"></i> To view more information and equipment for <?php echo $character_info['name']; ?>, please <a href="//<?php echo $domain; ?>/login/">login</a> or <a href="//<?php echo $domain; ?>/signup/">register!</a></p>
	</div>
</div>
<?php
	require_once __DIR__.'/../inc/footer.php';
	die();
}

elseif ($status == 1 && ($_loggedin && $friend_status = 'FRIENDS' && $friend_status != 'FOREVER_ALONE')) {
?>
		<p class="status noclear" style="margin-top:0px;"><i class="icon-ok faded"></i>
			<?php echo $account->GetNickName(); ?> has allowed you to view this character.
		</p>
<?php
}

elseif ($status == 1 && ($_loggedin && IsOwnAccount())) {
	// displays the same error as not found to not tell if exists or not.
?>
<div class="span9" style="margin-left:10px;">
		<p class="status noclear" style="margin-top:0px;"><i class="icon-ok faded"></i>
<?php if ($_loginaccount->GetAccountRank() >= RANK_MODERATOR): ?>
		This character can only be seen by friends, set by <?php echo $account->GetNickName(); ?>. 
<?php else: ?>
		This character is currently only viewable to friends and yourself.
<?php endif; ?>
		</p>
	</div>
<?php
}

else if ($status == 2 && ($_loggedin && IsOwnAccount())) {
	// displays the same error as not found to not tell if exists or not.
?>
<div class="span9" style="margin-left:10px;">
		<p class="status noclear" style="margin-top:0px;"><i class="icon-ok faded"></i>
<?php if ($_loginaccount->GetAccountRank() >= RANK_MODERATOR): ?>
		This character is currently hidden by <?php echo $account->GetNickName(); ?>. 
<?php else: ?>
		This character is currently only viewable to yourself (hidden).
<?php endif; ?>
		</p>
	</div>
<?php
}
?>
	
	<div class="span9" style="margin-left:10px;">
		<p class="lead">Equipment</p>
<?php

/******************* DRAGONS BE HERE ****************************/

$inventory = new InventoryData($character_info['internal_id']);


$optionlist = array();
$optionlist['str'] = 'STR : ';
$optionlist['dex'] = 'DEX : ';
$optionlist['int'] = 'INT : ';
$optionlist['luk'] = 'LUK : ';
$optionlist['maxhp'] = 'MaxHP : ';
$optionlist['maxmp'] = 'MaxMP : ';
$optionlist['weaponatt'] = 'Weapon Attack : ';
$optionlist['weapondef'] = 'Weapon Def. : ';
$optionlist['magicatt'] = 'Magic Attack : ';
$optionlist['magicdef'] = 'Magic Def. : ';
$optionlist['acc'] = 'Accuracy : ';
$optionlist['avo'] = 'Avoidability : ';
$optionlist['hands'] = 'Hands : ';
$optionlist['jump'] = 'Jump : ';
$optionlist['speed'] = 'Speed : ';
$optionlist['enchantments'] = 'Nr of times enchanted : ';
$optionlist['slots'] = 'Upgrades available : ';
$optionlist['hammers'] = 'Nr of hammers applied : ';


$reqlist = array();
$reqlist['reqlevel'] = 'REQ LEV : ';
$reqlist['reqstr'] = 'REQ STR : ';
$reqlist['reqdex'] = 'REQ DEX : ';
$reqlist['reqint'] = 'REQ INT : ';
$reqlist['reqluk'] = 'REQ LUK : ';
$reqlist['reqpop'] = 'REQ FAM : '; // pop = population -> Fame
$reqlist['itemlevel'] = 'ITEM LEV : ';
$reqlist['itemexp'] = 'ITEM EXP : ';

$IDlist = array();
$PotentialList = array();
$NebuliteList = array();

function GetItemQuality($item, $stats) {
	if ($stats['cash'] == 1) return 0; // Cash items do not have stats
	if ($item->itemid / 100000 == 19) return 0; // Taming mobs etc neither

	// Spiegelmanns badges have static qualities:
	if ($item->itemid >= 1182000 && $item->itemid <= 1182005)
		return $item->itemid - 1182000;


	$longcalc =
		(
			$item->str +
			$item->dex +
			$item->int +
			$item->luk +
			$item->maxhp +
			$item->maxmp +
			$item->acc +
			$item->avo +
			$item->speed +
			$item->hands +
			$item->jump +
			$item->weapondef +
			$item->weaponatt +
			$item->magicdef +
			$item->magicatt
		) 
		 - // Now, minus
		(
			ValueOrDefault($stats['incstr'], 0) +
			ValueOrDefault($stats['incdex'], 0) +
			ValueOrDefault($stats['incint'], 0) +
			ValueOrDefault($stats['incluk'], 0) +
			ValueOrDefault($stats['incmhp'], 0) +
			ValueOrDefault($stats['incmmp'], 0) +
			ValueOrDefault($stats['incacc'], 0) +
			ValueOrDefault($stats['inceva'], 0) + // yep...
			ValueOrDefault($stats['incspeed'], 0) +
			ValueOrDefault($stats['inccraft'], 0) +
			ValueOrDefault($stats['incjump'], 0) +
			ValueOrDefault($stats['incpad'], 0) +
			ValueOrDefault($stats['incpdd'], 0) +
			ValueOrDefault($stats['incmad'], 0) +
			ValueOrDefault($stats['incmdd'], 0) 
		);
	
	if ($longcalc < 0) return -1;
	elseif ($longcalc >= 0 && $longcalc < 6) return 0;
	elseif ($longcalc >= 6 && $longcalc < 23) return 1;
	elseif ($longcalc >= 23 && $longcalc < 40) return 2;
	elseif ($longcalc >= 40 && $longcalc < 55) return 3;
	elseif ($longcalc >= 55 && $longcalc < 70) return 4;
	elseif ($longcalc >= 70) return 5;
}


function GetItemDialogInfo($item, $isequip) {
	global $PotentialList, $IDlist, $reqlist, $optionlist, $NebuliteList;
	
	if (!array_key_exists($item->itemid, $IDlist)) {
		$IDlist[$item->itemid] = IGTextToWeb(GetMapleStoryString("item", $item->itemid, "desc"));
	}
	
	if ($isequip && $item->potential1 != 0 && !array_key_exists($item->potential1, $PotentialList)) 
		$PotentialList[$item->potential1] = GetPotentialInfo($item->potential1);
	if ($isequip && $item->potential2 != 0 && !array_key_exists($item->potential2, $PotentialList))
		$PotentialList[$item->potential2] = GetPotentialInfo($item->potential2);
	if ($isequip && $item->potential3 != 0 && !array_key_exists($item->potential3, $PotentialList))
		$PotentialList[$item->potential3] = GetPotentialInfo($item->potential3);
	if ($isequip && $item->potential4 != 0 && !array_key_exists($item->potential4, $PotentialList))
		$PotentialList[$item->potential4] = GetPotentialInfo($item->potential4);
	if ($isequip && $item->potential5 != 0 && !array_key_exists($item->potential5, $PotentialList))
		$PotentialList[$item->potential5] = GetPotentialInfo($item->potential5);
	if ($isequip && $item->potential6 != 0 && !array_key_exists($item->potential6, $PotentialList))
		$PotentialList[$item->potential6] = GetPotentialInfo($item->potential6);
	
	if ($isequip && $item->nebulite1 != -1 && !array_key_exists($item->nebulite1, $NebuliteList))
		$NebuliteList[$item->nebulite1] = GetNebuliteInfo($item->nebulite1);
	if ($isequip && $item->nebulite2 != -1 && !array_key_exists($item->nebulite2, $NebuliteList))
		$NebuliteList[$item->nebulite2] = GetNebuliteInfo($item->nebulite2);
	if ($isequip && $item->nebulite3 != -1 && !array_key_exists($item->nebulite3, $NebuliteList))
		$NebuliteList[$item->nebulite3] = GetNebuliteInfo($item->nebulite3);

	$stats = GetItemDefaultStats($item->itemid);
	
	$tradeblock = 0;
	if ($stats['tradeblock'] == 1) {
		if ($stats['accountsharetag'] == 1) { // Account shareable
			$tradeblock = 0x10;
		}
		elseif ($stats['tradeavailable'] == 1) { // Karma
			$tradeblock = 0x20;
		}
		elseif ($stats['tradeavailable'] == 2) { // Plat Karma
			$tradeblock = 0x21;
		}
		elseif ($stats['equiptradeblock'] == 1) { // Blocked when equipped
			$tradeblock = 0x30;
		}
		else $tradeblock = 1;
	}
	
	$reqlevel = ValueOrDefault($stats['reqlevel'], 0);
	$reqstr = ValueOrDefault($stats['reqstr'], 0);
	$reqdex = ValueOrDefault($stats['reqdex'], 0);
	$reqint = ValueOrDefault($stats['reqint'], 0);
	$reqluk = ValueOrDefault($stats['reqluk'], 0);
	$reqpop = ValueOrDefault($stats['reqpop'], 0);
	
	$quality = ($isequip ? GetItemQuality($item, $stats) : 0);
	
	$arguments = 'SetItemInfo(event, this, ';
	$arguments .= $item->itemid.','.($isequip ? 1 : 0).', ';
	$arguments .= ValueOrDefault($stats['reqjob'], 0).', ';
	
	$info_array = array();
	$info_array['iteminfo'] = $item;
	$info_array['requirements'] = array(
		'level' => (int)$reqlevel,
		'pop' => (int)$reqpop,
		'job' => (int)ValueOrDefault($stats['reqjob'], 0),
		'str' => (int)$reqstr,
		'dex' => (int)$reqdex,
		'int' => (int)$reqint,
		'luk' => (int)$reqluk
	);
	$info_array['other_info'] = array(
		'tradeblock' => $tradeblock,
		'expires' => GetSystemTimeFromFileTime($item->expires),
		'quality' => $quality
	);
	$info_array['other_info']['locked'] = ($isequip ? $item->HasLock() : 0);
	$info_array['other_info']['spiked'] = ($isequip ? $item->HasSpikes() : 0);
	$info_array['other_info']['coldprotection'] = ($isequip ? $item->HasColdProtection() : 0);
	$info_array['other_info']['questitem'] = (int)ValueOrDefault($stats['quest'], 0);
	$info_array['other_info']['karmad'] = ($isequip ? $item->IsKarmad() : 0);
	$info_array['other_info']['oneofakind'] = (int)ValueOrDefault($stats['only'], 0);
	$info_array['other_info']['charmexp'] = (int)ValueOrDefault($stats['charmexp'], 0);
	$info_array['other_info']['willexp'] = (int)ValueOrDefault($stats['willexp'], 0);
	$info_array['other_info']['charismaexp'] = (int)ValueOrDefault($stats['charismaexp'], 0);
	$info_array['other_info']['senseexp'] = (int)ValueOrDefault($stats['senseexp'], 0);
	$info_array['other_info']['craftexp'] = (int)ValueOrDefault($stats['craftexp'], 0);
	$info_array['other_info']['insightexp'] = (int)ValueOrDefault($stats['insightexp'], 0);

	
	$potential = 0;
	if ($isequip) {
		if ($item->HasClosedPotential()) {
			$potential = 1; // Default color
		}
		else {
			if ($item->potential1 != 0 || $item->potential4 != 0) $potential++;
			//if ($item->potential2 != 0) $potential++;
			//if ($item->potential3 != 0) $potential++;
			//if ($item->potential4 != 0) $potential++;
			//if ($item->potential5 != 0) $potential++;
			//if ($item->potential6 != 0) $potential++;
		}
	}
	
	$arguments_temp = 'SetItemInfo(event, this, '.json_encode($info_array).')';
	$iconid = $item->itemid;
	if ($isequip && $item->display_id != 0) {
		$iconid -= $iconid % 10000;
		$iconid += $item->display_id;
	}
	

	return array('mouseover' => $arguments_temp, 'potentials' => $potential, 'iconid' => $iconid);
}



$inv_pos_offx = 10;
$inv_pos_offy = 28;
$inv_extra_offx = $inv_extra_offy = 0;

function InventoryPosCalc($row, $col) {
	global $inv_pos_offx, $inv_pos_offy;
	global $inv_extra_offx, $inv_extra_offy;
?>
top: <?php echo ($row * (33 + $inv_extra_offy)) + $inv_pos_offy; ?>px; left: <?php echo ($col * (33 + $inv_extra_offx)) + $inv_pos_offx; ?>px; margin-bottom: <?php echo $inv_extra_offy; ?>px;<?php
}

?>
<style type="text/css">
.character_equips {
	background-image: url('//<?php echo $domain; ?>/inc/img/ui/Item/equips_background.png');
}

.character_pets_holder {
	background-image: url('//<?php echo $domain; ?>/inc/img/ui/Item/pet_equip.png');
}

.character_totems {
	background-image: url('//<?php echo $domain; ?>/inc/img/ui/Item/totem.png');
	width: 118px;
	height: 71px;
	position: relative;
	margin-bottom: 15px;
}

.character_droid {
	background-image: url('//<?php echo $domain; ?>/inc/img/ui/Item/final_ui_android.png');
	width: 154px;
	height: 172px;
	position: relative;
}

.job-specific-inventory > div {
	height: 172px;
	position: relative;
}

.job-specific-inventory .mechanic {
	background-image: url('//<?php echo $domain; ?>/inc/img/ui/Item/final_ui_mech.png');
	width: 151px;
}

.job-specific-inventory .coordinate {
	background-image: url('//<?php echo $domain; ?>/inc/img/ui/Item/final_ui_coordinate.png');
	width: 154px;
}

.job-specific-inventory .evan {
	background-image: url('//<?php echo $domain; ?>/inc/img/ui/Item/final_ui_dragon.png');
	width: 151px;
}


.inventory div {
	background-image: url('//<?php echo $domain; ?>/inc/img/ui/Item/item_bg.png');
}

#inventories {
	background-image: url('//<?php echo $domain; ?>/inc/img/ui/Item/final_ui.png');
}

.char-inventories > div {
	display: inline-block;
	float: left;
	margin-left: 15px;
}

<?php

// PET STUFF
$inv_pos_offx = 10; // Diff offsets
$inv_pos_offy = 22;
$inv_extra_offx = $inv_extra_offy = 0;

?>

</style>

<?php
$equips = $inventory->GetEquips();

$petequip_slots = array();
$petequip_slots[24] = array(0, -1); // Auto HP
$petequip_slots[25] = array(0, -1); // Auto MP

// Pet 1
$petequip_slots[14] = array(0, -1);
//$petequip_slots[20] = array(0, -1); // Collar?
$petequip_slots[21] = array(0, -1); // Item pouch, other slots = quote ring
$petequip_slots[22] = array(0, -1);
$petequip_slots[23] = array(0, -1);
$petequip_slots[26] = array(0, -1);
$petequip_slots[27] = array(0, -1);
$petequip_slots[28] = array(0, -1);
$petequip_slots[29] = array(0, -1);
$petequip_slots[46] = array(0, -1); // Item Ignore 1
$petequip_slots[62] = array(0, -1); // Smart Pet
$petequip_slots[57] = array(0, -1); // Auto buff

// Pet 2
$petequip_slots[30] = array(1, 14);
$petequip_slots[31] = array(1, 20);
$petequip_slots[32] = array(1, 29); // Flipped w/ 21
$petequip_slots[33] = array(1, 22);
$petequip_slots[34] = array(1, 23);
$petequip_slots[35] = array(1, 26);
$petequip_slots[36] = array(1, 27);
$petequip_slots[37] = array(1, 21); // Flipped w/ 29
$petequip_slots[47] = array(1, -1); // Item Ignore 2
$petequip_slots[63] = array(1, -1); // Smart Pet [guess]

// Pet 3
$petequip_slots[38] = array(2, 14);
$petequip_slots[39] = array(2, 20);
$petequip_slots[40] = array(2, 29); // Flipped w/ 21
$petequip_slots[41] = array(2, 22);
$petequip_slots[42] = array(2, 23);
$petequip_slots[43] = array(2, 26);
$petequip_slots[44] = array(2, 27);
$petequip_slots[45] = array(2, 21); // Flipped w/ 29
$petequip_slots[48] = array(2, -1); // Item Ignore 3
$petequip_slots[64] = array(2, -1); // Smart Pet [guess]

$petequips = array();
$petequips[0] = array();
$petequips[1] = array();
$petequips[2] = array();

$normalequips = array();
$normalequips['normal'] = array();
$normalequips['Coordinate'] = array();
$normalequips['Totem'] = array();
$normalequips['Android'] = array();
$normalequips['Mechanic'] = array();
$normalequips['Evan'] = array();
$normalequips['Bits'] = array();
$normalequips['BitsCase'] = array();
$cashequips = array();

foreach ($equips as $orislot => $item) {
	$slot = abs($orislot);
	if ($slot > 100) $slot -= 100;
	
	if (array_key_exists($slot, $petequip_slots)) {
		$block = $petequip_slots[$slot][0];
		$display_slot = $petequip_slots[$slot][1];
		if ($display_slot == -1)
			$display_slot = $slot;
		
		$petequips[$block][$display_slot] = $item;
	}
	else {
		if ($orislot > -100) 		$normalequips['normal'][$orislot] = $item;
		elseif ($orislot <= -20000) $normalequips['Bits'][$orislot] = $item;
		elseif ($orislot <= -5000) 	$normalequips['Totem'][$orislot] = $item;
		elseif ($orislot <= -1500) 	$normalequips['BitsCase'][$orislot] = $item;
		elseif ($orislot <= -1300) 	$normalequips['Coordinate'][$orislot] = $item;
		elseif ($orislot <= -1200) 	$normalequips['Android'][$orislot] = $item;
		elseif ($orislot <= -1100) 	$normalequips['Mechanic'][$orislot] = $item;
		elseif ($orislot <= -1000) 	$normalequips['Evan'][$orislot] = $item;
		elseif ($orislot <= -100) 	$cashequips[$orislot] = $item;
	}
}

function AddInventoryItems(&$inventory) {
	foreach ($inventory as $slot => $item) {
		$slot = abs($slot) % 100;
		
		$info = GetItemDialogInfo($item, true);
		
		$itemwzinfo = GetItemWZInfo($info['iconid']);
		
		
		if ($info['potentials'] != 0) {
?>
				<div class="item-icon slot<?php echo $slot; ?> potential<?php echo $info['potentials']; ?>" style="position: absolute;"></div>
<?php
		}
?>
				<img class="item-icon slot<?php echo $slot; ?>" potential="<?php echo $info['potentials']; ?>" style="margin-top: <?php echo (32 - $itemwzinfo['info']['icon']['origin']['Y']); ?>px; margin-left: <?php echo -$itemwzinfo['info']['icon']['origin']['X']; ?>px;" src="<?php echo GetItemIcon($info['iconid']); ?>" item-name="<?php echo IGTextToWeb(GetMapleStoryString("item", $item->itemid, "name")); ?>" onmouseover='<?php echo $info['mouseover']; ?>' onmousemove="MoveWindow(event)" onmouseout="HideItemInfo()" />
<?php
	}
}

?>

<div class="row char-inventories">
<?php if ($__is_viewing_self || !IsHiddenObject('equip_general')): ?>
	<div style="width: 184px;">
<?php MakeHideToggleButton('equip_general'); ?>
		<div class="character_equips">
			<div id="normal_equips">

<?php
foreach ($normalequips['normal'] as $slot => $item) {
	$slot = abs($slot);
	
	$info = GetItemDialogInfo($item, true);
	
	$itemwzinfo = GetItemWZInfo($info['iconid']);
	
	
	if ($info['potentials'] != 0) {
?>
				<div class="item-icon slot<?php echo $slot; ?> potential<?php echo $info['potentials']; ?>" style="position: absolute;"></div>
<?php
	}
?>
				<img class="item-icon slot<?php echo $slot; ?>" potential="<?php echo $info['potentials']; ?>" style="margin-top: <?php echo (32 - $itemwzinfo['info']['icon']['origin']['Y']); ?>px; margin-left: <?php echo -$itemwzinfo['info']['icon']['origin']['X']; ?>px;" src="<?php echo GetItemIcon($info['iconid']); ?>" item-name="<?php echo IGTextToWeb(GetMapleStoryString("item", $item->itemid, "name")); ?>" onmouseover='<?php echo $info['mouseover']; ?>' onmousemove="MoveWindow(event)" onmouseout="HideItemInfo()" />
<?php
}
?>
			</div>


			<div id="cash_equips">
<?php
foreach ($cashequips as $slot => $item) {
	$slot = abs($slot) - 100;
	
	$info = GetItemDialogInfo($item, true);
	
	$itemwzinfo = GetItemWZInfo($info['iconid']);
	
	
	if ($info['potentials'] != 0) {
?>
				<div class="item-icon slot<?php echo $slot; ?> potential<?php echo $info['potentials']; ?>" style="position: absolute;"></div>
<?php
	}
?>
				<img class="item-icon slot<?php echo $slot; ?>" potential="<?php echo $info['potentials']; ?>" style="margin-top: <?php echo (32 - $itemwzinfo['info']['icon']['origin']['Y']); ?>px; margin-left: <?php echo -$itemwzinfo['info']['icon']['origin']['X']; ?>px;" src="<?php echo GetItemIcon($info['iconid']); ?>" item-name="<?php echo IGTextToWeb(GetMapleStoryString("item", $item->itemid, "name")); ?>" onmouseover='<?php echo $info['mouseover']; ?>' onmousemove="MoveWindow(event)" onmouseout="HideItemInfo()" />
<?php
}
?>
			</div>
			<div style="bottom: 3px; right: 20px; position: absolute;">
				<label><input type="checkbox" onchange="ShowCashEquips(this.checked)" style="display: inline;" /> Show cash items</label>
			</div>
		</div>
	</div>
<?php endif; /* Hidden/not hidden check */ ?>
	
<?php if ($__is_viewing_self || !(IsHiddenObject('equip_droid_totem') && IsHiddenObject('equip_droid_totem'))): ?>
	<div class="char-totems-droid">
<?php 	MakeHideToggleButton('equip_droid_totem'); ?>

<?php 	if ($__is_viewing_self || !IsHiddenObject('equip_droid')): ?>
		<div class="character_droid">
<?php 		MakeHideToggleButton('equip_droid'); ?>
<?php 		AddInventoryItems($normalequips['Android']); ?>
		</div>
<?php 	endif; ?>


<?php 	if ($__is_viewing_self || !IsHiddenObject('equip_totems')): ?>
		<div class="character_totems">
<?php 		MakeHideToggleButton('equip_totems'); ?>
<?php 		AddInventoryItems($normalequips['Totem']); ?>
		</div>
<?php 	endif; ?>
	</div>
<?php endif; ?>


<?php if ($__is_viewing_self || !IsHiddenObject('job_equipment')): ?>
	<div class="job-specific-inventory">
<?php 	MakeHideToggleButton('job_equipment'); ?>
<?php
$job_css_class = '';
$array_name = '';
$jobid = $character_info['job'];
$jobid_group = floor($jobid / 100);
if ($jobid_group == 65 || $jobid == 6001) { $job_css_class = 'coordinate'; $array_name = 'Coordinate'; }
elseif ($jobid_group == 35) { $job_css_class = 'mechanic'; $array_name = 'Mechanic'; }
elseif ($jobid_group == 22 || $jobid == 2001) { $job_css_class = 'evan'; $array_name = 'Evan'; }

if ($job_css_class != '') {
?>
		<div class="<?php echo $job_css_class; ?>">
			<?php AddInventoryItems($normalequips[$array_name]); ?>
		</div>
<?php
}
?>
	</div>
<?php endif; ?>

<?php if ($__is_viewing_self || !IsHiddenObject('pets')): ?>
	<div style="width: 151px;">
<?php 	MakeHideToggleButton('pets'); ?>
		<div class="character_pets">
			<div class="character_pets_holder">
				<select onchange="ChangePet(this.value)">
					<option value="0">Pet 1</option>
					<option value="1">Pet 2</option>
					<option value="2">Pet 3</option>
				</select>

<?php
for ($i = 0; $i < 3; $i++) {
?>
				<div class="pet_inventory" style="display: <?php echo $i == 0 ? 'block' : 'none'; ?>;" id="pet_<?php echo $i; ?>">
<?php
	foreach ($petequips[$i] as $slot => $item) {
		
		$info = GetItemDialogInfo($item, true);
		
		$itemwzinfo = GetItemWZInfo($item->itemid);
		
		
		if ($info['potentials'] != 0) {
?>
					<div class="item-icon slot<?php echo $slot; ?> potential<?php echo $info['potentials']; ?>" style="position: absolute;"></div>
<?php
		}
?>
					<img class="item-icon slot<?php echo $slot; ?>" potential="<?php echo $info['potentials']; ?>" style="margin-top: <?php echo (32 - $itemwzinfo['info']['icon']['origin']['Y']); ?>px; margin-left: <?php echo -$itemwzinfo['info']['icon']['origin']['X']; ?>px;" src="<?php echo GetItemIcon($item->itemid); ?>" item-name="<?php echo IGTextToWeb(GetMapleStoryString("item", $item->itemid, "name")); ?>" onmouseover='<?php echo $info['mouseover']; ?>' onmousemove="MoveWindow(event)" onmouseout="HideItemInfo()" />
<?php
	}
?>
				</div>
<?php
}
?>
<!-- so many </div> fml -->
			</div>
		</div>
	</div>
<?php endif; ?>

	<hr />

<?php if ($__is_viewing_self || !IsHiddenObject('inventories')): ?>
	<div class="span4" id="inventories">
<?php 	MakeHideToggleButton('inventories'); ?>
		<select onchange="ChangeInventory(this.value)">
			<option value="1">Equipment</option>
			<option value="2">Use</option>
			<option value="4">Etc</option>
			<option value="3">Set-up</option> <!-- Nexon! -->
			<option value="5">Cash</option>
		</select>
		<br />
<?php


$inv_pos_offx = 2; // Diff offsets
$inv_pos_offy = 2;
$inv_extra_offx = $inv_extra_offy = 2;

for ($inv = 0; $inv < 5; $inv++) {
	$inv1 = $inventory->GetInventory($inv);
?>
		<div class="character-brick inventory scrollable" id="inventory_<?php echo $inv; ?>" style="display: <?php echo $inv == 0 ? 'block' : 'none'; ?>; padding: 5px  !important;">
<?php 
	for ($i = 0; $i < count($inv1); $i++) {

		$row = floor($i / 4);
		$col = $i % 4;
		if (isset($inv1[$i])) {
			$isequip = $inv == 0;
			$item = $inv1[$i];
			$info = GetItemDialogInfo($item, $isequip);

			$itemIcon = '';
			if ($item->bagid != -1 || ($item->type == ITEM_PET && $item->IsExpired())) $itemIcon = 'D';
			
			$display_id = GetItemIconID($item->itemid); // For nebulites

			$itemwzinfo = GetItemWZInfo($display_id);

?>
			<div class="item-icon <?php echo $info['potentials'] != 0 ? ' potential'.$info['potentials'] : ''; ?>" style="<?php InventoryPosCalc($row, $col); ?>"  onmouseover="document.getElementById('item_<?php echo $inv; ?>_<?php echo $i; ?>').onmouseover(event)" onmouseout="document.getElementById('item_<?php echo $inv; ?>_<?php echo $i; ?>').onmouseout(event)" onmousemove="document.getElementById('item_<?php echo $inv; ?>_<?php echo $i; ?>').onmousemove(event)"></div>
			<img class="item-icon" id="item_<?php echo $inv; ?>_<?php echo $i; ?>" potential="<?php echo $info['potentials']; ?>" style="<?php InventoryPosCalc($row, $col); ?> margin-top: <?php echo (32 - $itemwzinfo['info']['icon']['origin']['Y']); ?>px; margin-left: <?php echo -$itemwzinfo['info']['icon']['origin']['X']; ?>px;" src="<?php echo GetItemIcon($display_id, $itemIcon); ?>" item-name="<?php echo IGTextToWeb(GetMapleStoryString("item", $item->itemid, "name")); ?>" onmouseover='<?php echo $info['mouseover']; ?>' onmousemove="MoveWindow(event)" onmouseout="HideItemInfo()" />
<?php 
			if (!$isequip) {
				// Woop
?>
			<span class="item-amount" style="<?php InventoryPosCalc($row, $col); ?>" onmouseover="document.getElementById('item_<?php echo $inv; ?>_<?php echo $i; ?>').onmouseover(event)" onmouseout="document.getElementById('item_<?php echo $inv; ?>_<?php echo $i; ?>').onmouseout(event)" onmousemove="document.getElementById('item_<?php echo $inv; ?>_<?php echo $i; ?>').onmousemove(event)"><?php echo $item->amount; ?></span>
<?php
			}
		}
		else {
?>
			<div class="item-icon" style="<?php InventoryPosCalc($row, $col); ?>"></div>
<?php
		}
	}
?>
		</div>
<?php
}
?>
		<span id="mesos"><?php echo number_format($character_info['mesos']); ?></span>

	</div>
<?php endif; ?>
	

<?php if ($__is_viewing_self || !IsHiddenObject('teleport_rocks')): ?>
	<div>
<?php 	MakeHideToggleButton('teleport_rocks'); ?>
	<table class="span4" cellpadding="5">
<?php
	$q = $__database->query("
SELECT
	`index`,
	map
FROM
	teleport_rock_locations
WHERE
	character_id = ".$internal_id."
	AND
	map <> 999999999
");

	$lastgroup = '';
	$curgroup = '';
	while ($row = $q->fetch_assoc()) {
		$index = $row['index'];
		if ($index < 5) $curgroup = 'Normal';
		elseif ($index < 5 + 10) $curgroup = 'VIP';
		elseif ($index < 5 + 10 + 13) $curgroup = 'Hyper';
		elseif ($index < 5 + 10 + 13 + 13) $curgroup = 'Hyper';
		
		if ($lastgroup != $curgroup) {
			if ($lastgroup != '') {
?>
		<tr>
			<th>&nbsp;</th>
		</tr>
<?php
			}
?>
		<tr>
			<th><?php echo $curgroup.' Rock locations'; ?></th>
		</tr>
<?php
			$lastgroup = $curgroup;
		}
?>
		<tr>
			<td><?php echo GetMapname($row['map']); ?></td>
		</tr>
<?php
	}
?>
	</table>
	</div>
<?php endif; ?>

</div>
	<hr/>

<?php if ($__is_viewing_self || !IsHiddenObject('evo_rocks')): ?>
<?php 	MakeHideToggleButton('evo_rocks'); ?>
	<p class="lead">Evolution System Cores</p>
	<table cellpadding="5">
<?php
	$q = $__database->query("
SELECT
	`card`, `level`
FROM
	evolution_levels
WHERE
	`character_id` = ".$internal_id."
GROUP BY
	`card`
ORDER BY
	`card` ASC,
	`block` ASC,
	`index` ASC
");

	$i = 0;
	while ($row = $q->fetch_assoc()) {
		if ($i % 7 == 0) {
			if ($i != 0) {
?>
		</tr>
<?php
			}
?>
		<tr>
<?php
		}
		$item = (object)array('itemid' => $row['card'], 'amount' => $row['level'], 'expires' => 3439756800);
		$info = GetItemDialogInfo($item, false);
?>
			<td>
				<img class="item-icon" potential="0" src="<?php echo GetItemIcon($item->itemid); ?>" item-name="<?php echo IGTextToWeb(GetMapleStoryString("item", $item->itemid, "name")); ?>" onmouseover='<?php echo $info['mouseover']; ?>' onmousemove="MoveWindow(event)" onmouseout="HideItemInfo()" />
				<span class="item-amount">x<?php echo $item->amount; ?></span>

			</td>
<?php
		$i++;
	}
	if ($i != 0) {
?>
		</tr>
<?php
	}
?>
	
	</table>
<?php endif; ?>

<script>
var descriptions = <?php echo json_encode($IDlist); ?>;
var potentialDescriptions = <?php echo json_encode($PotentialList); ?>;
var nebuliteInfo = <?php echo json_encode($NebuliteList); ?>;
</script>

<div id="item_info" style="display: none;">
<div class="top"></div>
	<div id="item_info_title"></div>
	<div id="item_info_extra"></div>
	<div class="icon_holder"><img id="item_info_icon" src="" title="" width="72px" height="72px"/></div>
	<div id="item_info_description"></div>
	<div class="item_req_stats">

<?php
foreach ($reqlist as $option => $desc) {
?>
			<span id="item_info_req_row_<?php echo strtolower($option); ?>">
				<?php echo $desc; ?>
				<span id="item_info_req_<?php echo strtolower($option); ?>"></span>
			</span><br/>
<?php
}
?>

	</div>
	<div id="req_job_list">
		<span class="req_job" id="item_info_reqjob_0">Beginner</span>
		<span class="req_job" id="item_info_reqjob_1">Warrior</span>
		<span class="req_job" id="item_info_reqjob_2">Magician</span>
		<span class="req_job" id="item_info_reqjob_3">Bowman</span>
		<span class="req_job" id="item_info_reqjob_4">Thief</span>
		<span class="req_job" id="item_info_reqjob_5">Pirate</span>
	</div>
	<div class="item_stats" id="item_stats_block">
		<table border="0" tablepadding="3" tablespacing="3">

<?php
foreach ($optionlist as $option => $desc) {
	if ($option == 'scrolls') continue;
?>
			<tr id="item_info_row_<?php echo strtolower($option); ?>">
				<td width="150px"><?php echo $desc; ?></td>
				<td id="item_info_<?php echo strtolower($option); ?>"></td>
			</tr>
<?php
}
?>
		</table>

	</div>
	<div class="item_potential_stats" id="item_info_potentials">
		<hr />
		<table border="0" tablepadding="3" tablespacing="3" id="potentials">
		</table>
	</div>
	<div class="item_potential_stats" id="item_nebulite_info_block" style="display: none;">
		<hr />
		<span id="nebulite_info"></span>
	</div>
	<div class="item_potential_stats" id="item_info_bonus_potentials">
		<hr />
		<table border="0" tablepadding="3" tablespacing="3" id="bonus_potentials">
		</table>
	</div>
	<div id="extra_item_info"></div>
</div>
<div class="bottom"></div>
	<hr/>

<?php if ($__is_viewing_self || !IsHiddenObject('skills')): ?>
<?php 	MakeHideToggleButton('skills'); ?>
<p class="lead">Skills</p>
<style type="text/css">
#skill_list {
	background-image: url('//<?php echo $domain; ?>/inc/img/ui/skill/bg_final.png');
}

.skill_line {
	background-image: url('//<?php echo $domain; ?>/inc/img/ui/skill/line.png');
}

.skill {
	background-image: url('//<?php echo $domain; ?>/inc/img/ui/skill/skill.png');
}
</style>

<div id="skill_list">
<?php
	
	// Initialize SP
	
	$q = $__database->query("
SELECT
	slot, amount
FROM
	sp_data
WHERE
	character_id = ".$internal_id);
	
	$spdata = array();
	while ($row = $q->fetch_assoc()) {
		$spdata[$row['slot']] = $row['amount'];
	}
	$q->free();
	
	
	$q = $__database->query("
SELECT
	skillid, level, maxlevel, ceil((expires/10000000) - 11644473600) as expires
FROM
	skills
WHERE
	character_id = ".$internal_id."
ORDER BY
	skillid / 1000 ASC
");
	
	// $BlessingOfTheFairy = "A spirit with the power of #c%s# strengthens the character. Increases by one level every time #c%s# goes up 10 levels. With the Empress's Blessing, the higher increase is applied.";
	
	$lastgroup = -1;
	$first_skill = true;
	
	
	
	
	$groups = array();
	$i = 0;
	$jobtreeid = 0;
	
	while ($row = $q->fetch_assoc()) {
		$name = GetMapleStoryString('skill', $row['skillid'], 'name');
		if ($name == NULL) continue;
		$potentialMaxLevel = GetMapleStoryString('skill', $row['skillid'], 'mlvl');
		$block = floor($row['skillid'] / 10000);
		if ($lastgroup != $block && $lastgroup < 9200) {
			$first_skill = true;
			if ($lastgroup != -1) {
?>
			</div>
<?php
			}
			$lastgroup = $block;
			$book = $block >= 9200 ? 'Profession info' : GetMapleStoryString("skill", $lastgroup, "bname");
			$groups[++$i] = $book;
			
			$sp = 0;
			
			if (!IsRealJob($block)) {
				$sp = '-';
			}
			else {
				$jobtreeid++;
				if (isset($spdata[0])) $sp = $spdata[0]; // Global SP (old jobs)
				elseif (isset($spdata[$jobtreeid])) $sp = $spdata[$jobtreeid]; // Job SP
			}
			
			
?>
			<div id="bookname_<?php echo $i; ?>" class="skill_bookname" style="display: none;">
				<img class="book_icon" src="//static_images.mapler.me/Skills/<?php echo $block; ?>/info.icon.png" />
				<div class="book_title"><?php echo $book; ?></div>
			</div>
			<span id="skillsp_<?php echo $i; ?>" class="skill_sp" style="display: none;"><?php echo $sp; ?></span>
			<div id="skilllist_<?php echo $i; ?>" class="skill_job scrollable" style="display: none;">
<?php
		}
		
		$extra = '';
		
		if ($row['maxlevel'] == NULL) {
			$row['maxlevel'] = ($potentialMaxLevel == NULL ? '-' : $potentialMaxLevel);
		}
		if ($row['skillid'] < 90000000 && $row['level'] >= 100) {
			$playername = GetCharacterName($row['level']);
			$row['level'] = '<a href="/player/'.$playername.'">'.$playername.'</a>';
		}
		elseif (strpos($name, 'Blessing of the Fairy') !== FALSE && strlen($character_info['blessingoffairy']) > 1) {
			// BOF
			$extra = ' - <a href="/player/'.$character_info['blessingoffairy'].'">'.$character_info['blessingoffairy'].'</a>';
		}
		elseif (strpos($name, 'Empress\'s Blessing') !== FALSE && strlen($character_info['blessingofempress']) > 1) {
			// Empress Blessing
			$extra = ' - <a href="/player/'.$character_info['blessingofempress'].'">'.$character_info['blessingofempress'].'</a>';
		}
		
		// GetSystemTimeFromFileTime($row['expires']);
		if (!$first_skill) {
?>
				<div class="skill_line"></div>
<?php
		}
		$first_skill = false;
?>
				<div class="skill">
					<img class="skill_icon" src="//static_images.mapler.me/Skills/<?php echo $block; ?>/<?php echo $row['skillid']; ?>/icon.png" />
					<span class="skill_title"><?php echo $name; ?></span>
					<span class="skill_level"><?php echo $row['level'].($row['maxlevel'] == '-' ? '' : ' / '.$row['maxlevel']).$extra; ?></span>
				</div>

<?php
	}
?>
			</div>
			<select onchange="ChangeSkillList(this.value)" class="skilllist_selector">
<?php foreach ($groups as $id => $name): ?>
				<option value="<?php echo $id; ?>"><?php echo $name; ?></option>
<?php endforeach; ?>
			</select>
		</div>
	
	
	<hr />
<?php endif; ?>
	
<?php if ($__is_viewing_self || !IsHiddenObject('familiars')): ?>
<?php 	MakeHideToggleButton('familiars'); ?>
	<p class="lead">Familiars</p>
	<table cellspacing="10" cellpadding="6">
<?php
// Familiars
	$q = $__database->query("
SELECT
	IF(f.name = '', (
		SELECT `value` FROM strings WHERE objectid = fi.mob_id AND objecttype = 'mob' AND `key` = 'name'
		), f.name) AS `name`,
	fi.familiar_id
FROM
	familiars f
LEFT JOIN
	phpVana_familiar_info fi
	ON
		fi.familiar_id = f.mobid
WHERE
	f.character_id = ".$internal_id);
	
	while ($row = $q->fetch_row()) {
?>
			<tr>
				<td align="center"><img src="<?php echo GetItemDataLocation('//static_images.mapler.me/', $row[1]).'stand.0.png'; ?>" title="<?php echo $row[0]; ?>" /></td>
				<td><?php echo $row[0]; ?></td>
			</tr>
<?php
	}
?>
		</table>
<?php endif; ?>


<!-- /End content block -->
	</div>
	
</div>

<?php
}
// $__database->GetRanQueries();
require_once __DIR__.'/../inc/footer.php';
?>