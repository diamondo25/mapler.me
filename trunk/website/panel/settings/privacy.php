		<div class="span7">
<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['passwordOld'],$_POST['passwordNew1'],$_POST['passwordNew2'])) {
	$error = '';
	
	if ($error == '') {
		if ($_POST['passwordNew1'] !== $_POST['passwordNew2']) {
			// Not the same D:
			$error = 'You entered 2 different passwords. Try again.';
		}
	}
	
	if ($error == '') {
		$query = $__database->query("SELECT password, salt FROM accounts WHERE id = ".$_loginaccount->GetId());
		if ($query->num_rows == 1) {
			$row = $query->fetch_assoc();
			
			$encrypted = GetPasswordHash($_POST['passwordOld'], $row['salt']);
			if ($encrypted === $row['password']) {
				// New hash!
			
				$salt = '';
				for ($i = 0; $i < 8; $i++) {
					$salt .= chr(0x30 + rand(0, 20));
				}
				
				$encrypted = GetPasswordHash($_POST['passwordNew2'], $salt);
				
				$__database->query("UPDATE accounts SET password = '".$encrypted."', salt = '".$salt."' WHERE id = ".$_loginaccount->GetId());
?>
<p class="lead alert-info alert">Your password has been successfully changed!</p>
<?php
			}
		}
		else {
			$error = 'wat.';
		}
	}
	
	if ($error != '') {
?>
<p class="lead alert-warn alert"><?php echo $error; ?></p>
<?php
	}
}

?>

			<p class="lead">Privacy <sub>(Privacy settings and security)</sub></p>
			<form class="form-horizontal" method="post">
				
				<div class="control-group">
					<label class="control-label" for="oldPW">Old password</label>
					<div class="controls">
						<input type="password" name="passwordOld" id="oldPW" value="" />
					</div>
				</div>
				
				<div class="control-group">
					<label class="control-label" for="newPW1">New password</label>
					<div class="controls">
						<input type="password" name="passwordNew1" id="newPW1" value="" />
					</div>
				</div>
				
				<div class="control-group">
					<label class="control-label" for="newPW2">New password (again)</label>
					<div class="controls">
						<input type="password" name="passwordNew2" id="newPW2" value="" />
					</div>
				</div>
				
				<div class="control-group">
					<div class="controls">
						<button type="submit" class="btn btn-primary">Save</button>
					</div>
				</div>
			</form>
		</div>