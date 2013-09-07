<?php
    
    require_once __DIR__.'/../inc/functions.php';
    
    $success = '';
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm'])):
    
		$query = $__database->query("SELECT password, salt FROM accounts WHERE id = ".$_loginaccount->GetId());
		if ($query->num_rows == 1) {
			$row = $query->fetch_assoc();
			
			$encrypted = GetPasswordHash($_POST['confirm'], $row['salt']);
			if ($encrypted === $row['password']) {
    			$success = '2';
			}
			else {
    			$success = '1';
			}
        }
    
    if($success == '2') {
        $wastheirid = $_loginaccount->GetId();
        unset($_SESSION['username']);
        session_destroy();
        SetMaplerCookie('login_session', '', -100);

$a1 = $__database->query("DELETE FROM social_statuses WHERE account_id = ".$wastheirid);
$a = $a1->affected_rows;
$a1->free;

$b1 = $__database->query("
DELETE
	chr.*
FROM 
	characters chr 
LEFT JOIN 
	users usr 
	ON 
		usr.ID = chr.userid 
WHERE 
	usr.account_id = '".$__database->real_escape_string($wastheirid)."'");
$b = $b1->affected_rows;
$b1->free;

$finish = $__database->query("DELETE FROM accounts WHERE id = ".$wastheirid);
$finish->free;
        require_once __DIR__.'/../inc/header.php';
    }
?>

<?php if($success == '2'):
?>
        <p class="lead alert alert-info"><i class="icon-exlamation-sign"></i> Your account has been removed.</p>
        <ol>
            <li>Your account itself, and all related information has been removed successfully.</li>
            <li>Al characters has been removed successfully.</li>
            <li>All status messages have been removed successfully.</li>
            <li>All other matters handled successfully.</li>
        </ol>
        <p>If you have any feedback for our team, send us an email at support@mapler.me! :)</p>
        <p><b>We wish you a great day, and happy mapling!</b></p>
<?php
    elseif($success == '1'):
    require_once __DIR__.'/../inc/header.php';
?>
    <p class="lead alert alert-danger"><i class="icon-exlamation-sign"></i> You typed an incorrect password. Please try again.</p>
    <div class="row">
    <div class="span12">
        <form method="post">
            <input type="password" name="confirm" placeholder="Password"/>
            <input type="submit" class="btn btn-danger" value="Delete my account"/>
        </form>
    </div>
</div>
<?php
    endif;
    elseif($success == NULL):
    require_once __DIR__.'/../inc/header.php';
?>

<div class="row">
    <div class="span12">
        <?php if($_loggedin):
        ?>
        <p class="lead alert alert-info"><i class="icon-question-sign"></i> We're sorry to see you go! To protect your account please type your password below:</p>
        <form method="post">
            <input type="password" name="confirm" placeholder="Password"/>
            <input type="submit" class="btn btn-danger" value="Delete my account"/>
        </form>
        <?php
        else:
        DisplayError(1);
        endif; ?>
    </div>
</div>
<?php
    endif;
require_once __DIR__.'/../inc/footer.php';
?>