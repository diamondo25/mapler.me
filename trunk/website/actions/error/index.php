<?php require_once '../../inc/header.php'; ?>

      <?php

// Get Variables
$error = $_SERVER['REDIRECT_STATUS'];
$requested_url = $_SERVER['REQUEST_URI'];
$server_name = $_SERVER['SERVER_NAME'];
$subject2 = "IP ONLY";

// Different error messages to display
switch ($error) {

# Error 400 - Bad Request
case 400:
$errorname = 'Error 400 - Bad Request';
$errordesc = '<h1>Bad Request</h1>
  <h2>Error Type: 400</h2>
  <p>
  The URL that you requested &#8212; http://'.$server_name.$requested_url.' &#8212; does not exist on this server. You might want to re-check the spelling and the path.</p>
  <p>You can use the menu at the top of the page or at the right to navigate to another section.</p>';
break;

# Error 401 - Authorization Required
case 401:
$errorname = 'Error 401 - Authorization Required';
$errordesc = '<h1>Authorization Required</h1>
  <h2>Error Type: 401</h2>
  <p>
  The URL that you requested requires pre-authorization to access.</p>';
break;

# Error 403 - Access Forbidden
case 403:
$errorname = 'Error 403 - Access Forbidden';
$errordesc = '<div class="page-header"><h1>Access Forbidden <small>(erm, *glares*)</small></h1></div>
  <p>
  <b>Access to the URL that you requested is forbidden.</b></p>';
break;

# Error 404 - Page Not Found
case 404:
$errorname = 'Error 404 - Page Not Found';
$errordesc = '
<div class="character-brick">
<div class="caption">Oh no, 404! <small>(that page doesn\'t exist)</small></div>
<center><img src="https://dl.dropbox.com/u/22875564/mapler.me.resources/blackmage.gif"/>
  <p>
  Ooops! <b>http://'.$server_name.$requested_url.'</b> &#8212; cannot be found!</p>
  <p>Please use the menu above to return to another page. ♥</p></center>';
break;

# Error 500 - Server Configuration Error
case 500:
$errorname = 'Error 500 - Server Configuration Error';
$errordesc = '<h1>Server Configuration Error</h1>
  <h2>Error Type: 500</h2>
  <p>
  The URL that you requested &#8212; <a href="//'.$server_name.$requested_url.'">http://'.$server_name.$requested_url.'</a> &#8212; resulted in a server configuration error. It is possible that the condition causing the problem will be gone by the time you finish reading this.</p>';
break;

# Unknown error
default:
$errorname = 'Unknown Error';
$errordesc = '<h2>Unknown Error</h2>
  <p>The URL that you requested &#8212; <a href="//'.$server_name.$requested_url.'">http://'.$server_name.$requested_url.'</a> &#8212; resulted in an unknown error. It is possible that the condition causing the problem will be gone by the time you finish reading this. </p>';

}

// Display selected error message
echo($errordesc);

?>
      
<?php require_once '../../inc/footer.php'; ?>