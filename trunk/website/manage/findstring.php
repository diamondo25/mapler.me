<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['1'], $_GET['strings'])) {
	header('Content-type: application/json');
	require_once __DIR__.'/../inc/classes/database.php';
	$searching = $__database->real_escape_string($_POST['1']);
	$q = $__database->query("SELECT objecttype, objectid, `key`, `value` FROM strings WHERE `value` LIKE '%".$searching."%' OR `objectid` LIKE '%".$searching."%' ORDER BY objectid DESC LIMIT 200");
	
	$tmp = array();
	while ($row = $q->fetch_assoc())
		$tmp[] = $row;
		
	die(json_encode($tmp));
}

elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['2'], $_GET['accounts'])) {
	header('Content-type: application/json');
	require_once __DIR__.'/../inc/classes/database.php';
	$searching = $__database->real_escape_string($_POST['2']);
	$q = $__database->query("SELECT id, username, email, last_ip, account_rank FROM accounts WHERE `username` LIKE '%".$searching."%' OR `last_ip` LIKE '%".$searching."%' ORDER BY id DESC LIMIT 200");
	
	$tmp = array();
	while ($row = $q->fetch_assoc())
		$tmp[] = $row;
		
	die(json_encode($tmp));
}

elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['3'], $_GET['characters'])) {
	header('Content-type: application/json');
	require_once __DIR__.'/../inc/classes/database.php';
	$searching = $__database->real_escape_string($_POST['3']);
	$q = $__database->query("SELECT internal_id, name, last_update FROM characters WHERE `name` LIKE '%".$searching."%' ORDER BY internal_id DESC LIMIT 200");
	
	$tmp = array();
	while ($row = $q->fetch_assoc())
		$tmp[] = $row;
		
	die(json_encode($tmp));
}


require_once __DIR__.'/../inc/header.php';
?>

<style>
input[type=text] {
	padding: 14px;
	box-shadow: none;
	font-size: 16px;
}

.search_button {
	padding: 14px 28px;
	margin: -10px 0 0 -70px;
	border-radius: 0 3px 3px 0;
	-moz-border-radius: 0 3px 3px 0;
	-webkit-border-radius: 0 3px 3px 0;
}
</style>

<script>
function SearchStrings() {
	$.ajax({
		type: 'POST',
		url: '?strings',
		data: {1: $('#what1').val()},
		success: function (data) {
			var table = $('#results');
			var totaldump = '';
			for (var index in data) {
				var dmp = '';
				for (var col in data[index]) {
					dmp += '<td>' + data[index][col] + '</td>';
				}
				totaldump += '<tr>' + dmp + '</tr>';
			}
			
			$('#rowcount').html('Rows: ' + data.length);
			
			table.html(totaldump);
		}
	});
}

function SearchAccounts() {
	$.ajax({
		type: 'POST',
		url: '?accounts',
		data: {2: $('#what2').val()},
		success: function (data) {
			var table = $('#results');
			var totaldump = '';
			for (var index in data) {
				var dmp = '';
				for (var col in data[index]) {
					dmp += '<td>' + data[index][col] + '</td>';
				}
				totaldump += '<tr>' + dmp + '</tr>';
			}
			
			$('#rowcount').html('Rows: ' + data.length);
			
			table.html(totaldump);
		}
	});
}

function SearchCharacters() {
	$.ajax({
		type: 'POST',
		url: '?characters',
		data: {3: $('#what3').val()},
		success: function (data) {
			var table = $('#results');
			var totaldump = '';
			for (var index in data) {
				var dmp = '';
				for (var col in data[index]) {
					if (col == 'name') {
						dmp += '<td><a href="/player/' + data[index][col] + '">' + data[index][col] + '</a></td>';
					}
					else {
						dmp += '<td>' + data[index][col] + '</td>';
					}
				}
				totaldump += '<tr>' + dmp + '</tr>';
			}
			
			$('#rowcount').html('Rows: ' + data.length);
			
			table.html(totaldump);
		}
	});
}

</script>

	<form onsubmit="SearchStrings(); return false;">
		<input type="text" id="what1" class="span7" onkeyup="SearchStrings()"  placeholder="Search for strings ..."/>
		<a type="" class="search_button btn btn-info"><i class="icon-search icon-white"></i></a>
	</form>
	<form onsubmit="SearchAccounts(); return false;">
		<input type="text" id="what2" class="span7" onkeyup="SearchAccounts()" placeholder="Search for Mapler.me accounts ..."/>
		<a type="" class="search_button btn btn-info"><i class="icon-search icon-white"></i></a>
	</form>
	<form onsubmit="SearchCharacters(); return false;">
		<input type="text" id="what3" class="span7" onkeyup="SearchCharacters()" placeholder="Search for characters ..."/>
		<a type="" class="search_button btn btn-info"><i class="icon-search icon-white"></i></a>
	</form>
	
<span id="rowcount"></span>
<table id="results" style="width: 100%;" cellpadding="7" cellspacing="3"></table>


<?php
require_once __DIR__.'/../inc/footer.php';
?>