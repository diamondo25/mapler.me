<?php
if ($_loggedin) {
include_once('twitter.class.php');
?>

<script type="text/javascript">
function RemoveStatus(id) {
	if (confirm("Are you sure you want to delete this status?")) {
		document.location.href = '?remove=' + id;
	}
}
</script>

<script type="text/javascript">
function ConnectTwitter() {
		document.location.href = '?twitter';
}
</script>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['twitter'])) {
$connection = new TwitterOAuth('AeH4Ka2jIhiBWASIQUEQ', 'RjHPE4FXqsznLGohdHzSDnOeIuEucnQ6fPc0aNq8sw');
$request_token = $connection->getRequestToken(BASE_LINK_URL . 'callback.php');
$_SESSION['oauth_token'] = $token = $request_token['oauth_token'];
$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
}
?>

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
	elseif ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['remove'])) {
		// Removing status
		$id = intval($_GET['remove']);
		
		if ($_loginaccount->GetAccountRank() > RANK_DEVELOPER) {
			$__database->query("DELETE FROM social_statuses WHERE id = ".$id."");
		}
		else {
			$__database->query("DELETE FROM social_statuses WHERE id = ".$id." AND account_id = ".$_loginaccount->GetId());
		}
?>
<p class="lead alert-info alert">The status was successfully deleted.</p>
<?php
	}

?>

<div id="post" class="collapse">
	<form method="post" style="padding-bottom:10px;border-bottom:1px solid rgba(0,0,0,0.2);">
		<h3 id="myModalLabel">Post a status?</h3>
		<textarea name="content" style="height:100px; max-height:100px;" class="post-resize" id="post-status" placeholder="Type your status here!"></textarea>
		<button type="submit" class="btn btn-large">Post!</button>
	</form>
</div>

<?php
}
?>