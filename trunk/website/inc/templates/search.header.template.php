<script type="text/javascript">
function GetPlayer() {
	$('input[name=type]').val('character');
	$("input").attr("placeholder", "Search for a character?");
}
</script>

<?php
//default is status so results always show
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

.search-menu {
  width: 228px;
  padding: 0;
  background-color: #fff;
  -webkit-border-radius: 6px;
     -moz-border-radius: 6px;
          border-radius: 6px;
  -webkit-box-shadow: 0 1px 4px rgba(0,0,0,.065);
     -moz-box-shadow: 0 1px 4px rgba(0,0,0,.065);
          box-shadow: 0 1px 4px rgba(0,0,0,.065);
}
.search-menu > li > a {
  display: block;
  width: 190px \9;
  margin: 0 0 -1px;
  padding: 8px 14px;
  border: 1px solid #e5e5e5;
}
.search-menu > li:first-child > a {
  -webkit-border-radius: 6px 6px 0 0;
     -moz-border-radius: 6px 6px 0 0;
          border-radius: 6px 6px 0 0;
}
.search-menu > li:last-child > a {
  -webkit-border-radius: 0 0 6px 6px;
     -moz-border-radius: 0 0 6px 6px;
          border-radius: 0 0 6px 6px;
}
.search-menu > .active > a {
  position: relative;
  z-index: 2;
  padding: 9px 15px;
  border: 0;
  text-shadow: 0 1px 0 rgba(0,0,0,.15);
  -webkit-box-shadow: inset 1px 0 0 rgba(0,0,0,.1), inset -1px 0 0 rgba(0,0,0,.1);
     -moz-box-shadow: inset 1px 0 0 rgba(0,0,0,.1), inset -1px 0 0 rgba(0,0,0,.1);
          box-shadow: inset 1px 0 0 rgba(0,0,0,.1), inset -1px 0 0 rgba(0,0,0,.1);
}
/* Chevrons */
.search-menu .icon-chevron-right {
  float: right;
  margin-top: 2px;
  margin-right: -6px;
  opacity: .25;
}
.search-menu > li > a:hover {
  background-color: #f5f5f5;
}
.search-menu a:hover .icon-chevron-right {
  opacity: .5;
}
.search-menu .active .icon-chevron-right,
.search-menu .active a:hover .icon-chevron-right {
  background-image: url(../img/glyphicons-halflings-white.png);
  opacity: 1;
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
		<?php if ($searching == '') { ?>
		<hr />
		<ul id="filters" class="nav nav-list search-menu">
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
		<p>We're aware worlds are missing. This is currently being tested.</p>
		<?php } ?>
	</div>
