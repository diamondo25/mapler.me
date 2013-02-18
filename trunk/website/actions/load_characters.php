<?php
if (!$_loggedin) die();
?>
<h2>Load your characters</h2>
When you enter your Nexon America credentials below and press 'Login and retrieve characters!', a program will connect to the GMS loginserver and retrieves all your characters. Then, it'll store its data inside a nice database which you can query with the 'My Characters' page .
<p class="lead alert-error alert">This takes up around 2 minutes!</p>
<form action="" method="post">
<fieldset>
<div class="control-group">
	<label class="control-label" for="inputusername">Username</label>
	<div class="controls">
		<input type="text" id="inputusername" name="username" placeholder="" value="" />
	</div>
</div>
<div class="control-group">
	<label class="control-label" for="inputpassword">Password</label>
	<div class="controls">
		<input type="password" id="inputpassword" name="password" placeholder="" value="" />
	</div>
</div>
<div class="form-actions">
	<button type="submit" class="btn btn-danger btn-primary">Login and retrieve characters!</button>
</div>
</fieldset>
</form>

<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$username = $_POST["username"];
	$password = $_POST["password"];
	if (strlen($username) < 4 || strlen($username) > 30) {
		die("Incorrect username!");
	}
	if (strlen($password) < 4 || strlen($password) > 30) {
		die("Incorrect password!");
	}
?>
<?php
	$descriptorspec = array(
	   0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
	   1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
	   2 => array("file", "/tmp/error-output.txt", "a") // stderr is a file to write to
	);
	
	$cwd = '/mal';
	
	$process = proc_open('mono --debug /mal/MapleAccountLogger.exe '.escapeshellarg($username).' '.escapeshellarg($password).' '.$_logindata['id'].' 2>&1', $descriptorspec, $pipes, $cwd);
	
	if (is_resource($process)) {
		$data = stream_get_contents($pipes[1]);
		fclose($pipes[1]);
	
		$return_value = proc_close($process);
	}
	else {
		$data = "ERROR";
	}
	
	$matches = array();
	preg_match("/\[(.*)\] \[(\d+)\] (\w+) \((\d+)\)/s", $data, $matches);
	if (sizeof($matches) > 0) {
		$username = $matches[3];
	}
?>
<a href="/my-characters">Click here to view the result!</a>
<legend><button class="btn" data-toggle="collapse" data-target="#output" href="#output">Output</button></legend>
<div id="output" class="collapse accordion-body">
<pre>
<?php echo $data; ?>
</pre>
</div>
<?php
}
?>