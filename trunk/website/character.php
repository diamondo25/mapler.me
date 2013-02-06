<?php
require_once 'inc/header.php';
if (!$_loggedin) {
?>
<p class="lead alert-error alert">Please login to view this page.</p>
<?php

	require_once 'inc/footer.php';
	die();
}
elseif ($_loginaccount->GetAccountRank() != RANK_ADMIN) {
?>
<p class="lead alert-error alert">You are not an admin.</p>
<?php

	require_once 'inc/footer.php';
	die();
}

$q = $__database->query("
SELECT 
	*
FROM 
	`characters`
WHERE 
	name = '".$__database->real_escape_string($_GET['name'])."'
");

if ($q->num_rows == 0) {
	$q->free();
?>
<p class="lead alert-error alert">Character not found</p>
<?php
}
else {
	$character_info = $q->fetch_assoc();
	
	$internal_id = $character_info['internal_id'];
	
	$stat_addition = GetCorrectStat($internal_id);
	
?>
<h2><?php echo $character_info['name']; ?>'s Info</h2>
<img src="//<?php echo $domain; ?>/avatar/<?php echo $character_info['name']; ?>" />
<table class="character-brick">
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
<h4><?php echo GetMapleStoryString("skill", $lastgroup, "bname"); ?></h4>
<table border="1" cellspacing="2" cellpadding="8" width="500px" class="character-brick">
	<tr>
		<th>Skill Name</th>
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
		if ($row['expires'] == 3439756800) {
			$row['expires'] = '-';
		}
		else {
			$row['expires'] = GetSystemTimeFromFileTime($row['expires']);
		}
?>
	<tr>
		<td><?php echo $name; ?></td>
		<td><?php echo $row['level']; ?></td>
		<td><?php echo $row['maxlevel']; ?></td>
		<td><?php echo $row['expires']; ?></td>
	</tr>
<?php
	}
?>
</table>
<?php

$inventory = new InventoryData($character_info['internal_id']);


?>

<div class="row">
<?php
for ($inv = 0; $inv < 5; $inv++):
	$inv1 = $inventory->GetInventory($inv);
?>
<table border="1" class="span2 character-brick" style="padding:15px !important;">
<?php
	for ($i = 0; $i < count($inv1); $i += 4):
?>
	<tr height="50px">
<?php
		for ($j = $i; $j < $i + 4; $j++):
?>
		<td width="50px" align="center" valign="middle">
		<?php if (isset($inv1[$j])): ?>
			<img src="<?php echo GetItemIcon($inv1[$j]->itemid); ?>" alt="<?php echo GetMapleStoryString("item", $inv1[$j]->itemid, "name"); ?>" />
		<?php endif; ?>
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









<?php
	
}

require_once 'inc/footer.php';
?>