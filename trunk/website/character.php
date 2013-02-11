<?php
require_once 'inc/header.php';
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
	`GetCharacterAccountID`(id) AS account_id
FROM 
	`characters`
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

	<div id="profile" class="row">
		<div id="header" class="span12" style="background: url('//<?php echo $domain; ?>/inc/img/back_panel.png') repeat top center" >
          <div id="profile-user-details">
          	 <div class="row">
            	<div class="span6 offset3">
                	<div id="user-about" class="center">
                    	<h2><?php echo $character_info['name']; ?></h2>
						<img src="//<?php echo $domain; ?>/avatar/<?php echo $character_info['name']; ?>" />
                   </div>
               </div>
           </div>
		</div>
	</div>
</div>
<table>
<?php
	foreach ($character_info as $columnname => $value) {	
		if ($columnname == 'map') {
			$tmp = GetMapleStoryString("map", $value, "name");
			$subname = GetMapleStoryString("map", $value, "street");
			if ($subname != NULL) {
				$tmp = $subname." - ".$tmp;
			}
			$value = $tmp;
		}
		elseif (isset($stat_addition[$columnname])) {
			$value = ($value + $stat_addition[$columnname])." (".$value." + ".$stat_addition[$columnname].")";
		}
		
?>
	<tr>
		<th><?php echo $columnname; ?></th>
		<td><?php echo $value; ?></td>
	</tr>
<?php
	}
?>
</table>
<hr />
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
<?php

$inventory = new InventoryData($character_info['internal_id']);


?>
<br/>
<select onchange="ChangeInventory(this.value)" style="height:35px !important;">
	<option value="1">Equipment</option>
	<option value="2">Use</option>
	<option value="3">Set-up</option>
	<option value="4">Etc</option>
	<option value="5">Cash</option>
<select>

<div class="row">
<?php


$optionlist = array();
$optionlist[] = 'slots';
$optionlist[] = 'scrolls';
$optionlist[] = 'str';
$optionlist[] = 'dex';
$optionlist[] = 'int';
$optionlist[] = 'luk';
$optionlist[] = 'maxhp';
$optionlist[] = 'maxmp';
$optionlist[] = 'weaponatt';
$optionlist[] = 'weapondef';
$optionlist[] = 'magicatt';
$optionlist[] = 'magicdef';
$optionlist[] = 'acc';
$optionlist[] = 'avo';
$optionlist[] = 'hands';
$optionlist[] = 'jump';
$optionlist[] = 'speed';

$IDlist = array();



for ($inv = 0; $inv < 5; $inv++):
	$inv1 = $inventory->GetInventory($inv);
?>
<table border="1" class="span3 character-brick" style="padding:15px !important; display: none;" id="inventory_<?php echo $inv; ?>">
<?php
	for ($i = 0; $i < count($inv1); $i += 4):
?>
	<tr>
<?php
		for ($j = $i; $j < $i + 4; $j++):
?>
		<td style="width: 50px; height: 50px;" align="center" valign="middle">
<?php 
		if (isset($inv1[$j])) {
			$item = $inv1[$j];
			if (!isset($IDlist[$item->itemid]))
				$IDlist[$item->itemid] = IGTextToWeb(GetMapleStoryString("item", $item->itemid, "desc"));
			
			$arguments = "SetItemInfo(event, this, ";
			
			foreach ($optionlist as $option) {
				if ($inv == 0) 
					eval('$arguments .= $item->'.$option.'.", ";'); // Fugly
				else 
					$arguments .= '0,';
			}
			$arguments .= "descriptions[".$item->itemid."], '".GetSystemTimeFromFileTime($item->expires)."');";
?>
			<div style="position: relative; width: 50px; height: 50px;">
				<img src="<?php echo GetItemIcon($item->itemid); ?>" item-name="<?php echo IGTextToWeb(GetMapleStoryString("item", $item->itemid, "name")); ?>" onmouseover="<?php echo $arguments; ?>" onmouseout="HideItemInfo()" />
				<div style="position: absolute; bottom: 0; right: 0; color: black;"><?php echo $inv != 0 ? $item->amount : ''; ?></div>
			</div>
<?php 
		}
?>
		</td>
<?php
		endfor;
?>
	</tr>
<?php
	endfor;
?>
</table>
<?php
endfor;
?>
</div>

<style type="text/css">
#item_info {
	border: 1px solid rgba(0,0,0,0.6);
	border-radius:5px;
	background-color: rgba(255,255,255,0.95);
	padding: 5px;
	position: absolute;
	width: 200px;
	transition: all 2s;
	-moz-transition: all 2s; /* Firefox 4 */
	-webkit-transition: all 2s; /* Safari and Chrome */
	-o-transition: all 2s; /* Opera */
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

#item_info .item_stats {
	clear: both;
}

#item_info #item_info_expires {
	display: block;
	color: red;
}
</style>

<script>
var descriptions = <?php echo json_encode($IDlist); ?>;

function SetItemInfo(event, obj, <?php
foreach ($optionlist as $option) {
	echo $option.", ";
}
?>
description, expires) {
	document.getElementById('item_info_title').innerHTML = obj.getAttribute('item-name');
	document.getElementById('item_info_icon').src = obj.src;
	
<?php
foreach ($optionlist as $option) {
?>
	if (<?php echo $option; ?> != 0 && <?php echo $option; ?> != '') {
		document.getElementById('item_info_row_<?php echo strtolower($option); ?>').style.display = '';
		document.getElementById('item_info_<?php echo strtolower($option); ?>').innerHTML = <?php echo $option; ?>;
	}
	else {
		document.getElementById('item_info_row_<?php echo strtolower($option); ?>').style.display = 'none';
	}
<?php
}
?>
	document.getElementById('item_info').style.top = event.pageY + 10 + 'px';
	document.getElementById('item_info').style.left = event.pageX + 10 + 'px';
	
	document.getElementById('item_info_expires').innerHTML = expires;
	if (description != '') {
		document.getElementById('item_info_description').style.display = '';
		document.getElementById('item_info_description').innerHTML = description;
	}
	else {
		document.getElementById('item_info_description').style.display = 'none';
	}
	document.getElementById('item_info').style.display = 'block';
}

function HideItemInfo() {
	document.getElementById('item_info').style.display = 'none';
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
	<div id="item_info_expires"></div>
	<div class="icon_holder"><img id="item_info_icon" src="" title="" /></div>
	<div id="item_info_description"></div>
	<div class="item_stats">
		<table border="0">

<?php
foreach ($optionlist as $option) {
?>
			<tr id="item_info_row_<?php echo strtolower($option); ?>">
				<td><?php echo $option; ?></td>
				<td id="item_info_<?php echo strtolower($option); ?>"></td>
			</tr>
<?php
}
?>
		</table>

	</div>

</div>






<?php
	
}

require_once 'inc/footer.php';
?>