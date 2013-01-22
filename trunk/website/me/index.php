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

<script>
function ChangeImage(id, name) {
	document.getElementById('image_' + id).src = "//<?php echo $domain; ?>/actions/card/" + name;
	document.getElementById('stats_' + id).src = "//<?php echo $domain; ?>/actions/infopic/" + name;
}
</script>
<?php


if (count($cache) == 0) {
?>
<p class="lead alert-error alert"><?php echo $__url_userdata['username']; ?> hasn't added any characters yet!</p>

<?php
}

	
$last_world = NULL;
foreach ($cache as $row) {
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
?>
	<tr>
		<td><?php echo $row[1]; ?></td>
		<td><img src="//<?php echo $domain; ?>/inc/img/char_bg.png" alt="Image of <?php echo $row[1]; ?>" id="image_<?php echo $row[0]; ?>" width="271px" height="162px" /></td>
		<td><img src="//<?php echo $domain; ?>/inc/img/stat_window.png" alt="Statistics of <?php echo $row[1]; ?>" id="stats_<?php echo $row[0]; ?>" width="192px" height="345px" /></td>
	</tr>
<?php
}

?>
</table>
</div>
</fieldset>
<script>
<?php
foreach ($cache as $row) {
?>
ChangeImage(<?php echo $row[0]; ?>, '<?php echo $row[1]; ?>');
<?php
	
}
?>
</script>
	
	</div>
	</div>

<?php include_once('../inc/footer.php'); ?>