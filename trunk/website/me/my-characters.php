<?php
require_once '../inc/header.php';
?>
<script>
function ChangeImage(id, name) {
	document.getElementById('image_' + id).src = "//<?php echo $domain; ?>/card/" + name;
	document.getElementById('stats_' + id).src = "//<?php echo $domain; ?>/infopic/" + name;
}
</script>
<?php

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
	acc.username = '".$__database->real_escape_string($__url_useraccount->GetUsername())."' 
ORDER BY 
	chr.world_id ASC,
	chr.level DESC
");

if ($q->num_rows == 0) {
?>
<p class="lead alert-error alert">There are no character records for <?php echo $__url_useraccount->GetUsername(); ?></p>

<?php
}

	
$last_world = NULL;
while ($row = $q->fetch_row()) {
	if ($last_world != $row[2]) {
		if ($last_world != NULL) {
?>
</table>
</div>
</fieldset>
<?php
		}
?>
<fieldset>
<legend><button class="btn" data-toggle="collapse" data-target="#<?php echo $row[2]; ?>" href="#<?php echo $row[2]; ?>"><img src="//<?php echo $domain; ?>/inc/img/worlds/<?php echo $row[2]; ?>.png" /> <?php echo $row[2]; ?></button></legend>
<div id="<?php echo $row[2]; ?>" class="collapse accordion-body">
<table width="100%">

<?php
		$last_world = $row[2];
	}
?>
	<tr>
		<td><?php echo $row[1]; ?></td>
		<td><img src="//<?php echo $domain; ?>/avatar/<?php echo $row[1]; ?>" alt="Avatar of <?php echo $row[1]; ?>"/><br/><br/>
		<pre style="width: 280px;">http://<?php echo $domain; ?>/avatar/<?php echo $row[1]; ?></pre></td>
		<td><img src="//<?php echo $domain; ?>/inc/img/char_bg.png" alt="Image of <?php echo $row[1]; ?>" id="image_<?php echo $row[0]; ?>" width="271px" height="162px" /><br/><br/>
		<pre style="width: 280px;">http://<?php echo $domain; ?>/card/<?php echo $row[1]; ?></pre></td>
		<td><img src="//<?php echo $domain; ?>/inc/img/stat_window.png" alt="Statistics of <?php echo $row[1]; ?>" id="stats_<?php echo $row[0]; ?>" width="192px" height="345px" /><br/><br/>
		<pre style="width: 280px;">http://<?php echo $domain; ?>/infopic/<?php echo $row[1]; ?></pre></td>
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
<?php

require_once '../inc/footer.php';
?>