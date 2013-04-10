<?php
require_once __DIR__.'/inc/header.php';
require_once __DIR__.'/inc/job_list.php';
require_once __DIR__.'/inc/exp_table.php';

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
	name = '".$__database->real_escape_string($_GET['name'])."'
");

if ($q->num_rows == 0) {
	$q->free();
?>
<center>
	<img src="//<?php echo $domain; ?>/inc/img/no-character.gif" />
	<p>Character not found! The character may have been removed or hidden.</p>
</center>
<?php
	require_once __DIR__.'/inc/footer.php';
	die;
}

$character_info = $q->fetch_assoc();

// Check character status
$friend_status = $_loggedin ? GetFriendStatus($_loginaccount->GetID(), GetCharacterAccountId($character_info['id'])) : 'NO_FRIENDS';
$status = GetCharacterStatus($character_info['id']);

if ($status == 1 && (!$_loggedin || ($_loggedin && $friend_status != 'FRIENDS' && $friend_status != 'FOREVER_ALONE'))) {
?>
<center>
	<img src="//<?php echo $domain; ?>/inc/img/no-character.gif" />
	<p>Only friends are allowed to view this character!</p>
</center>
<?php
	require_once __DIR__.'/inc/footer.php';
	die;
}
elseif ($status == 2 && ($_loggedin && !IsOwnAccount())) {
	// displays the same error as not found to not tell if exists or not.
?>
<center>
	<img src="//<?php echo $domain; ?>/inc/img/no-character.gif" />
	<p>Character not found! The character may have been removed or hidden.</p>
</center>
<?php
    require_once __DIR__.'/inc/footer.php';
	die;
}
else {
	$account = Account::Load($character_info['account_id']);
	$internal_id = $character_info['internal_id'];
	$stat_addition = GetCorrectStat($internal_id);
	
	$channelid = $character_info['channel_id'];
	if ($channelid == -1) $channelid = 'Unknown';
	else $channelid++; // 1 = 0
	
	
	// Some quick count queries
	$qcount = $__database->query("
SELECT
	(SELECT COUNT(DISTINCT a.questid) FROM (SELECT questid FROM quests_done WHERE character_id = ".$internal_id." UNION ALL SELECT questid FROM quests_done_party WHERE character_id = ".$internal_id.") a) as `quests_done`,
	(SELECT COUNT(DISTINCT a.questid) FROM (SELECT questid FROM quests_running WHERE character_id = ".$internal_id." UNION ALL SELECT questid FROM quests_running_party WHERE character_id = ".$internal_id.") a) as `quests_left`,
	(SELECT COUNT(*) FROM skills WHERE character_id = ".$internal_id.") as `skills`
");
	$statistics = $qcount->fetch_assoc();
	$qcount->free();
?>
<div class="row">
	<div class="span12">
		<a href="//<?php echo $account->GetUsername(); ?>.<?php echo $domain; ?>/" class="btn btn-mini pull-right" style="margin-bottom: 10px">Return to <?php echo $account->GetNickName(); ?>'s Profile</a>	
	</div>
</div>

<div class="row">
	<div class="span3">
		<img src="//mapler.me/avatar/<?php echo $character_info['name']; ?>" class="avatar" /><br/>
		<p class="name"><?php echo $character_info['name']; ?><br/>
			<small class="name_extra" style="margin-top:10px;">Level <?php echo $character_info['level']; ?> <?php echo GetJobname($character_info['job']); ?></small>
		</p>
		<hr/>
		<p class="side"><i class="icon-home faded"></i> <?php echo GetMapname($character_info['map']); ?></p>
		<p class="side"><i class="icon-globe faded"></i> <?php echo $character_info['world_name']; ?></p>
		<p class="side"><i class="icon-map-marker faded"></i> Channel <?php echo $channelid; ?></p>
		<p class="side"><i class="icon-eye-open faded"></i> Last seen <?php echo time_elapsed_string($character_info['secs_since']); ?> ago</p>
		<hr/>
		<p class="side"><i class="icon-tasks"></i> <?php echo $statistics['quests_done']; ?> quests completed</p>
		<p class="side"><i class="icon-tasks faded"></i> <?php echo $statistics['quests_left']; ?> quests in progress</p>
		<p class="side"><i class="icon-briefcase faded"></i> <?php echo $statistics['skills']; ?> skills learned</p>
		<hr/>
		
		<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
		
		<a href="https://twitter.com/share" class="twitter-share-button" data-text="Check out the character <?php echo $character_info['name']; ?> on #maplerme!" data-dnt="true"></a>
				
		<div class="fb-like" style="position:relative;right:20px;" data-href="http://<?php echo $domain; ?>/player/<?php echo $character_info['name']; ?>" data-send="false" data-layout="button_count" data-width="450" data-show-faces="false"></div>
				
		<hr/>
		<p class="side"><i class="icon-user faded"></i> <a href="//<?php echo $domain; ?>/avatar/<?php echo $character_info['name']; ?>">Avatar</a></p>
		<p class="side"><i class="icon-heart faded"></i>  <a href="//<?php echo $domain; ?>/card/<?php echo $character_info['name']; ?>">Player Card</a></p>
		<p class="side"><i class="icon-th-list faded"></i>  <a href="//<?php echo $domain; ?>/infopic/<?php echo $character_info['name']; ?>">Statistics</a></p>
	</div>
	
<?php	
if (!$_loggedin) {
?>
	<div class="span9" style="margin-left:10px;">
		<p class="status" style="margin-top:0px;"><i class="icon-ban-circle faded"></i> To view more information and equipment for <?php echo $character_info['name']; ?>, please <a href="//<?php echo $domain; ?>/login/">login</a> or <a href="//<?php echo $domain; ?>/signup/">register!</a></p>
	</div>
</div>
<?php
	require_once __DIR__.'/inc/footer.php';
	die();
}

else if ($status == 2 && ($_loggedin && IsOwnAccount())) {
	// displays the same error as not found to not tell if exists or not.
?>
<div class="span9" style="margin-left:10px;">
		<p class="status" style="margin-top:0px;"><i class="icon-ok faded"></i> This character is currently only viewable by yourself (hidden).</p>
	</div>
<?php
}
?>
	
	<div class="span9" style="margin-left:10px;">
		<p class="lead"><?php echo $character_info['name']; ?>'s Equipment</p>
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
$optionlist['slots'] = 'Upgrades available : ';
$optionlist['scrolls'] = 'Number of upgrades done : ';


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


function GetItemDialogInfo($item, $isequip) {
	global $PotentialList, $IDlist, $reqlist, $optionlist;
	
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
	$reqpop = ValueOrDefault($stats['reqpop'], "'-'");
	
	$arguments = 'SetItemInfo(event, this, ';
	$arguments .= $item->itemid.','.($isequip ? 1 : 0).', ';
	$arguments .= ValueOrDefault($stats['reqjob'], 0).', ';
	
	
	//  All options.
	$args = 25;
	if ($isequip) {
		$arguments .= $reqlevel.', ';
		$arguments .= $reqstr.', ';
		$arguments .= $reqdex.', ';
		$arguments .= $reqint.', ';
		$arguments .= $reqluk.', ';
		$arguments .= $reqpop.', ';
		$arguments .= ($item->itemlevel == NULL ? "'-'" : $item->itemlevel).', ';
		$arguments .= ($item->itemexp == NULL ? "'-'" : "'".round(GetExpPercentage($item->itemlevel + 1, $item->itemexp))."%'").', ';
		$arguments .= $item->str.', ';
		$arguments .= $item->dex.', ';
		$arguments .= $item->int.', ';
		$arguments .= $item->luk.', ';
		$arguments .= $item->maxhp.', ';
		$arguments .= $item->maxmp.', ';
		$arguments .= $item->weaponatt.', ';
		$arguments .= $item->weapondef.', ';
		$arguments .= $item->magicatt.', ';
		$arguments .= $item->magicdef.', ';
		$arguments .= $item->acc.', ';
		$arguments .= $item->avo.', ';
		$arguments .= $item->hands.', ';
		$arguments .= $item->jump.', ';
		$arguments .= $item->speed.', ';
		$arguments .= $item->slots.', ';
		$arguments .= $item->scrolls.', ';
	}
	else {
		for ($i = 1; $i <= $args; $i++)
			$arguments .= '0, ';
	}

	$arguments .= "'".GetSystemTimeFromFileTime($item->expires)."', ";
	$arguments .= ($isequip ? $item->HasLock() : 0).", ";
	$arguments .= ($isequip ? $item->HasSpikes() : 0).", ";
	$arguments .= ($isequip ? $item->HasColdProtection() : 0).", ";
	$arguments .= $tradeblock.", ";
	$arguments .= ValueOrDefault($stats['quest'], 0).", ";
	$arguments .= ($isequip ? $item->IsKarmad() : 0).", ";
	$arguments .= ($isequip ? $item->socket3 : 0).", "; // Seems to be sort of potential flag (1 = locked, 12 = unlocked)
	$arguments .= ($isequip ? $item->potential1 : 0).", ";
	$arguments .= ($isequip ? $item->potential2 : 0).", ";
	$arguments .= ($isequip ? $item->potential3 : 0).", ";
	$arguments .= ($isequip ? $item->potential4 : 0).", ";
	$arguments .= ($isequip ? $item->potential5 : 0).", ";
	$arguments .= ValueOrDefault($stats['only'], 0).");";
	
	$potential = 0;
	if ($isequip && $item->socket3 == 1)
		$potential = 1; // Default color
	else {
		if ($isequip && $item->potential1 != 0) $potential++;
		if ($isequip && $item->potential2 != 0) $potential++;
		if ($isequip && $item->potential3 != 0) $potential++;
		if ($isequip && $item->potential4 != 0) $potential++;
		if ($isequip && $item->potential5 != 0) $potential++;
	}
	
	return array('mouseover' => $arguments, 'potentials' => $potential);
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


.inventory div {
	background-image: url('//<?php echo $domain; ?>/inc/img/ui/Item/item_bg.png');
}

#inventories {
	background-image: url('//<?php echo $domain; ?>/inc/img/ui/Item/final_ui.png');
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
$cashequips = array();
$cashequips['Coordinate'] = array();
$cashequips['Totem'] = array();
$cashequips['Android'] = array();
$cashequips['Mechanic'] = array();
$cashequips['Evan'] = array();
$cashequips['normal'] = array();

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
		if ($orislot > -100) 		$normalequips[$orislot] = $item;
		elseif ($orislot <= -5000) 	$cashequips['Coordinate'][$orislot] = $item;
		elseif ($orislot <= -1300) 	$cashequips['Totem'][$orislot] = $item;
		elseif ($orislot <= -1200) 	$cashequips['Android'][$orislot] = $item;
		elseif ($orislot <= -1100) 	$cashequips['Mechanic'][$orislot] = $item;
		elseif ($orislot <= -1000) 	$cashequips['Evan'][$orislot] = $item;
		elseif ($orislot <= -100) 	$cashequips['normal'][$orislot] = $item;
	}
}

?>

<div class="row">
	<div class="span3" style="width: 184px;">
		<div class="character_equips">
			<div id="normal_equips">

<?php
foreach ($normalequips as $slot => $item) {
	$slot = abs($slot);
	
	$info = GetItemDialogInfo($item, true);
	
	$itemwzinfo = GetItemWZInfo($item->itemid);
	
	
	if ($info['potentials'] != 0) {
?>
				<div class="item-icon slot<?php echo $slot; ?> potential<?php echo $info['potentials']; ?>" style="position: absolute;"></div>
<?php
	}
?>
				<img class="item-icon slot<?php echo $slot; ?>" potential="<?php echo $info['potentials']; ?>" style="margin-top: <?php echo (32 - $itemwzinfo['info_icon_origin_Y']); ?>px; margin-left: <?php echo -$itemwzinfo['info_icon_origin_X']; ?>px;" src="<?php echo GetItemIcon($item->itemid); ?>" item-name="<?php echo IGTextToWeb(GetMapleStoryString("item", $item->itemid, "name")); ?>" onmouseover="<?php echo $info['mouseover']; ?>" onmousemove="MoveWindow(event)" onmouseout="HideItemInfo()" />
<?php
}
?>
			</div>
			<div id="cash_equips">

<?php
foreach ($cashequips['normal'] as $slot => $item) {
	$slot = abs($slot) - 100;
	
	$info = GetItemDialogInfo($item, true);
	
	$itemwzinfo = GetItemWZInfo($item->itemid);
	
	
	if ($info['potentials'] != 0) {
?>
				<div class="item-icon slot<?php echo $slot; ?> potential<?php echo $info['potentials']; ?>" style="position: absolute;"></div>
<?php
	}
?>
				<img class="item-icon slot<?php echo $slot; ?>" potential="<?php echo $info['potentials']; ?>" style="margin-top: <?php echo (32 - $itemwzinfo['info_icon_origin_Y']); ?>px; margin-left: <?php echo -$itemwzinfo['info_icon_origin_X']; ?>px;" src="<?php echo GetItemIcon($item->itemid); ?>" item-name="<?php echo IGTextToWeb(GetMapleStoryString("item", $item->itemid, "name")); ?>" onmouseover="<?php echo $info['mouseover']; ?>" onmousemove="MoveWindow(event)" onmouseout="HideItemInfo()" />
<?php
}
?>
			</div>
			<div style="bottom: 3px; right: 20px; position: absolute;">
				<label><input type="checkbox" onchange="ShowCashEquips(this.checked)" style="display: inline;" /> Show cash items</label>
			</div>
		</div>
	</div>

	<div class="span4" id="inventories">
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
		<div class="character-brick inventory" id="inventory_<?php echo $inv; ?>" style="display: <?php echo $inv == 0 ? 'block' : 'none'; ?>; padding: 5px  !important;">
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
			
			$display_id = GetItemIconID($item->itemid);

			$itemwzinfo = GetItemWZInfo($display_id);

?>
			<div class="item-icon <?php echo $info['potentials'] != 0 ? ' potential'.$info['potentials'] : ''; ?>" style="<?php InventoryPosCalc($row, $col); ?>"  onmouseover="document.getElementById('item_<?php echo $inv; ?>_<?php echo $i; ?>').onmouseover(event)" onmouseout="document.getElementById('item_<?php echo $inv; ?>_<?php echo $i; ?>').onmouseout(event)" onmousemove="document.getElementById('item_<?php echo $inv; ?>_<?php echo $i; ?>').onmousemove(event)"></div>
			<img class="item-icon" id="item_<?php echo $inv; ?>_<?php echo $i; ?>" potential="<?php echo $info['potentials']; ?>" style="<?php InventoryPosCalc($row, $col); ?> margin-top: <?php echo (32 - $itemwzinfo['info_icon_origin_Y']); ?>px; margin-left: <?php echo -$itemwzinfo['info_icon_origin_X']; ?>px;" src="<?php echo GetItemIcon($display_id, $itemIcon); ?>" item-name="<?php echo IGTextToWeb(GetMapleStoryString("item", $item->itemid, "name")); ?>" onmouseover="<?php echo $info['mouseover']; ?>" onmousemove="MoveWindow(event)" onmouseout="HideItemInfo()" />
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


	<div class="span3" style="width: 151px;">
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
				<div class="pet_inventory" style="display: <?php echo $inv == 0 ? 'block' : 'none'; ?>;" id="pet_<?php echo $i; ?>">
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
					<img class="item-icon slot<?php echo $slot; ?>" potential="<?php echo $info['potentials']; ?>" style="margin-top: <?php echo (32 - $itemwzinfo['info_icon_origin_Y']); ?>px; margin-left: <?php echo -$itemwzinfo['info_icon_origin_X']; ?>px;" src="<?php echo GetItemIcon($item->itemid); ?>" item-name="<?php echo IGTextToWeb(GetMapleStoryString("item", $item->itemid, "name")); ?>" onmouseover="<?php echo $info['mouseover']; ?>" onmousemove="MoveWindow(event)" onmouseout="HideItemInfo()" />
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
	
</div>

<script>
var descriptions = <?php echo json_encode($IDlist); ?>;
var potentialDescriptions = <?php echo json_encode($PotentialList); ?>;

<?php

if (false): // set to true to print this stuff

?>
function SetItemInfo(event, obj, itemid, isequip, reqjob, <?php
foreach ($reqlist as $option => $desc) {
	echo $option.", ";
}
foreach ($optionlist as $option => $desc) {
	echo $option.", ";
}
?>
expires, f_lock, f_spikes, f_coldprotection, f_tradeblock, questitem, f_karmad, potentialflag, potential1, potential2, potential3, potential4, potential5, one) {
	document.getElementById('item_info_title').innerHTML = obj.getAttribute('item-name');
	document.getElementById('item_info_icon').src = obj.src;
	
<?php
foreach ($reqlist as $option => $desc) {
?>
	document.getElementById('item_info_req_row_<?php echo strtolower($option); ?>').style.display = (!isequip && (<?php echo $option; ?> == '' || <?php echo $option; ?> == 0)) ? 'none' : '';
	document.getElementById('item_info_req_<?php echo strtolower($option); ?>').innerHTML = <?php echo $option; ?>;
	
<?php
}
?>


<?php
foreach ($optionlist as $option => $desc) {
	if ($option == 'scrolls') continue;
?>
	document.getElementById('item_info_row_<?php echo strtolower($option); ?>').style.display = (<?php echo $option; ?> == 0 || <?php echo $option; ?> == '') ? 'none' : '';
	document.getElementById('item_info_<?php echo strtolower($option); ?>').innerHTML = <?php echo $option; ?>;
	
<?php
}
?>
	
	var description = descriptions[itemid];
	
	if (description != '') {
		document.getElementById('item_info_description').style.display = '';
		document.getElementById('item_info_description').innerHTML = description;
	}
	else {
		document.getElementById('item_info_description').style.display = 'none';
	}
	
	var extrainfo = '';
	
	if (one)
		extrainfo += '<span>One of a Kind</span>';
	
	if (questitem)
		extrainfo += '<span>Quest item</span>';
	
	if (f_lock)
		extrainfo += '<span>Sealed untill ' + expires + '</span>';
	else if (expires != '')
		extrainfo += '<span>Expires on ' + expires + '</span>';
	if (f_spikes)
		extrainfo += '<span>Prevents slipping</span>';
	if (f_coldprotection)
		extrainfo += '<span>Cold prevention</span>';
	if (f_tradeblock) {
		var tradeInfo = 'Untradable';
		switch (f_tradeblock) {
			case 0x10: tradeInfo = 'Use the Sharing Tag to move an item to another character on the same account once.'; break;
			case 0x20: tradeInfo = 'Use the Scissors of Karma to enable an item to be traded one time'; break;
			case 0x21: tradeInfo = 'Use the Platinum Scissors of Karma to enable an item to be traded one time'; break;
			case 0x30: tradeInfo = 'Trade disabled when equipped'; break;
			case 0x10: tradeInfo = 'Can be traded once within an account (Cannot be traded after being moved)'; break;
		}
		extrainfo += '<span>' + tradeInfo + '</span>';
	}
	if (f_karmad)
		extrainfo += '<span>1 time trading (karma\'d)</span>';

	//extrainfo += '<span>ITEMID ' + itemid + '</span>';
	
	
	document.getElementById('item_info_extra').innerHTML = extrainfo;
	document.getElementById('item_info_extra').style.display = extrainfo == '' ? 'none' : 'block';
	
	// Classes
	
	if (reqjob == 0) reqjob = 255; // All classes
	SetJob(0, reqjob, 0x80); // Beginner
	SetJob(1, reqjob, 0x01); // Warrior
	SetJob(2, reqjob, 0x02); // Magician
	SetJob(3, reqjob, 0x04); // Bowman
	SetJob(4, reqjob, 0x08); // Thief
	SetJob(5, reqjob, 0x10); // Pirate
	
	document.getElementById('potentials').innerHTML = ""; // Clear potentials
	
	var potentiallevel = Math.round(reqlevel / 10);
	if (potentiallevel == 0) potentiallevel = 1;
	
	if (potentialflag == 1) { // 12 = unlocked...?
		var row = document.getElementById('potentials').insertRow(-1);
		row.innerHTML = '<tr> <td width="150px">Hidden Potential.</td> </tr>';
	}
	
<?php
for ($i = 1; $i <= 5; $i++) {
?>
	if (potential<?php echo $i;?> != 0) {
		var potentialinfo = potentialDescriptions[potential<?php echo $i;?>];
		if (potentialinfo.name != null) {
			var leveldata = potentialinfo.levels[potentiallevel];
			
			var result = potentialinfo.name;
			for (var leveloption in leveldata) {
				result = result.replace('#' + leveloption, leveldata[leveloption]);
			}
			
			var row = document.getElementById('potentials').insertRow(-1);
			row.innerHTML = '<tr> <td>' + result + '</td> </tr>';
		}
	}
<?php
}
?>
	
	document.getElementById('item_info_potentials').style.display = document.getElementById('potentials').innerHTML == '' ? 'none' : 'block';
	
	var potentialName = obj.getAttribute('potential');
	document.getElementById('item_info').setAttribute('class', potentialName != null ? 'potential' + potentialName : '');
	
	
	document.getElementById('item_info').style.display = 'block';
	document.getElementById('req_job_list').style.display = isequip ? 'block' : 'none';
	MoveWindow(event);
}

function SetJob(id, flag, neededflag) {
	var correct = (flag & neededflag) == neededflag;
	if (neededflag == 0x80 && flag != 255) 
		correct = false;
	document.getElementById('item_info_reqjob_' + id).setAttribute("class", "req_job" + (correct ? ' needed_job' : ''));
	
}

function HideItemInfo() {
	document.getElementById('item_info').style.display = 'none';
}

function MoveWindow(event) {
	var expectedTop = event.pageY + 10;
	var expectedBottom = expectedTop + parseInt(document.getElementById('item_info').clientHeight);
	if (document.body.clientHeight < expectedBottom) {
		expectedTop -= (expectedBottom - document.body.clientHeight) + 10;
	}
	document.getElementById('item_info').style.top = expectedTop + 'px';
	document.getElementById('item_info').style.left = event.pageX + 10 + 'px';
}


<?php

endif;

?>
</script>

<div id="item_info" style="display: none;">
	<div id="item_info_title"></div>
	<div id="item_info_extra"></div>
	<div class="icon_holder"><img id="item_info_icon" src="" title="" width="50" height="50" /></div>
	<div id="item_info_description"></div>
	<div class="item_req_stats">
		<table border="0" tablepadding="3" tablespacing="3">

<?php
foreach ($reqlist as $option => $desc) {
?>
			<tr id="item_info_req_row_<?php echo strtolower($option); ?>">
				<td><?php echo $desc; ?></td>
				<td id="item_info_req_<?php echo strtolower($option); ?>"></td>
			</tr>
<?php
}
?>
		</table>

	</div>
	<div id="req_job_list">
		<span class="req_job" id="item_info_reqjob_0">Beginner</span>
		<span class="req_job" id="item_info_reqjob_1">Warrior</span>
		<span class="req_job" id="item_info_reqjob_2">Magician</span>
		<span class="req_job" id="item_info_reqjob_3">Bowman</span>
		<span class="req_job" id="item_info_reqjob_4">Thief</span>
		<span class="req_job" id="item_info_reqjob_5">Pirate</span>
	</div>
	<div class="item_stats">
		<hr />
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
	
</div>
	<hr/>

<p class="lead"><?php echo $character_info['name']; ?>'s Skills</p>
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
	character_id = ".$internal_id."
	");
	
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
	<div id="skilllist_<?php echo $i; ?>" class="skill_job" style="display: none;">
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

	</div>
</div>

<?php
}
// $__database->GetRanQueries();
require_once __DIR__.'/inc/footer.php';
?>