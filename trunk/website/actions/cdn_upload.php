<?php require_once __DIR__.'/../inc/header.php'; ?>

<?php
if (!$_loggedin):
?>
<center>
	<p class="lead status">Opps! You must be logged in to use our CDN!</p>
</center>
<?php
else:

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {

$allowed =  array('gif','png','jpg','jpeg');
$filename = $_FILES['file']['name'];
$ext = pathinfo($filename, PATHINFO_EXTENSION);
if(!in_array($ext,$allowed) ) {
    echo "<p class='alert alert-danger'>Error: Only picture files can be uploaded to Mapler.me!</p>";
}
else {
	$target_url = 'http://cdn.mapler.me/add.php';
	$file = $_FILES['file']['tmp_name'];
	$owner = $_loginaccount->GetID();
	
	$post = array('owner'=> $owner,'file'=>'@'. $_FILES['file']['tmp_name']);
 
    $ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$target_url);
	curl_setopt($ch, CURLOPT_POST,1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	$result=curl_exec ($ch);
	curl_close ($ch);
}
}
?>

<style>

.lead {
	font-size:30px;
}

</style>
<div class="row">
	<div class="span12">
		<center>
			<p style="font-size:40px;"><img src="//<?php echo $domain; ?>/inc/img/icon.png" style="width:50px;position:relative;top:10px;"/>mapler.cdn</p>
			<p><i>In Soviet Russia, maplers is you!</i></p>
			<hr />
		</center>
		<p>Mapler.me offers a free service for uploading <b>pictures</b> to share or include in status messages. When uploading a file, you will be given a link as well as a code to include it in statuses.</p>
	</div>
</div>
	<form method="POST" enctype="multipart/form-data">
		<input type="file" name="file" id="file" class="status">
		<input type="submit" class="span12 btn btn-large" value="Upload File!">
	</form>

<?php
endif;
?>
      
<?php require_once __DIR__.'/../inc/footer.php'; ?>