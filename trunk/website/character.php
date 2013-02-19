<?php
require_once 'inc/header.php';
require_once 'actions/job_list.php';
if (!$_loggedin) {
?>
<p class="lead alert-error alert">Please login to view this page.</p>
<?php

	require_once 'inc/footer.php';
	die();
}

$q = $__database->query("
SELECT 
	*,
	w.world_name,
	`GetCharacterAccountID`(id) AS account_id
	
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
<p class="lead alert-error alert">Character not found! The character may have been removed or misspelled.</p>
<?php
}
else {
	$character_info = $q->fetch_assoc();
	
	if ($_loginaccount->GetID() != $character_info['account_id'] && $_loginaccount->GetAccountRank() < RANK_DEVELOPER) {
	?>
	<p class="lead alert-error alert">You are not allowed to view this page.</p>
	<?php

		require_once 'inc/footer.php';
		die();
	}
	
	$internal_id = $character_info['internal_id'];
	
	$stat_addition = GetCorrectStat($internal_id);
	
?>

		<div class="row">
			<img src="//<?php echo $domain; ?>/avatar/<?php echo $character_info['name']; ?>" class="pull-left" />
			<h2 class="span10"><?php echo $character_info['name']; ?> &middot; <span class="nobreak">Level <?php echo $character_info['level']; ?> <?php echo GetJobname($character_info['job']); ?></span><br/>
			
<?php

$map = GetMapleStoryString("map", $character_info['map'], "name");
$subname = GetMapleStoryString("map", $character_info['map'], "street");
if ($subname != NULL) {
	$map = $subname." - ".$map;
}

?>
			
			<small><i>Last seen in <?php echo $map; ?>, <?php echo $character_info['world_name']; ?></i></small></h2>
		</div>
		
		<hr/>
		
		<div class="row">
		<p class="lead">Equipment & statistics...</p>
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

$IDlist = array();
$PotentialList = array();


function GetItemDialogInfo($item, $isequip) {
	global $PotentialList, $IDlist, $reqlist, $optionlist;
	
	if (!isset($IDlist[$item->itemid])) {
		$IDlist[$item->itemid] = IGTextToWeb(GetMapleStoryString("item", $item->itemid, "desc"));
	}
	
	if ($isequip && $item->potential1 != 0 && !isset($PotentialList[$item->potential1])) 
		$PotentialList[$item->potential1] = GetPotentialInfo($item->potential1);
	if ($isequip && $item->potential2 != 0 && !isset($PotentialList[$item->potential2])) 
		$PotentialList[$item->potential2] = GetPotentialInfo($item->potential2);
	if ($isequip && $item->potential3 != 0 && !isset($PotentialList[$item->potential3])) 
		$PotentialList[$item->potential3] = GetPotentialInfo($item->potential3);
	if ($isequip && $item->potential4 != 0 && !isset($PotentialList[$item->potential4])) 
		$PotentialList[$item->potential4] = GetPotentialInfo($item->potential4);
	if ($isequip && $item->potential5 != 0 && !isset($PotentialList[$item->potential5])) 
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
	
	$arguments = "SetItemInfo(event, this, ";
	$arguments .= $item->itemid.",".($isequip ? 1 : 0).", ";
	$arguments .= ValueOrDefault($stats['reqjob'], 0).", ";
	
	foreach ($reqlist as $option => $desc) {
		if ($isequip) 
			eval('$arguments .= $'.$option.'.", ";'); // Fugly
		else 
			$arguments .= '0, ';
	}
	
	foreach ($optionlist as $option => $desc) {
		if ($isequip) 
			eval('$arguments .= $item->'.$option.'.", ";'); // Fugly
		else 
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
top: <?php echo ($row * (33 + $inv_extra_offy)) + $inv_pos_offy; ?>px; left: <?php echo ($col * (33 + $inv_extra_offx)) + $inv_pos_offx; ?>px; margin-bottom: <?php echo $inv_extra_offy; ?>px;
<?php
}

?>
<style type="text/css">
.character_equips_holder {
	background-image: url('//<?php echo $domain; ?>/inc/img/ui/Item/equips_background.png');
	width: 184px;
	height: 290px;
	position: relative;
}

.character_equips_holder img {
	position: absolute;
}

/* monster book */
.character_equips .slot55 {
<?php InventoryPosCalc(0, 0); ?>
}

/* medal */
.character_equips .slot49 {
<?php InventoryPosCalc(1, 0); ?>
}

/* pocket  */
.character_equips .slot52 {
<?php InventoryPosCalc(2, 0); ?>
}

/* mantle */
.character_equips .slot9 {
<?php InventoryPosCalc(3, 0); ?>
}

/* gloves */
.character_equips .slot8 {
<?php InventoryPosCalc(4, 0); ?>
}

/* taming mob */
.character_equips .slot18 {
<?php InventoryPosCalc(6, 0); ?>
}








/* cap */
.character_equips .slot1 {
<?php InventoryPosCalc(0, 1); ?>
}

/* face */
.character_equips .slot2 {
<?php InventoryPosCalc(1, 1); ?>
}

/* clothes */
.character_equips .slot5 {
<?php InventoryPosCalc(3, 1); ?>
}

/* pants */
.character_equips .slot6 {
<?php InventoryPosCalc(4, 1); ?>
}

/* saddle */
.character_equips .slot19 {
<?php InventoryPosCalc(6, 1); ?>
}







/* badge */
.character_equips .slot56 {
<?php InventoryPosCalc(0, 2); ?>
}

/* pendant */
.character_equips .slot17 {
<?php InventoryPosCalc(3, 2); ?>
}

/* 2nd pendent (cash) - Note to Erwin: There's a different UI that this requires (but should be fine without it anyway) */
.character_equips .slot65 {
<?php InventoryPosCalc(2, 2); ?>
}

/* belt */
.character_equips .slot50 {
<?php InventoryPosCalc(4, 2); ?>
}

/* shoes */
.character_equips .slot7 {
<?php InventoryPosCalc(5, 2); ?>
}






/* android */
.character_equips .slot53 {
<?php InventoryPosCalc(0, 3); ?>
}

/* android heart */
.character_equips .slot54 {
<?php InventoryPosCalc(0, 4); ?>
}

/* ring 3 */
.character_equips .slot15 {
<?php InventoryPosCalc(1, 3); ?>
}

/* ear acc */
.character_equips .slot4 {
<?php InventoryPosCalc(2, 3); ?>
}

/* weapon */
.character_equips .slot11 {
<?php InventoryPosCalc(3, 3); ?>
}

/* ring 1 */
.character_equips .slot12 {
<?php InventoryPosCalc(4, 3); ?>
}







/* ring 4 */
.character_equips .slot16 {
<?php InventoryPosCalc(1, 4); ?>
}

/* shoulder */
.character_equips .slot51 {
<?php InventoryPosCalc(2, 4); ?>
}

/* orb / shield */
.character_equips .slot10 {
<?php InventoryPosCalc(3, 4); ?>
}

/* ring 2 */
.character_equips .slot13 {
<?php InventoryPosCalc(4, 4); ?>
}


.character_equips_holder div {
    height: 32px;
    width: 32px;
}

.inventory {
	position: relative;
	width: 170px;
	height: 195px;
	overflow-y: scroll;
	
}

.inventory div {
    height: 32px;
    width: 32px;
	position: absolute;
	border: 1px solid lightgray;
	z-index: 1;
}

.inventory img {
	position: absolute;
	z-index: 2;
}

</style>

<?php
$equips = $inventory->GetEquips();

?>

<div class="row">
	<div class="span3">
		<div class="character_equips">
			<div class="character_equips_holder">

<?php
foreach ($equips as $slot => $item) {
	if ($slot <= -100) continue; // Cash equips etc...
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
		</div>
	</div>

	<div class="span4">
		<select onchange="ChangeInventory(this.value)" style="height:35px !important;">
			<option value="1">Equipment</option>
			<option value="2">Use</option>
			<option value="3">Set-up</option>
			<option value="4">Etc</option>
			<option value="5">Cash</option>
		</select>
		<br />
<?php


$inv_pos_offx = 5; // Diff offsets
$inv_pos_offy = 5;
$inv_extra_offx = $inv_extra_offy = 5;


for ($inv = 0; $inv < 5; $inv++):
	$inv1 = $inventory->GetInventory($inv);
?>
		<div class="span3 character-brick inventory" id="inventory_<?php echo $inv; ?>" style="display: none; padding: 5px  !important;">
<?php for ($i = 0; $i < count($inv1); $i += 4): ?>
<?php 	for ($j = $i; $j < $i + 4; $j++): ?>
<?php 
		$row = floor($j / 4);
		$col = $j % 4;
		if (isset($inv1[$j])) {
			$isequip = $inv == 0;
			$item = $inv1[$j];
			$info = GetItemDialogInfo($item, $isequip);


			$itemwzinfo = GetItemWZInfo($item->itemid);
	
	
	
?>
			<div class="item-icon <?php echo $info['potentials'] != 0 ? ' potential'.$info['potentials'] : ''; ?>" style="<?php InventoryPosCalc($row, $col); ?>"></div>
			<img class="item-icon" potential="<?php echo $info['potentials']; ?>" style="<?php InventoryPosCalc($row, $col); ?> margin-top: <?php echo (32 - $itemwzinfo['info_icon_origin_Y']); ?>px; margin-left: <?php echo -$itemwzinfo['info_icon_origin_X']; ?>px;" src="<?php echo GetItemIcon($item->itemid); ?>" item-name="<?php echo IGTextToWeb(GetMapleStoryString("item", $item->itemid, "name")); ?>" onmouseover="<?php echo $info['mouseover']; ?>" onmousemove="MoveWindow(event)" onmouseout="HideItemInfo()" />

<?php 
		}
		else {
?>
			<div class="item-icon" style="<?php InventoryPosCalc($row, $col); ?>"></div>
<?php
		}
?>
<?php	endfor; ?>
<?php endfor; ?>
		</div>
<?php endfor; ?>


	</div>
	<div class="span3">
		<img src="//<?php echo $domain; ?>/infopic/<?php echo $character_info['name']; ?>" />
	</div>
	

</div>

<style type="text/css">
#item_info {
	border: 1px solid rgba(0,0,0,0.6);
	border-radius: 5px;
	background-color: rgba(255,255,255,0.95);
	padding: 5px;
	position: absolute;
	width: 285px;
	z-index: 100;
}

#item_info #item_info_extra, #item_info #item_info_description {
	margin-bottom: 5px;
}

#item_info #item_info_extra span {
	text-align: center;
	display: block;
	font-size: 12px;
}

#item_info #item_info_description {
	margin-left: 70px;
}

#item_info #item_info_title {
	text-align: center;
	margin-bottom: 5px;
	font-size: 15px;
}

#item_info .icon_holder {
	margin: 0 auto;
	border: 1px solid black;
	background-color: lightgray;
	padding: 3px;
	width: 50px;
	height: 50px;
	margin-right: 5px;
	float: left;
}

#item_info .item_stats, #item_info .item_potential_stats {
	clear: both;
}

#item_info .item_stats > table, #item_info .item_potential_stats > table {
	font-size: 11px;
}

#item_info .item_req_stats {
	float: right;
	width: 170px;
}
#item_info .item_req_stats > table {
	font-size: 11px;
}

#item_info .req_job {
	font-size: 11px;
	color: white;
	
	padding: 2px;
	border-radius: 3px;
	background-color: rgba(0,0,0,1);
}

#item_info .needed_job {
	color: orange;
}

#item_info #req_job_list {
	clear: both;
	padding-top: 10px;
}

#item_info #req_job_list hr {
	margin: 5px 0;
}

#item_info #item_potential_stats {
	display: none;
}

.potential1 {
	border: 1px solid #FF0066 !important;
}

.potential2 {
	border: 1px solid #5CA1FF !important;
}

.potential3 {
	border: 1px solid #C261FF !important;
}

.potential4 {
	border: 1px solid #FFCC00 !important;
}

.potential5 {
	border: 1px solid #00FF00 !important;
}
</style>

<script>
var descriptions = <?php echo json_encode($IDlist); ?>;
var potentialDescriptions = <?php echo json_encode($PotentialList); ?>;

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
	
	var potentiallevel = Math.floor(reqlevel / 10);
	if (potentiallevel == 0) potentiallevel = 1;
	
	if (potentialflag == 1) { // 12 = unlocked
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

var lastid = -1;
function ChangeInventory(id) {
	id -= 1;
	if (lastid != -1)
		document.getElementById('inventory_' + lastid).style.display = 'none';
	lastid = id;
	document.getElementById('inventory_' + lastid).style.display = 'block';
}
ChangeInventory(1);
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
		<hr />
	</div>
	<div class="item_stats">
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
	
<hr />

<p class="lead">Skills, Mounts, and more...</p>
<?php

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

	while ($row = $q->fetch_assoc()) {
		$name = GetMapleStoryString("skill", $row['skillid'], "name");
		if ($name == NULL) continue;
		$block = floor($row['skillid'] / 10000);
		if ($lastgroup != $block) {
			if ($lastgroup != -1) {
?>
</table>
<?php
			}
			$lastgroup = $block;
?>
<br/><h4><?php echo GetMapleStoryString("skill", $lastgroup, "bname"); ?></h4>
<table border="1" cellspacing="2" cellpadding="8" class="character-brick" style="width: 500px">
	<tr>
		<th style="width: 250px">Skill Name</th>
		<th>Level</th>
		<th>Max Level</th>
		<th>Expires at</th>
	</tr>
<?php
		}
		
		if ($row['maxlevel'] == NULL) {
			$row['maxlevel'] = '-';
		}
		if ($row['skillid'] < 90000000 && $row['level'] >= 100) {
			$row['level'] = 'Bound with: '.GetCharacterName($row['level']);
		}
?>
	<tr>
		<td><img src="//static_images.mapler.me/Skills/<?php echo $block; ?>/<?php echo $row['skillid']; ?>/icon.png" /> <?php echo $name; ?></td>
		<td><?php echo $row['level']; ?></td>
		<td><?php echo $row['maxlevel']; ?></td>
		<td><?php echo GetSystemTimeFromFileTime($row['expires']); ?></td>
	</tr>
<?php
	}
?>
</table>
<hr />







<?php
	
}


// $__database->GetRanQueries();

require_once 'inc/footer.php';
?>