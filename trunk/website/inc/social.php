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
if ($antispam && $_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['content']) {
	// Oh jolly
	$antispam = false;
	$minutes_timeout = 1;
	$_loginaccount->SetConfigurationOption('last_status_sent', date("Y-m-d H:i:s"));
	
	$content = htmlentities($_POST['content']);
	
	$char_config = $_loginaccount->GetConfigurationOption('character_config', array('characters' => array(), 'main_character' => null));
	$has_characters = !empty($char_config['main_character']);
	
	// set internally
	$accid = $_loginaccount->GetId();
	$nicknm = $_loginaccount->GetNickname();
	
	if ($has_characters):
	$chr = $char_config['main_character'];
	else:
	$chr = '';
	endif;
	
	$__database->query("INSERT INTO social_statuses VALUES (NULL, '".$accid."', '".$nicknm."', '".$chr."', '".$content."', 0, NOW(), 0)");
?>
<p class="lead alert-success alert">Sending to Maple Admin.. checking.. success! Status posted.</p>
<?php	
}

elseif ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['removeid'])) {
	// Removing status
	
	$id = $__database->real_escape_string($_GET['removeid']);
	
	$__database->query("DELETE FROM social_statuses WHERE id = '".$id."' AND account_id = ".$_loginaccount->GetId());
?>
<p class="lead alert-info alert">The status was successfully deleted.</p>
<?php
}

?>

<?php if (!$antispam): ?>
<p class="lead alert-error alert">Please wait <?php echo $minutes_timeout; ?> minute<?php echo $minutes_timeout > 1 ? 's' : ''; ?> before posting another message. :)</p>
<?php else: ?>

<div class="row">
	<div class="span4">
			<form method="post">
			<div class="input-append">
					<textarea name="content" id="appendedInputButton" style="height:37px;max-height:37px;"></textarea>
					<button type="submit" class="btn btn-large" style="padding:16px;">Post!</button>
					</div>
			</form>
		</div>
	<div class="span8">
	<p class="lead">New: Status updates now implemented.<br/>
		<sub>Temporarily, messages can only be sent once a minute.</sub>
	</p>
	</div>
</div>

<?php endif; ?>