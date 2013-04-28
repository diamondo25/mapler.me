<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['name'], $_POST['nick'], $_POST['bio'], $_POST['email'])) {
	$name = htmlentities($_POST['name'], ENT_COMPAT, 'UTF-8');
	$nick = htmlentities($_POST['nick'], ENT_COMPAT, 'UTF-8');
	$bio = htmlentities($_POST['bio'], ENT_COMPAT, 'UTF-8');
	$email = htmlentities($_POST['email'], ENT_COMPAT, 'UTF-8');
	
	$_loginaccount->SetFullname($name);
	$_loginaccount->SetNickname($nick);
	$_loginaccount->SetBio($bio);
	$_loginaccount->SetEmail($email);
	$_loginaccount->Save();
}


?>
			<p class="lead">Profile <sub>(Profile settings and Options)</sub></p>
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
						<textarea class="span2" style="width:210px;max-width:210px;" type="text" name="bio" id="inputBio"><?php echo $_loginaccount->GetBio(); ?></textarea>
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
			</form>