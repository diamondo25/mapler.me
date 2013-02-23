<?php

$to = 'blablaechthema@hotmail.com';
$nickname = 'derp';

// subject
$subject = 'Mapler.me - Welcome!';

// message

$message = file_get_contents('inc/email_signup.template.php');
$message = str_replace("{NICK}", $nickname, $message);

// To send HTML mail, the Content-type header must be set
$headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
$headers .= 'From: Mapler.me <no-reply@mapler.me>' . "\r\n";

// Mail it
mail($to, $subject, $message, $headers);
?>