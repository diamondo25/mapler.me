<script type="text/javascript">
function GetPlayer() {
	$('input[name=type]').val('character');
	$("input").attr("placeholder", "Search for a character?");
}
</script>

<?php
// search related functions and set up.
$searchtype = 'character';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['type'])) {
	$typepls = nl2br(htmlentities(strip_tags(trim($_POST['type']))));
	$searchtype = $typepls;
}

$searching = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search'])) {
	$searchback = nl2br(htmlentities(strip_tags(trim($_POST['search']))));
	if (!empty($searchback)) {
		$searching = $searchback;
	}
}

$check = strlen($searching);
?>

<style type="text/css">
.avatar {
	padding: 5px;
	background: #fff;
	box-shadow: 0 1px 2px rgba(0,0,0,0.15);
	border: 1px solid #ddd;
	margin-bottom: 20px;
	width: 96%;
}

.name {
	font-weight: bold;
	font-family: Helvetica, sans-serif;
	font-size: 24px;
	letter-spacing: 0px;
	color: #777;
}

.name_extra {
	font-weight: 200;
	letter-spacing: normal;
	color: #999;
	font-size: 15px;
}

.side {
	font-weight: 200;
	letter-spacing: normal;
	color: #999;
	font-size: 15px;
}

.rank {
	white-space: nowrap;
	background: #f7921e;
	color: #fff;
	padding: 2px 4px;
	font-size: 11px;
	-webkit-border-radius: 3px;
	-moz-border-radius: 3px;
	border-radius: 3px;
	margin-bottom: 10px;
}
</style>

<div class="row">
	<div class="span3" style="height:100% !important; float: left;">
		<p class="lead">Search</p>
        <form method="post" action="http://<?php echo $domain; ?>/search/">
			<input type="text" name="search" placeholder="Search for a character?" />
			<input type="hidden" name="type" id="stype" value="character"/>
			<p>Tip: Type nothing to view all recent characters!</p>
		</form>
		<?php if ($check == '0') { ?>
		<hr />
		<ul id="filters" class="nav nav-list sidebar">
			<li><a href="#" data-filter="*">Show all.</a></li>
			<li><a href="#" data-filter=".scania">Scania</a></li>
			<li><a href="#" data-filter=".bera">Bera</a></li>
			<li><a href="#" data-filter=".broa">Broa</a></li>
			<li><a href="#" data-filter=".windia">Windia</a></li>
			<li><a href="#" data-filter=".bellocan, .nova">Bellocan / Nova</a></li>
			<li><a href="#" data-filter=".mardia, .kradia, .yellonde, .chaos">Mardia, Kradia, Yellonde, Chaos</a></li>
			<li><a href="#" data-filter=".galicia, .arcania, .zenith, .el nido, .demethos">Galicia, Arcania, Zenith, El Nido, Demethos</a></li>
			<li><a href="#" data-filter=".renegades">Renegades</a></li>
		</ul>
		<?php } ?>
	</div>
