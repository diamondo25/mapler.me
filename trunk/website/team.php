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

<script type="text/javascript">
function ShowMore() {
	$('.more').css("display", "block");
}
</script>


<style>
.x {
	height: 240px;
}

.x img {
	animation: fadein 2s;
    -moz-animation: fadein 2s; /* Firefox */
    -webkit-animation: fadein 2s; /* Safari and Chrome */
    -o-animation: fadein 2s; /* Opera */
}

.more {
    animation: fadein 2s;
    -moz-animation: fadein 2s; /* Firefox */
    -webkit-animation: fadein 2s; /* Safari and Chrome */
    -o-animation: fadein 2s; /* Opera */
    display: none;
    margin-top: 20px;
}

@keyframes fadein {
    from {
        opacity:0;
    }
    to {
        opacity:1;
    }
}
@-moz-keyframes fadein { /* Firefox */
    from {
        opacity:0;
    }
    to {
        opacity:1;
    }
}
@-webkit-keyframes fadein { /* Safari and Chrome */
    from {
        opacity:0;
    }
    to {
        opacity:1;
    }
}
@-o-keyframes fadein { /* Opera */
    from {
        opacity:0;
    }
    to {
        opacity: 1;
    }
}​


</style>

<center><h2>Our Team</h2></center>
<div class="row">
<?php
foreach ($cache as $row) {
$account = Account::Load($row['id']); 
?>

	<div class="character-brick clickable-brick span4 x" onclick="document.location = '//<?php echo $row['name']; ?>.<?php echo $domain; ?>'">
		<center>
			<br />
			<img src="//mapler.me/avatar/<?php echo $row['character']; ?>"/><br/>
			<p class="lead"><?php echo $row['name']; ?><br />
			<small><?php echo $row['job']; ?></small></p>
		</center>
	</div>

<?php
}
?>

	<div class="character-brick clickable-brick span4 x" onclick="ShowMore()">
		<center style="margin-top:55px;">
			<br />
			<i class="icon-star icon-5x"></i><br />
			<p class="lead">Join the team!</p>
		</center>
	</div>
	
	<div class="more span12">
	<p class="lead"><img src="//mapler.me/avatar/Notification" class="pull-left"/> Hello! There are currently no openings for team positions. <br />
	Please check back later or watch for any announcements on our <a href="//<?php echo $domain; ?>/blog/">blog</a>.</p>
	</div>

</div>
<?php require_once __DIR__.'/inc/footer.php'; ?>