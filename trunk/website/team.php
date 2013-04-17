<?php require_once __DIR__.'/inc/header.php';

$q = $__database->query("
SELECT
	*
FROM
	staff_information
");

$cache = array();
while ($row = $q->fetch_assoc()) {
	$cache[] = $row;
}
$q->free();
?>

<style>

.title {
	font-weight: bold;
	font-family: Helvetica, sans-serif;
	font-size: 24px;
	letter-spacing: 0px;
	color: #777;
}

.more {
	font-weight: 200;
	letter-spacing: normal;
	color: #999;
	font-size: 15px;
}

.avatar {
	padding: 5px;
	background: #fff;
	box-shadow: 0 1px 2px rgba(0,0,0,0.15);
	border: 1px solid #ddd;
	margin-bottom: 20px;
}

hr {
	margin: 0 auto;
	border: 0;
	border-top: 1px solid #eee;
	border-bottom: 1px solid #CCC;
	width: 100%;
	margin-bottom:15px;
}

</style>

<center><h2>Our Team</h2></center>
<div class="stream_display">
<?php
foreach ($cache as $row) {
?>

	<div class="status clickable-brick" style="margin:10px;" onclick="document.location = '//<?php echo $row['name']; ?>.<?php echo $domain; ?>'">
		<img src="//mapler.me/avatar/<?php echo $row['character']; ?>" class="pull-right"/><br/>
		<p class="title"><?php echo $row['name']; ?><br/>
		<small class="more"><?php echo $row['job']; ?></small></p>
		<hr/>
		<small class="more"><?php echo $row['description']; ?></small></p>
	</div>

<?php
}
?>
</div>
<?php require_once __DIR__.'/inc/footer.php'; ?>