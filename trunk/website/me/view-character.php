<?php
include_once('../inc/header.php');

$q = $__database->query("
SELECT 
	chr.*, 
	w.world_name 
FROM 
	characters chr 
LEFT JOIN 
	users usr 
	ON 
		usr.ID = chr.userid 
LEFT JOIN 
	accounts acc 
	ON 
		acc.id = usr.account_id 
LEFT JOIN 
	world_data w 
	ON 
		w.world_id = chr.world_id 

WHERE 
	acc.username = '".$__database->real_escape_string($__url_userdata['username'])."'
	AND
	chr.name = '".$__database->real_escape_string($_GET['name'])."'
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
	
?>
<h2><?php echo $row['name']; ?>'s Info</h2>
<img src="//<?php echo $domain; ?>/avatar/<?php echo $row['name']; ?>" />
<table>
<?php
	foreach ($row as $columnname => $value) {
?>
	<tr>
		<th><?php echo $columnname; ?></th>
		<td><?php echo $value; ?></td>
	</tr>
<?php
	}
?>
</table>
<?php

	$q->free();
	
	$q = $__database->query("
SELECT
	skillid, level, maxlevel, ceil((expires/10000000) - 11644473600) as expires
FROM
	skills
WHERE
	character_id = ".$internal_id."");
	
?>
<table border="1">
<?php
	while ($row = $q->fetch_assoc()) {
		if ($row['maxlevel'] == NULL) {
			$row['maxlevel'] = '-';
		}
		if ($row['expires'] == 3439756800) {
			$row['expires'] = '-';
		}
		else {
			$row['expires'] = date("Y-m-d h:i:s", $row['expires']);
		}
?>
	<tr>
		<td><?php echo $row['skillid']; ?></td>
		<td><?php echo $row['level']; ?></td>
		<td><?php echo $row['maxlevel']; ?></td>
		<td><?php echo $row['expires']; ?></td>
	</tr>
<?php
	}
?>
</table>
<?php

	
}

include_once('../inc/footer.php');
?>