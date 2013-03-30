<?php
if ($_loggedin) {
?>

<script type="text/javascript">
function RemoveStatus(id) {
	if (confirm("Are you sure you want to delete this status?")) {
		document.location.href = '?removeid=' + id;
	}
}
</script>
<?php

// Preventing spamming of form. [disabled]
//$antispam = true;
//if ($_loginaccount->GetConfigurationOption('last_status_sent', 0) != 0) {
//
//}

// If antispam passes, push status
	if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['content'])) {

		$content = nl2br(htmlentities(strip_tags($_POST['content'])));
		$dc = isset($_POST['dc']) ? 1 : 0;

		$char_config = $_loginaccount->GetConfigurationOption('character_config', array('characters' => array(), 'main_character' => null));
		$has_characters = !empty($char_config['main_character']);

		// set internally
		$accid = $_loginaccount->GetId();
		$nicknm = $_loginaccount->GetNickname();
		$chr = $has_characters ? $char_config['main_character'] : '';
		
		if ($content == '') {
?>
		<p class="lead alert-danger alert">Error: That status was left blank, retry?</p>
<?php
		}
		
		else {
		
		$_loginaccount->SetConfigurationOption('last_status_sent', date("Y-m-d H:i:s"));

		$__database->query("INSERT INTO social_statuses VALUES (NULL, ".$accid.", '".$__database->real_escape_string($nicknm)."', '".$__database->real_escape_string($chr)."', '".$__database->real_escape_string($content)."', ".$dc.", NOW(), 0)");
?>
<p class="lead alert-success alert">The status was successfully posted!</p>
<?php
		}
	}
	elseif ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['removeid'])) {
		// Removing status
		$id = intval($_GET['removeid']);
		$__database->query("DELETE FROM social_statuses WHERE id = ".$id." AND account_id = ".$_loginaccount->GetId());
?>
<p class="lead alert-info alert">The status was successfully deleted.</p>
<?php
	}

?>

<div id="PostStatus" class="modal-share hide fade" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<form method="post">
	<div class="modal-header">
		<h3 id="myModalLabel">Post a status?</h3>
	</div>
	<div class="modal-body">
		<textarea name="content" style="height:100px; max-height:100px; width:550px; max-width: 550px; border: none; -moz-user-select: text;" placeholder="Type your status here!"></textarea>
	</div>
	<div class="modal-footer">
		<button type="submit" class="btn btn-large">Post!</button>
	</div>
	</form>
</div>

<?php
}
?>