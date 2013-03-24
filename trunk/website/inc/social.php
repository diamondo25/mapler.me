<script type="text/javascript">
function RemoveStatus(id) {
	if (confirm("Are you sure you want to delete this status?")) {
		document.location.href = '?removeid=' + id;
	}
}
</script>
<?php

// Preventing spamming of form.
$antispam = true;
if ($_loginaccount->GetConfigurationOption('last_status_sent', 0) != 0) {
	$tmp = strtotime($_loginaccount->GetConfigurationOption('last_status_sent'));
	$tmp += 1 * 60; // 1 minute

	if (time() < $tmp) { // 1 minute
		$antispam = false;
		$minutes_timeout = ceil(($tmp - time()) / 60);
	}

}

// If antispam passes, push status
if ($antispam && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['content'])) {
	// Oh jolly
	$antispam = false;
	$minutes_timeout = 1;
	$_loginaccount->SetConfigurationOption('last_status_sent', date("Y-m-d H:i:s"));

	$content = nl2br(htmlentities(strip_tags($_POST['content'])));
	$dc = isset($_POST['dc']) ? 1 : 0;

	$char_config = $_loginaccount->GetConfigurationOption('character_config', array('characters' => array(), 'main_character' => null));
	$has_characters = !empty($char_config['main_character']);

	// set internally
	$accid = $_loginaccount->GetId();
	$nicknm = $_loginaccount->GetNickname();

	$chr = $has_characters ? $char_config['main_character'] : '';


	$__database->query("INSERT INTO social_statuses VALUES (NULL, ".$accid.", '".$__database->real_escape_string($nicknm)."', '".$__database->real_escape_string($chr)."', '".$__database->real_escape_string($content)."', ".$dc.", NOW(), 0)");
?>
<p class="lead alert-success alert">Sending to Maple Admin.. checking.. success! Status posted.</p>
<?php
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

<?php if (!$antispam): ?>
<p class="lead alert-error alert">Please wait <?php echo $minutes_timeout; ?> minute<?php echo $minutes_timeout > 1 ? 's' : ''; ?> before posting another message. :)</p>
<?php else: ?>

<div id="PostStatus" class="modal-share hide fade" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"
style="-webkit-touch-callout: none;
-webkit-user-select: none;
-khtml-user-select: none;
-moz-user-select: none;
-ms-user-select: none;
user-select: none;">
  <div class="modal-header">
    <h3 id="myModalLabel">Post a status?</h3>
  </div>
  <div class="modal-body">
  	<form method="post">
    	<textarea name="content" style="height:100px; max-height:100px; width:550px; max-width: 550px; border: none;" placeholder="Type your status here!"></textarea>
  </div>
  <div class="modal-footer">
    <button type="submit" class="btn btn-large">Post!</button>
    <button type="button" class="btn btn-large" data-toggle="button" name="dc" value="1">Disable commenting?</button>
	</form>

  </div>
</div>

<?php endif; ?>