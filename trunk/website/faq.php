<?php require_once __DIR__.'/inc/header.php'; ?>

<style>
.faq input[type=text] {
padding: 14px;
box-shadow: none;
font-size: 16px;
width: 55%;
}

.search_button {
padding: 14px 28px;
margin: -10px 0 0 -70px;
border-radius: 0 3px 3px 0;
-moz-border-radius: 0 3px 3px 0;
-webkit-border-radius: 0 3px 3px 0;
}

.faq li {
margin-bottom: 20px;
}
</style>
<div class="row">
<div class="faq span12" style="margin-bottom:30px;">
		<div class="center">
			<img src="http://cdn.mapler.me/media/67549be4c8a03cbd248d1c97410a18e8.png" style="border-radius:5px;"/>
			<input type="text" value="" placeholder="Start typing ...">
			<a type="" class="search_button btn btn-info"><i class="icon-search icon-white"></i></a>
		</div>
</div>

<div class="faq span12">
		<ol>
<?php
$q = $__database->query("
SELECT
	*
FROM
	faq
");

$questions = array();
while ($row = $q->fetch_assoc()) {
	$questions[] = $row;
}
$q->free();
foreach ($questions as $row) {
?>
			<li style="display:none;">
				<h3><?php echo $row['title']; ?> <span class="ct-label"><?php echo $row['topic']; ?></span></h3>
				<p><?php echo $row['content']; ?></p>
			</li>
<?php
}
?>
		</ol>
		<div class="no-results" style="display:none;">
			<center>
				<p class="lead">We couldn't find an answer to that request! Try mentioning @Tyler or @Diamondo25!</p>
			</center>
		</div>
	</div>
</div>
      
<?php require_once __DIR__.'/inc/footer.php'; ?>