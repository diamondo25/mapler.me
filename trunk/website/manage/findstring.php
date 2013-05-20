<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['thing'], $_GET['ajax'])) {
	header('Content-type: application/json');
	require_once __DIR__.'/../inc/classes/database.php';
	$searching = $__database->real_escape_string($_POST['thing']);
	$q = $__database->query("SELECT objecttype, objectid, `key`, `value` FROM strings WHERE `value` LIKE '%".$searching."%' LIMIT 100");
	
	$tmp = array();
	while ($row = $q->fetch_assoc())
		$tmp[] = $row;
		
	die(json_encode($tmp));
}


require_once __DIR__.'/../inc/header.php';
?>
<script>
function Search() {
	$.ajax({
		type: 'POST',
		url: '?ajax',
		data: {thing: $('#what').val()},
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
			
			table.html(totaldump);
		}
	});
}

</script>

<div class="input-append">
	<input type="text" id="what" class="span7" /><button class="btn" onclick="Search()">Search...</button>
</div>
<table id="results" style="width: 100%;" cellpadding="7" cellspacing="3"></table>


<?php
require_once __DIR__.'/../inc/footer.php';
?>