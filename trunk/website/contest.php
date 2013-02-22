<?php
require_once 'inc/header.php';
?>

<?php
$q = $__database->query("
SELECT 
	assigned_to
FROM
	`beta_invite_keys`
WHERE 
	invite_key = 'BETADQ3A'
");

$check = $q->fetch_assoc();
?>

<?php
if (isset($check['assigned_to'])) {
	echo '<p class="lead alert alert-danger">Too late! Someone has won the contest!</p>';
}
?>

<center><img src="//<?php echo $domain; ?>/inc/img/contest.png"/>
<p class="lead">Mapler.me's Beta Contest! Guess right, and you're invited!</p></center>
<p>The current code is <b class="btn"?>BETAD??A</b> Once you've found out the code, quickly register! First to claim the code will be invited into Mapler.me!</p>

<form action="" method="post">
<div class="input-append">
  <input class="span11" id="appendedInputButton" name="beta" type="text" placeholder="Type your guess here!">
  <input type="submit" class="btn" style="position: relative;
right: 1px;
height: 36px;
border-radius: 0px;
width: 115px;" value="Go!"/>
</div>
</form>
<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['beta'] == 'BETADQ3A') {
	echo '<p class="lead alert alert-success">Congrats! You\'ve guessed correctly. Go <a href="//'.$domain.'/signup/"/>sign up now!</a></p>';
}

else if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['beta'] !== 'BETADQ3A') {
	echo '<p class="lead alert alert-danger">Opps! You\'ve guessed incorrectly. Try again!</p>';
}
?>
<?php
require_once 'inc/footer.php';
?>
