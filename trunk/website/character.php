<?php
include_once('inc/header.php');
?>

<?php
if (!$_loggedin) {
?>

<p class="lead alert-error alert">Plese login to view this page.</p>
<?php die; ?>
<?php
}
?>

<?php
if (!$_logindata['account_rank'] == RANK_ADMIN):
?>
<p class="lead alert-error alert">You are not an admin.</p>
<?php die; ?>
<?php
endif;
?>

<?php
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
	$row = $q->fetch_assoc();
	
	$internal_id = $row['internal_id'];
	
	$stat_addition = GetCorrectStat($internal_id);
	
?>
<h2><?php echo $row['name']; ?>'s Info</h2>
<img src="//<?php echo $domain; ?>/avatar/<?php echo $row['name']; ?>" />
<table>
<?php
	foreach ($row as $columnname => $value) {	
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
<table border="1" cellspacing="2" cellpadding="8" width="500px">
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




	$q = $__database->query("
SELECT
	inventory, itemid, slot, amount, cashid
FROM
	items
WHERE
	character_id = ".$internal_id."
ORDER BY
	inventory ASC,
	slot ASC
	");
	
	$lastgroup = -1;

	while ($row = $q->fetch_assoc()) {
		$name = GetMapleStoryString("item", $row['itemid'], "name");
		$cash = $row['cashid'] != 0;
		$block = $row['inventory'];
		if ($lastgroup != $block) {
			if ($lastgroup != -1) {
?>
</table>
<?php
			}
			$lastgroup = $block;
?>
<h4><?php echo GetInventoryName($lastgroup); ?></h4>
<table border="1" cellspacing="2" cellpadding="8" width="500px">
	<tr>
		<th>Itemname</th>
		<th>Slot</th>
		<th>Amount</th>
	</tr>
<?php
		}
		
		if ($cash) {
			$name = '<em>'.$name.'</em>';
		}
		
?>
	<tr>
		<td><?php echo $name; ?></td>
		<td><?php echo $row['slot']; ?></td>
		<td><?php echo $row['amount']; ?></td>
	</tr>
<?php
	}
?>
</table>
<?php
	
}

include_once('inc/footer.php');
?>