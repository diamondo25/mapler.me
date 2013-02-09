<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['name'], $_POST['nick'], $_POST['bio'], $_POST['email'])) {
	$_loginaccount->SetFullname($_POST['name']);
	$_loginaccount->SetNickname($_POST['nick']);
	$_loginaccount->SetBio($_POST['bio']);
	$_loginaccount->SetEmail($_POST['email']);
	$_loginaccount->Save();
}


?>

		<div class="span7">
			<p class="lead">General <sub>(General settings and Options)</sub></p>
			<form class="form-horizontal" method="post">
				<div class="control-group">
					<label class="control-label" for="inputName">Name (Full Name)</label>
					<div class="controls">
						<input type="text" name="name" id="inputName" value="<?php echo $_loginaccount->GetFullname(); ?>" />
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="inputNick">Nickname</label>
					<div class="controls">
						<input type="text" name="nick" id="inputNick" value="<?php echo $_loginaccount->GetNickname(); ?>" />
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="inputNick">Bio</label>
					<div class="controls">
						<textarea class="span2" style="width:210px;max-width:210px;" type="text" name="bio" id="inputBio" value="<?php echo $_loginaccount->GetBio(); ?>" /><?php echo $_loginaccount->GetBio(); ?></textarea>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="inputEmail">Email</label>
					<div class="controls">
						<input type="text" name="email" id="inputEmail" value="<?php echo $_loginaccount->GetEmail(); ?>" />
					</div>
				</div>
				<div class="control-group">
					<div class="controls">
						<button type="submit" class="btn btn-primary">Save</button>
					</div>
				</div>
				</div>
			</form>
		</div>