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

.ct-label a {
    color: #FFF !important;
}
</style>

<?php if(isset($_GET['id'])) {
?>
<div class="row">
<?php
    $faqid = intval($_GET['id']);
    
    $q = $__database->query("
    SELECT
    	*
    FROM
    	faq
    WHERE
        id = $faqid
    ");
    
    $questions = array();
    while ($row = $q->fetch_assoc()) {
    	$questions[] = $row;
    }

if ($q->num_rows == 0) {
	$q->free();
?>
<div class="span12">
<center>
	<p class="lead status">Question not found.</p>
</center>
</div>
<?php
	require_once __DIR__.'/inc/footer.php';
	die;
}
    
    $q->free();
    foreach ($questions as $row) {
?>
<div class="faq span12">
        <div class="pull-right">
			<a href="//<?php echo $domain; ?>/faq/" class="btn btn-default"><i class="icon-chevron-left"></i> Return to the FAQ</a>
		</div>
		<h3 style="margin-bottom:20px;" class="alert alert-info"><i class="icon-question-sign"></i> Question: <?php echo $row['title']; ?></h3>
		<p class="lead"><?php echo $row['content']; ?></p>
</div>
<?php
}
?>
</div>
<?php
	require_once __DIR__.'/inc/footer.php';
    die;
}
?>

<div class="row">

<div class="faq span12" style="margin-bottom:30px;">
		<div class="center">
			<img src="http://cdn.mapler.me/media/67549be4c8a03cbd248d1c97410a18e8.png" style="border-radius:5px;"/>
			<p>Have a question? Start typing a topic or word you're thinking about, and it may appear below!<br />
			If not, feel free to <a href="//<?php echo $domain; ?>/support/">let us know</a> and we'll add it here!</p>
			<input type="text" value="" placeholder="Start typing ...">
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
				<h3><span id="searchfor"><?php echo $row['title']; ?></span> <span class="ct-label"><a href="//<?php echo $domain; ?>/faq/<?php echo $row['id']; ?>"><i class="icon-share-sign"></i> Direct Link</a></span></h3>
				<p id="searchfor"><?php echo $row['content']; ?></p>
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