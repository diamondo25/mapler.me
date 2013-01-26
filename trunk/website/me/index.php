<?php
include_once('../inc/header.php');

$q = $__database->query("
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
	accounts acc 
	ON 
		acc.id = usr.account_id 
LEFT JOIN 
	world_data w 
	ON 
		w.world_id = chr.world_id 

WHERE 
	acc.username = '".$__database->real_escape_string($__url_userdata['username'])."' 
ORDER BY 
	chr.world_id ASC,
	chr.level DESC");

// printing table rows

$cache = array();

while ($row = $q->fetch_row()) {
	$cache[] = $row;
}
$q->free();



?>

	<div class="row">
	<div class="span2">
<?php
if (count($cache) > 0) {
?>
<img src="//<?php echo $domain; ?>/avatar/<?php echo $cache[0][1]; ?>" class="img-polaroid"/>
<?php
}
?>
	</div>
	<div class="span10">
	<p class="lead"><?php echo $__url_userdata['full_name']; ?> <span class="muted">(<?php echo $__url_userdata['nickname']; ?>)</span></p>
	<?php echo $__url_userdata['bio']; ?>
	<hr/>
	
	<!-- Character Display -->
	<div class="span10">

<?php


if (count($cache) == 0) {
?>
<p class="lead alert-error alert"><?php echo $__url_userdata['username']; ?> hasn't added any characters yet!</p>

<?php
}

	
$last_world = NULL;
$i = 0;
foreach ($cache as $row) {
	if ($last_world != $row[2]) {
		if ($last_world != NULL) {
			for ($i %= 5; $i < 5; $i++) {
?>
				<td width="200px">&nbsp;</td>
<?php
			}
			$i = 0;
?>
			</tr>
		</table>
	</div>
</fieldset>
<?php
		}
?>
<fieldset>
	<legend><button class="btn" data-toggle="collapse" data-target="#<?php echo $row[2]; ?>" href="#<?php echo $row[2]; ?>"><?php echo $row[2]; ?></button></legend>
	<div id="<?php echo $row[2]; ?>" class="collapse accordion-body">
		<table width="100%">
			<tr>
<?php
		$last_world = $row[2];
	}
	if ($i != 0 && $i % 5 == 0) {
?>
			</tr>
			<tr>
<?php
	}
?>
				<td width="200px">
					<center><img src="//<?php echo $domain; ?>/avatar/<?php echo $row[1]; ?>" class="img-polaroid" /></center>
					<br />
					<center><?php echo $row[1]; ?></center>
				</td>
<?php
	$i++;
}

for ($i %= 5; $i < 5; $i++) {
?>
				<td width="200px">&nbsp;</td>
<?php
}
?>
			</tr>
		</table>
	</div>
</fieldset>
	
	</div>
	</div>

<?php include_once('../inc/footer.php'); ?>