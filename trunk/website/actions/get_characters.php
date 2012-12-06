<script>
function ChangeImage(id, name) {
	document.getElementById('image_' + id).src = "actions/character_image.php?name=" + name;
	document.getElementById('stats_' + id).src = "actions/character_stats.php?name=" + name;
}
</script>
<?php
if (!$_loggedin) die();


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
	world_data w 
	ON 
		w.world_id = chr.world_id 

WHERE 
	usr.account_id = '".$_logindata["id"]."' 
ORDER BY 
	chr.world_id ASC
");
	
$last_world = NULL;
while ($row = $q->fetch_row()) {
	if ($last_world != $row[2]) {
		if ($last_world != NULL) {
?>
</table>
</fieldset>
<?php
		}
?>
<fieldset>
<legend><button class="btn" data-toggle="collapse" data-target="#<?php echo $row[2]; ?>" href="#<?php echo $row[2]; ?>"><?php echo $row[2]; ?></button></legend>
<div id="<?php echo $row[2]; ?>" class="collapse accordion-body">
<table width="100%">

<?php
		$last_world = $row[2];
	}
	/*
?>
<div style="display: inline-block">
<?php echo $row[1]; ?><br />
<img src="char_bg.png" alt="Image of <?php echo $row[1]; ?>" id="image_<?php echo $row[0]; ?>" width="271px" height="162px" /><br />
<img src="stat_window.png" alt="Statistics of <?php echo $row[1]; ?>" id="stats_<?php echo $row[0]; ?>" width="192px" height="345px" />
</div>
<?php
		*/
?>
	<tr>
		<td><?php echo $row[1]; ?></td>
		<td><img src="actions/img/char_bg.png" alt="Image of <?php echo $row[1]; ?>" id="image_<?php echo $row[0]; ?>" width="271px" height="162px" /></td>
		<td><img src="actions/img/stat_window.png" alt="Statistics of <?php echo $row[1]; ?>" id="stats_<?php echo $row[0]; ?>" width="192px" height="345px" /></td>
	</tr>
<?php
}

$q->data_seek(0);
?>
</table>
</div>
</fieldset>
<script>
<?php
while ($row = $q->fetch_row()) {
?>
ChangeImage(<?php echo $row[0]; ?>, '<?php echo $row[1]; ?>');
<?php
	
}
?>
</script>
