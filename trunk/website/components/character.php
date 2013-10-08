<?php
require_once __DIR__.'/../inc/domains.php';
require_once __DIR__.'/../inc/header.php';
require_once __DIR__.'/../inc/job_list.php';
require_once __DIR__.'/../inc/exp_table.php';
require_once __DIR__.'/../inc/classes/character_objects.php';

$__char_db = ConnectCharacterDatabase(CURRENT_LOCALE);

set_time_limit(0);

$q = $__char_db->query("
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
	chr.name = '".$__char_db->real_escape_string($_GET['name'])."'");

if ($q->num_rows == 0) {
	$q->free();
?>
<center>
	<img src="//<?php echo $locale_domain; ?>/inc/img/no-character.gif" />
	<p>Character not found! The character may have been removed or hidden.</p>
</center>
<?php
	require_once __DIR__.'/../inc/footer.php';
	die;
}

$character_info = $q->fetch_assoc();
$character_account_id = GetCharacterAccountId($character_info['id'], CURRENT_LOCALE);


$character_info['guildname'] = '';
$q2 = $__char_db->query("
SELECT
	g.name
FROM
	characters c
LEFT JOIN
	guild_members gm
	ON
		gm.character_id = c.id
LEFT JOIN
	guilds g
	ON
		g.id = gm.guild_id
WHERE
	c.internal_id = ".$character_info['internal_id']);
if ($q2->num_rows == 1) {
	// Try to fetch guildname
	$row2 = $q2->fetch_row();
	if ($row2[0] !== null) {
		$character_info['guildname'] = $row2[0];
	}
}
$q2->free();



// Check character status
$friend_status = $_loggedin ? ($character_account_id == $_loginaccount->GetID() ? 'FOREVER_ALONE' : GetFriendStatus($_loginaccount->GetID(), $character_account_id)) : 'NO_FRIENDS';
$status = GetCharacterStatus($character_info['id'], CURRENT_LOCALE);

if ($status == 1 && (!$_loggedin || ($_loggedin && $friend_status != 'FRIENDS' && $friend_status != 'FOREVER_ALONE' && $_loginaccount->GetAccountRank() < RANK_MODERATOR))) {
?>
<center>
	<img src="//<?php echo $locale_domain; ?>/inc/img/no-character.gif" />
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
	<img src="//<?php echo $locale_domain; ?>/inc/img/no-character.gif" />
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
	<img src="//<?php echo $locale_domain; ?>/inc/img/no-character.gif" />
	<p>Character not found! The character may have been removed or hidden.</p>
</center>
<?php
    require_once __DIR__.'/../inc/footer.php';
	die;
}


else {
	$account = Account::Load($character_info['account_id']);
	$internal_id = $character_info['internal_id'];
	$stat_addition = GetCorrectStat($internal_id, CURRENT_LOCALE);
	$__is_viewing_self = $friend_status == 'FOREVER_ALONE';
	
	$channelid = $character_info['channel_id'];
	if ($channelid == -1) $channelid = 'Unknown';
	else $channelid++; // 1 = 0
	
	$__hidden_objects = array();
	
	function IsHiddenObject($optionName, $no_override = false) {
		global $__char_db, $internal_id, $__hidden_objects, $_loggedin, $_loginaccount;
		
		if ($_loggedin && !$no_override && $_loginaccount->IsRankOrHigher(RANK_MODERATOR))
			return false;
		
		if (isset($__hidden_objects[$optionName]))
			return $__hidden_objects[$optionName];
		$q = $__char_db->query("
SELECT
	option_value
FROM
	character_options
WHERE
	character_id = ".$internal_id."
	AND
	option_key = 'display_".$__char_db->real_escape_string($optionName)."'
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
	$qcount = $__char_db->query("
SELECT
	(SELECT COUNT(DISTINCT a.questid) FROM (SELECT questid FROM quests_done WHERE character_id = ".$internal_id." UNION ALL SELECT questid FROM quests_done_party WHERE character_id = ".$internal_id.") a) as `quests_done`,
	(SELECT COUNT(DISTINCT a.questid) FROM (SELECT questid FROM quests_running WHERE character_id = ".$internal_id." UNION ALL SELECT questid FROM quests_running_party WHERE character_id = ".$internal_id.") a) as `quests_left`,
	(SELECT COUNT(*) FROM skills WHERE character_id = ".$internal_id.") as `skills`
");
	$statistics = $qcount->fetch_assoc();
	$qcount->free();
	
	$avatarurl = 'http://'.$locale_domain.'/ignavatar/' . $character_info['name'].'?size=big&flip';

	$expbar = array();
	$expbar['current'] = $character_info['exp'];
	$expbar['max'] = GetNextLevelEXP($character_info['level'], $character_info['exp']);
	$expbar['percentage'] = GetExpPercentage($character_info['level'], $character_info['exp']);
	$expbar['percentage'] = round($expbar['percentage'] * 100) / 100;
?>
<div class="row">
	<div class="span12">
	<?php if ($character_info['account_id'] !== '2') { ?>
		<a href="//<?php echo $account->GetUsername(); ?>.<?php echo $domain; ?>/" class="btn btn-mini pull-right" style="margin-bottom: 10px">Return to <?php echo $account->GetNickName(); ?>'s Profile</a> 		<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
		
		<a href="https://twitter.com/share" class="twitter-share-button" data-text="Check out the character <?php echo $character_info['name']; ?> on #maplerme!" data-dnt="true"></a>
				
		<div class="fb-like" style="position:relative;right:20px;" data-href="http://<?php echo $locale_domain; ?>/player/<?php echo $character_info['name']; ?>" data-send="false" data-layout="button_count" data-width="450" data-show-faces="false"></div>
		
	<?php } else { ?>
		<p class="alert alert-warning">Important: This character was incorrectly added to Mapler.me. (Logged in to an account before connecting to Mapler.me). To gain ownership of this character, please try re-adding the account, or send a request through Support with a screenshot of your character in-game saying your username.</p>
	<?php } ?>
	</div>
</div>

<div class="row">
	<div class="span3 pull-right" style="text-align:center;">
<?php if ($__is_viewing_self): ?>
		<button class="btn" style="margin-bottom:10px;" onclick="ToggleTogglers()">Display/Hide Visibility</button>
<?php endif; ?>
		<div class="location">
			<img src="<?php echo $avatarurl ?>" class="h" /><br />
		</div>
		<div class="invert-box">
		<p class="name"><?php echo $character_info['name']; ?><br />
			<small class="name_extra" style="margin-top:10px;">Level <?php echo $character_info['level']; ?> <?php echo GetJobname($character_info['job']); ?></small>
			<div class="progress progress-striped" title="<?php echo $expbar['percentage']; ?>% [<?php echo $expbar['current'].' / '.$expbar['max']; ?>]" style="width:90%;margin:0 auto;">
				<div class="bar" style="width: <?php echo $expbar['percentage']; ?>%;"></div>
			</div>
			<center><?php echo $expbar['percentage']; ?>% [<?php echo $expbar['current'].' / '.$expbar['max']; ?>]</center>
		</p>
<?php if ($_loggedin && $_loginaccount->GetAccountRank() >= RANK_MODERATOR): ?>
		<br />
		<p class="side"> ID: <?php echo $character_info['id']; ?></p>
		<p class="side"> Internal ID: <?php echo $internal_id; ?></p>
		<p class="side"> NX Account ID: <?php echo $character_info['userid']; ?></p>
<?php endif; ?>
		<br />
<?php

?>
<?php if ($character_info['guildname'] != ''): ?>
		<p class="side"><i class="icon-tag faded"></i> Guild: <a href="//<?php echo $locale_domain; ?>/guild/<?php echo GetAlliancedWorldName($character_info['world_id'], CURRENT_LOCALE); ?>/<?php echo $character_info['guildname']; ?>"><?php echo $character_info['guildname']; ?></a></p>
<?php endif; ?>
<?php
$born_quest = Quest::GetQuest($internal_id, CURRENT_LOCALE, 13261, true);
$born_at = '<abbr title="We could not determine the creation date...">???</abbr>';
if ($born_quest !== null && isset($born_quest->data['born'])) {
	$months = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
	$date = $born_quest->data['born'];
	$born_at = substr($date, 4, 2).' '.$months[intval(substr($date, 2, 2)) - 1].'  20'.substr($date, 0, 2);
}

?>
		<p class="side"><i class="icon-leaf faded"></i> Created: <?php echo $born_at; ?></p>
		<p class="side"><i class="icon-home faded"></i> <?php echo GetMapname($character_info['map'], CURRENT_LOCALE); ?></p>
		<p class="side"><i class="icon-globe faded"></i> <?php echo $character_info['world_name']; ?></p>
		<p class="side"><i class="icon-map-marker faded"></i> Channel <?php echo $channelid; ?></p>
<?php if (isset($character_info['married_with']) && $character_info['married_with'] != $character_info['name']): ?>
<?php if ($__is_viewing_self || !IsHiddenObject('marriage')): ?>
<?php MakeHideToggleButton('marriage'); ?>
		<p class="side"><i class="icon-heart faded"></i> Married to <a href="//<?php echo $locale_domain; ?>/player/<?php echo $character_info['married_with']; ?>"><?php echo $character_info['married_with']; ?></a></p>
<?php endif; ?>
<?php endif; ?>
		<p class="side"><i class="icon-eye-open faded"></i> Last seen <?php echo time_elapsed_string($character_info['secs_since']); ?> ago</p>
		<br /><br />
		<p class="side"><i class="icon-tasks"></i> <?php echo $statistics['quests_done']; ?> quests completed</p>
		<p class="side"><i class="icon-tasks faded"></i> <?php echo $statistics['quests_left']; ?> quests in progress</p>
		<p class="side"><i class="icon-briefcase faded"></i> <?php echo $statistics['skills']; ?> skills learned</p>
		<br /><br />
		<p class="side"><i class="icon-user faded"></i> <a href="//<?php echo $locale_domain; ?>/avatar/<?php echo $character_info['name']; ?>">Avatar</a></p>
		<p class="side"><i class="icon-heart faded"></i> <a href="//<?php echo $locale_domain; ?>/card/<?php echo $character_info['name']; ?>">Player Card</a></p>
		<p class="side"><i class="icon-th-list faded"></i> <a href="//<?php echo $locale_domain; ?>/infopic/<?php echo $character_info['name']; ?>">Statistics</a></p>
	</div>
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
	
	<div class="span9">
		<p class="lead">Equipment</p>
<?php

/******************* DRAGONS BE HERE ****************************/

$inventory = new InventoryData($character_info['internal_id'], CURRENT_LOCALE);


$optionlist = array();
//$optionlist['itemcategory'] = 'Category : ';
$optionlist['weaponcategory'] = 'Weapon Category : ';
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
$optionlist['enchantments'] = 'Times enchanted : ';
$optionlist['slots'] = 'Upgrades available : ';
$optionlist['hammers'] = 'Hammers applied : ';


$reqlist = array();
$reqlist['reqlevel'] = 'REQ LEV : ';
$reqlist['reqstr'] = 'REQ STR : ';
$reqlist['reqdex'] = 'REQ DEX : ';
$reqlist['reqint'] = 'REQ INT : ';
$reqlist['reqluk'] = 'REQ LUK : ';
// $reqlist['reqpop'] = 'REQ FAM : '; // pop = population -> Fame // Removed !
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
	global $reqlist, $optionlist;
	$stats = GetItemDefaultStats($item->itemid, CURRENT_LOCALE);
	
	$tradeblock = 0;
	
	if ($stats['accountsharetag'] == 1) // Account shareable
		$tradeblock = 0x10;
	elseif ($stats['tradeavailable'] == 1) // Scissors
		$tradeblock = 0x20;
	elseif ($stats['tradeavailable'] == 2) // Plat Scissors
		$tradeblock = 0x21;
	elseif ($stats['equiptradeblock'] == 1) // Blocked when equipped
		$tradeblock = 0x30;
	elseif ($stats['tradeblock'] == 1)
		$tradeblock = 1;
	
	$iscash = ValueOrDefault($stats['cash'], 0);
	
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
	
	$info_array['default_stats'] = array(
		'str' => ValueOrDefault($stats['incstr'], 0),
		'dex' => ValueOrDefault($stats['incdex'], 0),
		'int' => ValueOrDefault($stats['incint'], 0),
		'luk' => ValueOrDefault($stats['incluk'], 0),
		'maxhp' => ValueOrDefault($stats['incmhp'], 0),
		'maxmp' => ValueOrDefault($stats['incmmp'], 0),
		'acc' => ValueOrDefault($stats['incacc'], 0),
		'avo' => ValueOrDefault($stats['inceva'], 0),
		'speed' => ValueOrDefault($stats['incspeed'], 0),
		'hands' => ValueOrDefault($stats['inccraft'], 0),
		'jump' => ValueOrDefault($stats['incjump'], 0),
		'weaponatt' => ValueOrDefault($stats['incpad'], 0),
		'weapondef' => ValueOrDefault($stats['incpdd'], 0),
		'magicatt' => ValueOrDefault($stats['incmad'], 0),
		'magicdef' => ValueOrDefault($stats['incmdd'], 0),
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
	

	return array('mouseover' => $arguments_temp, 'potentials' => $potential, 'iconid' => $iconid, 'iscash' => $iscash, 'islocked' => $info_array['other_info']['locked']);
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

.character_totems {
	background-image: url('//<?php echo $locale_domain; ?>/inc/img/ui/Item/totem.png');
	width: 118px;
	height: 71px;
	position: relative;
	margin-bottom: 15px;
}


.inventory div { /* items */
	background-image: url('//<?php echo $locale_domain; ?>/inc/img/ui/Item/item_bg.png');
}
.inventory .disabled-slot {
	background-image: url('//<?php echo $locale_domain; ?>/inc/img/ui/new_inventory/disabled.png');
}
.inventory .no-bg {
	background-image: none;
}

#inventories {
	background-image: url('//<?php echo $locale_domain; ?>/inc/img/ui/new_inventory/item-background.png');
}

.character_totems,
.new-inventory-container,
.evolution-system,
#inventories {
	display: inline-block;
	float: left;
	margin-left: 0px;
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
$petequip_slots[24] = array(-1, -1); // Auto HP
$petequip_slots[25] = array(-1, -1); // Auto MP

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
$petequip_slots[57] = array(0, -1); // Auto buff 1
$petequip_slots[60] = array(0, -1); // All cure potting
$petequip_slots[62] = array(0, -1); // Smart Pet

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
$petequip_slots[58] = array(1, 57); // Auto buff 2
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
$petequip_slots[59] = array(2, 57); // Auto buff 3
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
$normalequips['Haku'] = array();
$cashequips = array();

foreach ($equips as $orislot => $item) {
	$slot = abs($orislot) % 100;
	
	if ($orislot > -200 && array_key_exists($slot, $petequip_slots)) {
		$block = $petequip_slots[$slot][0];
		$display_slot = $petequip_slots[$slot][1];
		if ($display_slot == -1)
			$display_slot = $slot;
		
		if ($block == -1) {
			$petequips[0][$display_slot] = $item;
			$petequips[1][$display_slot] = $item;
			$petequips[2][$display_slot] = $item;
		}
		else {
			$petequips[$block][$display_slot] = $item;
		}
	}
	else {
		if ($orislot > -100) 		$normalequips['normal'][$orislot] = $item;
		elseif ($orislot <= -20000) $normalequips['Bits'][$slot] = $item;
		elseif ($orislot <= -5000) 	$normalequips['Totem'][$orislot] = $item;
		elseif ($orislot <= -1500) 	$normalequips['BitsCaseBits'][$slot] = $item;
		elseif ($orislot <= -1400) 	$normalequips['Haku'][$orislot] = $item;
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
		
		$itemwzinfo = GetItemWZInfo($info['iconid'], CURRENT_LOCALE);
		
		
		if ($info['potentials'] != 0) {
?>
				<div class="item-icon slot<?php echo $slot; ?> potential<?php echo $info['potentials']; ?>" style="position: absolute;"></div>
<?php
		}
?>
				<img class="item-icon slot<?php echo $slot; ?>" potential="<?php echo $info['potentials']; ?>" style="margin-top: <?php echo (32 - $itemwzinfo['info']['icon']['origin']['Y']); ?>px; margin-left: <?php  echo -$itemwzinfo['info']['icon']['origin']['X']; ?>px;" src="<?php echo GetItemIcon($info['iconid'], CURRENT_LOCALE); ?>" item-name="<?php echo IGTextToWeb(GetMapleStoryString("item", $item->itemid, "name", CURRENT_LOCALE)); ?>" onmouseover='<?php echo $info['mouseover']; ?>' onmousemove="MoveWindow(event)" onmouseout="HideItemInfo()" />
<?php
	}
}

?>

<!-- New inventories -->
<style type="text/css">

.item-slot {
	z-index: inherit !important;
	overflow: visible;
	position: absolute;
	width: 32px;
	height: 32px;
}

.item-slot > .icon {
	max-width: inherit;
}

.item-slot > .amount {
	bottom: -3px;
	color: white;
	position: absolute;
	left: 1px;
	z-index: 3;
	font-family: Arial;
	font-size: 12px;
	text-shadow: -1px -1px 0 #000, 1px -1px 0 #000, -1px 1px 0 #000, 1px 1px 0 #000;
}

.item-slot > .cashitem {
	background: url('/inc/img/ui/Item/Equip/cash.png') no-repeat;
	width: 13px;
	height: 13px;
	position: absolute;
	bottom: 1px;
	right: 0;
	z-index: 3;
}

.item-slot > .locked {
	background: url('/inc/img/ui/Item/Equip/lock.png') no-repeat;
	width: 13px;
	height: 14px;
	position: absolute;
	top: 0;
	right: 0;
	z-index: 3;
}

.new-inventory-container {
	background: url('/inc/img/ui/new_inventory/background.png') no-repeat;
	position: relative;
}

.new-inventory-container,
.new-inventory-container > div {
	width: 321px;
	height: 288px;
	margin: 0px 10px 10px 0px;
}

.new-inventory-container > select {
	/* Select buttan */
    height: 25px;
    left: 12px;
    padding: 2px;
    position: absolute;
    top: 24px;
    width: 296px;
	z-index: 20;
}

#inv_char {
	background: url('/inc/img/ui/new_inventory/character.png') no-repeat;
}

#inv_android {
	background: url('/inc/img/ui/new_inventory/android.png') no-repeat;
}

#inv_pet {
	background: url('/inc/img/ui/new_inventory/pet.png') no-repeat;
}

#inv_haku {
	background: url('/inc/img/ui/new_inventory/haku.png') no-repeat;
}

#inv_coordinate {
	background: url('/inc/img/ui/new_inventory/coordinate.png') no-repeat;
}

#inv_mechanic {
	background: url('/inc/img/ui/new_inventory/mechanic.png') no-repeat;
}

#inv_evan {
	background: url('/inc/img/ui/new_inventory/evan.png') no-repeat;
}

.avatar-container {
    left: 33px;
    top: 16px;
}

.avatar-container {
	width: 256px;
	height: 256px;
	position: absolute;
	background-position: center 30px;
	background-repeat: no-repeat;
}

.avatar-container span {
    display: block;
    margin-top: 182px;
    text-align: center;
	color: white;
	font-size: 12px;
}

.pet > .avatar-container {
	left: -55px;
	width: 256px;
	height: 256px;
	top: 65px;
}
.pet > .avatar-container span {
	margin-top: 156px;
}

#inv_mechanic > .avatar-container {
	background-position: center 66px;
	left: 33px;
}

#inv_mechanic > .avatar-container span {
	margin-top: 218px;
}

#inv_pet > select {
    height: 20px;
    left: 19px;
    padding: 0;
    position: absolute;
    top: 90px;
    width: 280px;
}

.new-inventory-container span.top-col {
	position: absolute;
	color: black;
	font-size: 13px;
}

.new-inventory-container .slot {
	position: absolute;
}


.top-col.lt,
.top-col.rt {
	top: 52px;
}

.top-col.lb,
.top-col.rb {
	top: 71px;
}

.top-col.lt,
.top-col.lb {
	left: 50px;
}
.top-col.rt,
.top-col.rb {
	left: 200px;
}

.evolution-system {
	background-image: url('/inc/img/ui/evolvingsystem.png');
	width: 357px;
	height: 317px;
	position: relative;
	display: inline-block;
}

.evolution-system .cores {
	height: 130px;
	left: 8px;
	overflow-x: hidden;
	overflow-y: scroll;
	position: absolute;
	top: 180px;
	width: 342px;
	margin: 0;
	padding: 0;
}

.evolution-system .selected-cores {
	height: 160px;
	left: 110px;
	position: absolute;
	top: 20px;
	width: 230px;
	overflow: hidden !important;
}

.full-bits {
	width: 420px;
}

.selected-bitcase {
	height: 70px;
    margin: 0;
    overflow: hidden;
    padding: 0;
    width: 200px;
}

.bitcase-name {
	color: white;
    font-weight: bold;
    left: 60px;
    position: absolute;
    top: 40px;
}

.bitcase {
	overflow-y: visible;
	margin: 0;
	padding: 0;
	float: left;
}

.main-bits {
	background-image: url('/inc/img/ui/bits/background.png');
	width: 248px;
	height: 229px;
	position: relative;
    overflow: hidden;
	float: left;
	margin: 0px 10px 10px 0px;
}

.bits {
    height: 141px;
    left: 2px;
    top: 70px;
    width: 231px;
	position: absolute;
}

.teleport-rock {
	background-image: url('/inc/img/ui/teleport-bg.png');
	width: 160px;
	height: 285px;
	position: relative;
	display: inline-block;
	float: left;
	margin: 10px 10px 0px 0px;
}

.teleport-rock > span {
	color: white;
	font-weight: bold;
	left: 85px;
	position: absolute;
	top: 30px;
}

.teleport-rock .locations {
	position: absolute;
	top: 75px;
	left: 12px;
	width: 137px;
	height: 197px;
	overflow: scroll;
}

.teleport-rock .locations td {
	white-space: nowrap;
	color: black;
	padding-left: 5px;
}

.teleport-rock .locations tr {
	height: 18px;
}

select {
	z-index: 20;
}

</style>
<?php
function MakeUsableSlotmap($input) {
	// Creates [slot] = [x, y] mapping from [x][y] = [slot] mapping
	$ret = array();
	for ($i = 0; $i < count($input); $i++) {
		for ($j = 0; $j < count($input[$i]); $j++) {
			$slot = $input[$i][$j];
			if ($slot < 0) continue;
			$ret[$slot] = array($i, $j);
		}
	}
	return $ret;
}

// -1 = empty
// -2 = Unknown slot
$new_inventory_slot_map = array();
$new_inventory_slot_map['character'] = MakeUsableSlotmap(array(
	array(55, 56,  1, -1, -1, -1, 53, 54, -1),
	array(52, 49,  2, -1, -1, -1,  4, 51, -1),
	array(18,  9,  3, -1, -1, -1, 11, 10, 61),
	array(14,  8,  5, -1, -1, -1, 12, 13, -2),
	array(19,  7,  6, 50, 17, 65, 15, 16, -2)
));
$new_inventory_slot_map['android'] = MakeUsableSlotmap(array(
	array(-1, -1,  8, -1, -1, -1,  1, -1, -1),
	array(-1,  0, -1, -1, -1, -1, -1, -1, -1),
	array(-1, -1,  3, -1, -1, -1, -1, -1, -1),
	array(-1,  4, -1, -1, -1, -1,  6, -1, -1),
	array(-1, -1, -1,  5, -1, -1, -1, -1, -1),
));
$new_inventory_slot_map['mechanic'] = MakeUsableSlotmap(array(
	array(-1, -1, -1, -1, -1, -1, -1, -1, -1),
	array(-1, -1, -1, -1, -1, -1, -1,  4, -1),
	array( 0, -1, -1, -1, -1, -1, -1, -1, -1),
	array(-1,  1, -1, -1, -1, -1, -1,  3, -1),
	array( 2, -1, -1, -1, -1, -1, -1, -1, -1),
));
$new_inventory_slot_map['pet'] = MakeUsableSlotmap(array(
	array(14, -1, -1, -1, -1),
	array(26, 27, -2, -2, -1),
	array(22, 23, 46, 28, 62),
	array(24, -2, 25, -2, -1),
	array(60, -2, 57, -2, -1),
));
$new_inventory_slot_map['haku'] = MakeUsableSlotmap(array(
	array(0) // lol.
));
$new_inventory_slot_map['evan'] = MakeUsableSlotmap(array(
	array( 0, -1, -1, -1, -1, -1, -1, -1,  2),
	array(-1, -1, -1, -1, -1, -1, -1, -1, -1),
	array( 1, -1, -1, -1, -1, -1, -1, -1,  3),
));
$new_inventory_slot_map['coordinate'] = MakeUsableSlotmap(array(
	array(-1, -1,  0, -1, -1, -1, -2),
	array(-1, -1, -2, -1, -1, -1, -2),
	array(-1,  1, -1, -1, -1, -1, -1),
	array( 4, -1, -2, -1, -1, -1, -1),
));

function FindItemInInventoryByItemID($inventory, $itemid) {
	foreach ($inventory as $slot => $item) {
		if ($item !== null && $item->itemid == $itemid) {
			return $item;
		}
	}
	
	return null;
}


function RenderItems(&$itemset, $slotmap_name) {
	global $new_inventory_slot_map;
	foreach ($itemset as $slot => $item) {
		$slot = abs($slot) % 100;
		if (!isset($new_inventory_slot_map[$slotmap_name][$slot])) {
			echo '<!-- NOT FOUND: '.$slotmap_name.': '.$slot.', '.$item->itemid.' ('.GetMapleStoryString("item", $item->itemid, "name", CURRENT_LOCALE).') -->'."\r\n";
			continue;
		}
		$pos = $new_inventory_slot_map[$slotmap_name][$slot];
		
		$info = GetItemDialogInfo($item, CURRENT_LOCALE, true);
		
		$itemwzinfo = GetItemWZInfo($info['iconid'], CURRENT_LOCALE);
?>
			<div class="item-slot<?php echo $info['potentials'] != 0 ? ' potential'.$info['potentials'] : ''; ?>" style="<?php InventoryPosCalc($pos[0], $pos[1]); ?>" item-name="<?php echo IGTextToWeb(GetMapleStoryString("item", $item->itemid, "name", CURRENT_LOCALE)); ?>" onmouseover='<?php echo $info['mouseover']; ?>' onmousemove="MoveWindow(event)" onmouseout="HideItemInfo()">
				<img class="icon" potential="<?php echo $info['potentials']; ?>" style="margin-top: <?php echo (32 - $itemwzinfo['info']['icon']['origin']['Y']); ?>px; margin-left: <?php  echo -$itemwzinfo['info']['icon']['origin']['X']; ?>px;" src="<?php echo GetItemIcon($item->itemid, CURRENT_LOCALE); ?>" />

<?php if ($info['iscash'] == 1): ?>
				<div class="cashitem"></div>
<?php endif; ?>
<?php if ($info['islocked'] == 1): ?>
				<div class="locked"></div>
<?php endif; ?>
			</div>
<?php
	}
}

function RenderItemsTable(&$itemset, $slots, $items_per_row, $max_slots = null) {
	for ($i = 0; $i < $slots; $i++) {
		$row = floor($i / $items_per_row);
		$col = $i % $items_per_row;
		
		if ($max_slots !== null && $max_slots <= $i) {
			// Draw 'blocked' cell
?>
			<div class="item-slot disabled-slot" style="<?php InventoryPosCalc($row, $col); ?>"></div>
<?php
		}
		elseif (isset($itemset[$i])) {
			$item = $itemset[$i];
			$isequip = $item->type == ITEM_EQUIP;
			$info = GetItemDialogInfo($item, $isequip);

			$itemIcon = '';
			if ($item->bagid != -1 || ($item->type == ITEM_PET && $item->IsExpired())) $itemIcon = 'D';
			
			$display_id = GetItemIconID($item->itemid, CURRENT_LOCALE); // For nebulites

			$itemwzinfo = GetItemWZInfo($display_id, CURRENT_LOCALE);
?>
			<div class="item-slot<?php echo $info['potentials'] != 0 ? ' potential'.$info['potentials'] : ''; ?>" style="<?php InventoryPosCalc($row, $col); ?>" item-name="<?php echo IGTextToWeb(GetMapleStoryString("item", $item->itemid, "name", CURRENT_LOCALE)); ?>" onmouseover='<?php echo $info['mouseover']; ?>' onmousemove="MoveWindow(event)" onmouseout="HideItemInfo()">
			
				<img class="icon" potential="<?php echo $info['potentials']; ?>" style="margin-top: <?php echo (32 - $itemwzinfo['info']['icon']['origin']['Y']); ?>px; margin-left: <?php  echo -$itemwzinfo['info']['icon']['origin']['X']; ?>px;" src="<?php echo GetItemIcon($display_id, CURRENT_LOCALE, $itemIcon); ?>" />
<?php if (!$isequip): ?>
				<span class="amount"><?php echo $item->amount; ?></span>
<?php endif; ?>
<?php if ($info['iscash'] == 1): ?>
				<div class="cashitem"></div>
<?php endif; ?>
<?php if ($info['islocked'] == 1): ?>
				<div class="locked"></div>
<?php endif; ?>
			</div>
<?php
		}
		else {
?>
			<div class="item-slot" style="<?php InventoryPosCalc($row, $col); ?>"></div>
<?php
		}
	}
}

function RenderItemAtPosition($item, $x, $y, $bgicon = false, $amount = true) {
	$isequip = $item->type == ITEM_EQUIP;
	$info = GetItemDialogInfo($item, $isequip);
	$pos = 'left: '.$x.'px; top: '.$y.'px;';
	$itemIcon = '';
	if ($item->bagid != -1 || ($item->type == ITEM_PET && $item->IsExpired())) $itemIcon = 'D';
	
	$display_id = GetItemIconID($item->itemid, CURRENT_LOCALE); // For nebulites

	$itemwzinfo = GetItemWZInfo($display_id, CURRENT_LOCALE);
	$uid = substr(uniqid(), -5);
?>
			<div class="item-slot<?php echo $info['potentials'] != 0 ? ' potential'.$info['potentials'] : ''; ?> <?php echo !$bgicon ? 'no-bg' : ''; ?>" style="<?php echo $pos; ?>" item-name="<?php echo IGTextToWeb(GetMapleStoryString("item", $item->itemid, "name", CURRENT_LOCALE)); ?>" onmouseover='<?php echo $info['mouseover']; ?>' onmousemove="MoveWindow(event)" onmouseout="HideItemInfo()">
				<img class="icon" potential="<?php echo $info['potentials']; ?>" style="margin-top: <?php echo (32 - $itemwzinfo['info']['icon']['origin']['Y']); ?>px; margin-left: <?php  echo -$itemwzinfo['info']['icon']['origin']['X']; ?>px;" src="<?php echo GetItemIcon($display_id, CURRENT_LOCALE, $itemIcon); ?>" />
<?php if (!$isequip && $amount): ?>
				<span class="amount"><?php echo $item->amount; ?></span>
<?php endif; ?>
<?php if ($info['iscash'] == 1): ?>
				<div class="cashitem"></div>
<?php endif; ?>
<?php if ($info['islocked'] == 1): ?>
				<div class="locked"></div>
<?php endif; ?>
			</div>
<?php
}

?>

<?php
$job_css_class = '';
$jobid = $character_info['job'];
$jobid_group = floor($jobid / 100);
if ($__is_viewing_self || !IsHiddenObject('job_equipment')) { 
	if ($jobid_group == 65 || $jobid == 6001) { $job_css_class = 'coordinate'; }
	elseif ($jobid_group == 35) { $job_css_class = 'mechanic'; }
	elseif ($jobid_group == 22 || $jobid == 2001) { $job_css_class = 'evan'; }
	elseif ($jobid_group == 41 || $jobid == 4001 || $jobid_group == 42 || $jobid == 4002) { $job_css_class = 'haku'; }
}

?>
	<div class="new-inventory-container">
		<select cur-inv="inv_char" onchange="$('#' + $(this).attr('cur-inv')).css('display', 'none'); $(this).attr('cur-inv', $(this).val()); $('#' + $(this).attr('cur-inv')).css('display', '');">

		<option value="inv_char">Character</option>
<?php 	if ($__is_viewing_self || !IsHiddenObject('equip_pet')): ?>
			<option value="inv_pet">Pet</option>
<?php	endif; ?>
<?php 	if ($__is_viewing_self || !IsHiddenObject('equip_droid')): ?>
			<option value="inv_android">Android</option>
<?php	endif; ?>
<?php	if ($job_css_class == 'mechanic'): ?>
			<option value="inv_mechanic">Mechanic</option>
<?php	elseif ($job_css_class == 'coordinate'): ?>
			<option value="inv_coordinate">Coordinate</option>
<?php	elseif ($job_css_class == 'haku'): ?>
			<option value="inv_haku">Haku</option>
<?php	elseif ($job_css_class == 'evan'): ?>
			<option value="inv_evan">Dragon</option>
<?php	endif; ?>
		</select>
		<div id="inv_char">
<?php 	MakeHideToggleButton('equip_general'); ?>
			<span class="top-col lt"><?php echo $character_info['name']; ?></span>
			<span class="top-col lb"><?php echo GetJobname($character_info['job']); ?></span>
			<span class="top-col rt"><?php echo $character_info['guildname']; ?></span>
			<span class="top-col rb"><?php echo $character_info['fame']; ?></span>
			<div class="avatar-container" style="background-image: url('<?php MakePlayerAvatar($character_info['name'], CURRENT_LOCALE, array('size' => 'big', 'onlyurl' => true)); ?>');"><span><?php echo $character_info['name']; ?></span></div>

<?php
$inv_pos_offx = 13;
$inv_pos_offy = 91;
$inv_extra_offx = $inv_extra_offy = 0;

?>
			<div id="normal_equips">
<?php
if ($__is_viewing_self || !IsHiddenObject('equip_general')):
	RenderItems($normalequips['normal'], 'character');
endif;
?>
			</div>
			<div id="cash_equips" style="display: none">
<?php
if ($__is_viewing_self || !IsHiddenObject('equip_general')):
	RenderItems($cashequips, 'character');
endif;
?>
			</div>
			
			<div style="bottom: 3px; right: 100px; position: absolute;">
				<label style="color: black;"><input type="checkbox" onchange="ShowCashEquips(this.checked)" style="display: inline;" /> Show cash items</label>
			</div>
		</div>

		
		
		
		
		
<?php 	if ($__is_viewing_self || !IsHiddenObject('equip_pet')): ?>
		<div id="inv_pet" style="display: none">
<?php 	MakeHideToggleButton('equip_pet'); ?>
			<select cur-pet="0" onchange="$('#pet_' + $(this).attr('cur-pet')).css('display', 'none'); $(this).attr('cur-pet', $(this).val()); $('#pet_' + $(this).attr('cur-pet')).css('display', '');">
				<option value="0">Pet 1</option>
				<option value="1">Pet 2</option>
				<option value="2">Pet 3</option>
			</select>
<?php

$pets = Pet::LoadPets($internal_id, CURRENT_LOCALE);

$inv_pos_offx = 136;
$inv_pos_offy = 115;
for ($i = 0; $i < 3; $i++) {
	$pet = $pets[$i];
	$isfound = $pet !== null;
?>
			<div class="pet" style="display: <?php echo $i == 0 ? 'block' : 'none'; ?>;" id="pet_<?php echo $i; ?>">
				<span class="top-col lt"><?php echo $isfound ? $pet->name : ''; ?></span>
				<span class="top-col lb"><?php echo $isfound ? $pet->level : ''; ?></span>
				<span class="top-col rt"><?php echo $isfound ? $pet->fullness : ''; ?></span>
				<span class="top-col rb"><?php echo $isfound ? $pet->closeness : ''; ?></span>

				<div class="avatar-container" style="background-image: url('<?php if ($isfound): ?>//<?php echo $locale_domain; ?>/pet/<?php echo $pet->itemid - 5000000; ?>/<?php endif; ?>');"><span><?php echo $isfound ? $pet->name : ''; ?></span></div>
<?php
	RenderItems($petequips[$i], 'pet');
?>
			</div>
<?php
}
?>
		</div>
<?php	endif; ?>

<?php 	if ($__is_viewing_self || !IsHiddenObject('equip_droid')): ?>
		<div id="inv_android" style="display: none">
<?php 	MakeHideToggleButton('equip_droid'); ?>
<?php
$droid = Android::GetAndroid($internal_id, CURRENT_LOCALE);

?>
			<span class="top-col lt"><?php echo $character_info['name']; ?></span>
			<span class="top-col lb"><?php echo GetJobname($character_info['job']); ?></span>
			<span class="top-col rt"><?php echo $character_info['guildname']; ?></span>
			<span class="top-col rb"><?php echo $character_info['fame']; ?></span>
			<div class="avatar-container" style="background-image: url('');"><span><?php echo $droid !== null ? $droid->name : ''; ?></span></div>

<?php
$inv_pos_offx = 12;
$inv_pos_offy = 90;
$inv_extra_offx = $inv_extra_offy = 0;
RenderItems($normalequips['Android'], 'android');
?>
		</div>
<?php	endif; ?>



<?php	if ($job_css_class == 'coordinate'): ?>
		<div id="inv_coordinate" style="display: none">
<?php 	MakeHideToggleButton('job_equipment'); ?>
			<span class="top-col lt"><?php echo $character_info['name']; ?></span>
			<span class="top-col lb"><?php echo GetJobname($character_info['job']); ?></span>
			<span class="top-col rt"><?php echo $character_info['guildname']; ?></span>
			<span class="top-col rb"><?php echo $character_info['fame']; ?></span>
			<div class="avatar-container" style="background-image: url('<?php MakePlayerAvatar($character_info['name'], CURRENT_LOCALE, array('size' => 'big', 'onlyurl' => true)); ?>');"><span><?php echo $character_info['name']; ?></span></div>

<?php
$inv_pos_offx = 14;
$inv_pos_offy = 92;
$inv_extra_offx = $inv_extra_offy = 0;
RenderItems($normalequips['Coordinate'], 'coordinate');
?>
		</div>


<?php	elseif ($job_css_class == 'mechanic'): ?>
		<div id="inv_mechanic" style="display: none">
<?php 	MakeHideToggleButton('job_equipment'); ?>
			<span class="top-col lt"><?php echo $character_info['name']; ?></span>
			<span class="top-col lb"><?php echo GetJobname($character_info['job']); ?></span>
			<span class="top-col rt"><?php echo $character_info['guildname']; ?></span>
			<span class="top-col rb"><?php echo $character_info['fame']; ?></span>
			<div class="avatar-container" style="background-image: url('<?php MakePlayerAvatar($character_info['name'], CURRENT_LOCALE, array('size' => 'big', 'onlyurl' => true)); ?>');"><span><?php echo $character_info['name']; ?></span></div>

<?php
$inv_pos_offx = 18;
$inv_pos_offy = 94;
$inv_extra_offx = $inv_extra_offy = 0;

RenderItems($normalequips['Mechanic'], 'mechanic');
?>
		</div>

<?php	elseif ($job_css_class == 'haku'): ?>
		<div id="inv_haku" style="display: none">
<?php 	MakeHideToggleButton('job_equipment'); ?>
<?php
$inv_pos_offx = 159;
$inv_pos_offy = 169;
$inv_extra_offx = $inv_extra_offy = 0;
RenderItems($normalequips['Haku'], 'haku');
?>
		</div>
		

<?php	elseif ($job_css_class == 'evan'): ?>
		<div id="inv_evan" style="display: none">
<?php 	MakeHideToggleButton('job_equipment'); ?>
<?php
$inv_pos_offx = 9;
$inv_pos_offy = 106;
$inv_extra_offx = $inv_extra_offy = 0;
RenderItems($normalequips['Evan'], 'evan');
?>
		</div>
<?php	endif; ?>
	</div>
	
	
	<br /><br /><br />

<?php if ($__is_viewing_self || !IsHiddenObject('inventories')): ?>
	<div id="inventories">
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


$inv_pos_offx = 5; // Diff offsets
$inv_pos_offy = 2;
$inv_extra_offx = $inv_extra_offy = 2;

for ($inv = 0; $inv < 5; $inv++) {
	$inv1 = $inventory->GetInventory($inv);
?>
		<div class="character-brick inventory scrollable" id="inventory_<?php echo $inv; ?>" style="display: <?php echo $inv == 0 ? 'block' : 'none'; ?>; padding: 5px  !important;">
<?php
	RenderItemsTable($inv1, max(4 * 8, ceil(count($inv1) / 8) * 8), 8, count($inv1));
?>
		</div>
<?php
}
?>
		<span class="mesos"><?php echo number_format($character_info['mesos']); ?></span>
		<span class="maplepoints">0</span>

	</div>
<?php endif; ?>






<?php if ($__is_viewing_self || !IsHiddenObject('bits')): ?>
	<div class="full-bits">
<?php 	MakeHideToggleButton('bits'); ?>
<?php


	$bits_quest = Quest::GetQuest($internal_id, CURRENT_LOCALE, 7022, true);
	$bits_info = null;

	// c=0;e=1;l=3;s=12
	// c = Type of case (3090000)
	// e = Equipped. Is removed when unequipped
	// l = Lines
	// s = Slots
	if ($bits_quest !== null && !$bits_quest->IsCompleted() && isset($bits_quest->data['e']) && $bits_quest->data['e'] == 1):
		$bits_info = $bits_quest->data;
		$bits_rows = (int)$bits_info['l'];
		$bits_cols = (int)$bits_info['s'] / $bits_rows;
		
		// Get size
		$url = "http://".$domain."/ui/bits/".$bits_rows."/".$bits_cols."/";
		$width_height_array = json_decode(file_get_contents($url.'?onlysize'), true);
	endif;
?>
		<div class="main-bits">
			<div class="inventory selected-bitcase">
<?php
	$bitcase_name = '';
	if ($bits_info !== null) {
		$item = FindItemInInventoryByItemID($inventory->GetInventory(2), 3090000 + $bits_info['c']);
		if ($item !== null) {
			RenderItemAtPosition($item, 17, 33, false, false);
			
			$bitcase_name = IGTextToWeb(GetMapleStoryString('item', $item->itemid, 'name', CURRENT_LOCALE));
		}
	}
?>
				<span class="bitcase-name"><?php echo $bitcase_name; ?></span>
			</div>
			<div class="inventory bits">
<?php

	$inv_pos_offx = 5; // Diff offsets
	$inv_pos_offy = 2;
	$inv_extra_offx = $inv_extra_offy = 2;

	RenderItemsTable($normalequips['Bits'], max(6 * 4, ceil(count($normalequips['Bits']) / 6) * 6), 6);

?>
			</div>
		</div>
<?php
	if ($bits_info !== null):
?>
		<div class="inventory bitcase" style="width: <?php echo $width_height_array['width']; ?>px; height: <?php echo $width_height_array['height']; ?>px; background-image: url('<?php echo $url; ?>');">

<?php

		$inv_pos_offx = 11; // Diff offsets
		$inv_pos_offy = 24;
		$inv_extra_offx = $inv_extra_offy = 4;
		$inv_extra_offy = 2;
			
		RenderItemsTable($normalequips['BitsCaseBits'], $bits_rows * $bits_cols, $bits_cols);

?>
		</div>
<?php
	endif;
?>
	</div>
<?php
endif;

?>
	
	
<?php if ($character_info['level'] >= 100 && ($__is_viewing_self || !IsHiddenObject('evo_rocks'))): ?>
	<div class="evolution-system">
<?php 	MakeHideToggleButton('evo_rocks'); ?>
<?php

	$q = $__char_db->query("
SELECT
	`card`, `level`, `block`, `index`
FROM
	evolution_levels
WHERE
	`character_id` = ".$internal_id."
GROUP BY
	`card`
ORDER BY
	`card` ASC,
	`index` ASC
");

	$cores = array();
	$cores[1] = array();
	$cores[2] = array();
	while ($row = $q->fetch_assoc()) {
		$cores[(int)$row['block']][(int)$row['index']] = (object)array('itemid' => $row['card'], 'amount' => $row['level'], 'expires' => 3439756800, 'type' => ITEM_RECHARGE, 'bagid' => -1);
	}
	
	$selected_core_positions = array();
	$selected_core_positions[0] = array(43, 12);
	// $selected_core_positions[1] = array(17, 17);
	$selected_core_positions[2] = array(86, 83);
	//$selected_core_positions[3] = array(86, 83);
	//$selected_core_positions[4] = array(86, 83);
	
	
	// Right list
	$selected_core_positions[7] = array(190, 8);
	$selected_core_positions[8] = array(190, 58);
	$selected_core_positions[9] = array(190, 108);
?>
		<div class="selected-cores inventory">
<?php
	foreach ($cores[1] as $slot => $item) {
		if (!isset($selected_core_positions[$slot])) continue;
		$pos = $selected_core_positions[$slot];
		RenderItemAtPosition($item, $pos[0], $pos[1]);
	}
?>
		</div>
		<div class="cores inventory">

<?php
$inv_pos_offx = 3; // Diff offsets
$inv_pos_offy = 2;
$inv_extra_offx = $inv_extra_offy = 8;
	
	
	RenderItemsTable($cores[2], max(3 * 8, ceil(count($cores[2]) / 8) * 8), 8);
?>
		
		</div>
	</div>
<?php endif; ?>
<br/>
<?php if ($__is_viewing_self || !IsHiddenObject('teleport_rocks')): ?>
<?php 	MakeHideToggleButton('teleport_rocks'); ?>
<?php
	$q = $__char_db->query("
SELECT
	*
FROM
	teleport_rock_locations
WHERE
	character_id = ".$internal_id."
");

	$lastgroup = '';
	$curgroup = '';
	$row = $q->fetch_row();
	$rocks = array();
	for ($i = 1; $i < 41 + 1; $i++) // 41 rocks + 1 offset
		$rocks[] = $row[$i];

	foreach ($rocks as $index => $map) {
		if ($map == 999999999) continue;
		if ($index < 5) $curgroup = 'Regular';
		elseif ($index < 5 + 10) $curgroup = 'VIP';
		elseif ($index < 5 + 10 + 13) $curgroup = 'Hyper';
		elseif ($index < 5 + 10 + 13 + 13) $curgroup = 'Hyper';
		
		if ($lastgroup != $curgroup) {
			if ($lastgroup != '') {
?>
				</table>
			</div>
		</div>
<?php
			}
?>
		<div class="teleport-rock">
			<span><?php echo $curgroup; ?></span>
			<div class="locations">
				<table border="0" cellpadding="0" cellspacing="0">
<?php
			$lastgroup = $curgroup;
		}
?>
					<tr>
						<td><?php echo GetMapname($map, CURRENT_LOCALE); ?></td>
					</tr>
<?php
	}
	
	if ($curgroup != '') {
?>
				</table>
			</div>
		</div>
<?php
	}
?>
<?php endif; ?>


<script>
var descriptions = <?php echo json_encode($IDlist); ?>;
var potentialDescriptions = <?php echo json_encode($PotentialList); ?>;
var nebuliteInfo = <?php echo json_encode($NebuliteList); ?>;
</script>

<div id="item_info" style="display: none;">
	<div class="top"></div>
	
	<center id="item_info_stars"></center>
	<div id="item_info_title"></div>
	
	<div id="item_info_extra"></div>
	
	<div class="dotline"></div>
	
	<div class="icon_holder">
		<img id="item_info_icon" src="" title="" />
		<div class="cover"></div>
	</div>
	
	<div id="item_info_description"></div>
	
	<div class="item_req_stats">
		<div class="req-block">
			<div type="level">
				<div class="digit" nr="1"></div>
				<div class="digit" nr="0"></div>
				<div class="digit" nr="0"></div>
			</div>
			
			<br />
			
			<div style="float: right;">
				<div type="int">
					<div class="digit" nr="9"></div>
					<div class="digit" nr="0"></div>
					<div class="digit" nr="0"></div>
				</div>
				<div type="luk">
					<div class="digit" nr="1"></div>
					<div class="digit" nr="0"></div>
					<div class="digit" nr="4"></div>
				</div>
			</div>
			<div type="str">
				<div class="digit" nr="3"></div>
				<div class="digit" nr="0"></div>
				<div class="digit" nr="0"></div>
			</div>
			<div type="dex">
				<div class="digit" nr="8"></div>
				<div class="digit" nr="0"></div>
				<div class="digit" nr="0"></div>
			</div>
		</div>
	</div>
	<div id="req_job_list">
		<div class="req_job" job="0"></div>
		<div class="req_job" job="1"></div>
		<div class="req_job" job="2"></div>
		<div class="req_job" job="3"></div>
		<div class="req_job" job="4"></div>
		<div class="req_job" job="5"></div>
		<center class="req_job only_job"></center>
	</div>
	<div class="item_stats" id="item_stats_block">
		<div class="dotline"></div>
<?php
foreach ($optionlist as $option => $desc) {
?>
		<p id="item_info_row_<?php echo strtolower($option); ?>">
			<span width="150px"><?php echo $desc; ?></span>
			<span id="item_info_<?php echo strtolower($option); ?>"></span>
		</p>
<?php
}
?>
	</div>
	<div class="item_potential_stats" id="item_info_potentials">
		<div class="dotline"></div>
		<table border="0" tablepadding="3" tablespacing="3" id="potentials">
		</table>
	</div>
	<div class="item_potential_stats" id="item_nebulite_info_block" style="display: none;">
		<div class="dotline"></div>
		<span id="nebulite_info"></span>
	</div>
	<div class="item_potential_stats" id="item_info_bonus_potentials">
		<div class="dotline"></div>
		<table border="0" tablepadding="3" tablespacing="3" id="bonus_potentials">
		</table>
	</div>
	<div id="extra_item_info"></div>
	<div class="bottom"></div>
</div>

<hr />

<?php if ($__is_viewing_self || !IsHiddenObject('skills')): ?>
<style type="text/css">
#skill_list {
	background-image: url('//<?php echo $locale_domain; ?>/inc/img/ui/skill/bg_final.png');
}

.skill_line {
	background-image: url('//<?php echo $locale_domain; ?>/inc/img/ui/skill/line.png');
}

.skill {
	background-image: url('//<?php echo $locale_domain; ?>/inc/img/ui/skill/skill.png');
}
</style>

<?php
	// Initialize SP
	
	$q = $__char_db->query("
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
	
	
	$q = $__char_db->query("
SELECT
	skillid, level, maxlevel, ceil((expires/10000000) - 11644473600) as expires
FROM
	skills
WHERE
	character_id = ".$internal_id."
	AND
	FLOOR(skillid / 10000) < 9200
ORDER BY
	skillid / 1000 ASC
");
	
	// $BlessingOfTheFairy = "A spirit with the power of #c%s# strengthens the character. Increases by one level every time #c%s# goes up 10 levels. With the Empress's Blessing, the higher increase is applied.";
	
	$lastgroup = -1;
	$first_skill = true;
	
	
	
if (false):
	
	$groups = array();
	$i = 0;
	$jobtreeid = 0;
	$skills = array();
	while ($row = $q->fetch_assoc()) {
		$name = GetMapleStoryString('skill', $row['skillid'], 'name', CURRENT_LOCALE);
		if ($name == NULL) continue;
		$potentialMaxLevel = GetMapleStoryString('skill', $row['skillid'], 'mlvl', CURRENT_LOCALE);
		$block = floor($row['skillid'] / 10000);
		
		$extra = '';
		
		if ($row['maxlevel'] === NULL) {
			$row['maxlevel'] = ($potentialMaxLevel == NULL ? '-' : $potentialMaxLevel);
		}
		
		$skills[$block][$row['skillid']] = array($row['level'], $row['maxlevel']);
	}
	
	// Build skill groups.
	
	$skill_pages = array();
	for ($i = 0; $i < 5; $i++) {
		$skill_pages[$i] = array(
			'attack' => array(),
			'passive' => array(),
			'active' => array(),
		);
	}
	
?>

<div class="skills">
<?php 	MakeHideToggleButton('skills'); ?>
	

</div>
<?php
endif;
?>
<div id="skill_list">
<?php 	MakeHideToggleButton('skills'); ?>
<?php
	
	$q->data_seek(0);
	
	while ($row = $q->fetch_assoc()) {
		$name = GetMapleStoryString('skill', $row['skillid'], 'name', CURRENT_LOCALE);
		if ($name == NULL) continue;
		$potentialMaxLevel = GetMapleStoryString('skill', $row['skillid'], 'mlvl', CURRENT_LOCALE);
		$block = floor($row['skillid'] / 10000);
		if ($lastgroup != $block && $lastgroup < 9200) {
			$first_skill = true;
			if ($lastgroup != -1) {
?>
			</div>
<?php
			}
			$lastgroup = $block;
			$book = $block >= 9200 ? 'Profession info' : GetMapleStoryString("skill", $lastgroup, "bname", CURRENT_LOCALE);
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
			$playername = GetCharacterName($row['level'], CURRENT_LOCALE);
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
	
	
	<br /><br />
<?php endif; ?>
	
<?php if (!EMS && ($__is_viewing_self || !IsHiddenObject('familiars'))): ?>
<?php 	MakeHideToggleButton('familiars'); ?>
	<p class="lead">Familiars</p>
	<table cellspacing="10" cellpadding="6">
<?php
// Familiars
	$q = $__char_db->query("
SELECT
	IF(f.name = '', (
		SELECT `value` FROM strings WHERE objectid = fi.mob_id AND objecttype = 'mob' AND `key` = 'name'
		), f.name) AS `name`,
	fi.familiar_id
FROM
	familiars f
LEFT JOIN
	phpvana_familiar_info fi
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

<?php
}
// $__char_db->GetRanQueries();
require_once __DIR__.'/../inc/footer.php';
?>