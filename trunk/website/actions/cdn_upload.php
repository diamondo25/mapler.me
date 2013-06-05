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
	$imagecheck = getimagesize($_FILES['file']['tmp_name']);
		if(!in_array($ext,$allowed)) {
			echo "<p class='alert alert-danger'>Error: Only picture files can be uploaded to Mapler.me!</p>";
		}
		else if(!$imagecheck) {
			echo "<p class='alert alert-danger'>Error: Only picture files can be uploaded to Mapler.me!</p>";
		}
		else {
			$localFile = $_FILES['file']['tmp_name']; // This is the entire file that was uploaded to a temp location.
			$filename = md5(basename($_FILES['file']['tmp_name'])) . '.' . $ext;
			$filenamewithoutextention = md5(basename($_FILES['file']['tmp_name']));
			$owner = $_loginaccount->GetID();
			$ftp = '$ftp';
			$fp = fopen($localFile, 'r');
	
			//Connecting to website.
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_USERPWD, "maplerme:".$ftp."->login");
			curl_setopt($ch, CURLOPT_URL, 'ftp://direct.cdn.mapler.me/i.mapler.me/i/'.$filename);
			curl_setopt($ch, CURLOPT_UPLOAD, 1);
			curl_setopt($ch, CURLOPT_INFILE, $fp);
			curl_setopt($ch, CURLOPT_INFILESIZE, filesize($localFile));
			curl_setopt($ch, CURLOPT_NOPROGRESS, false);
			curl_exec ($ch);
			if (curl_errno($ch))
				$msg = curl_error($ch);
			else {
		?>
		<div class="status">
		<p class='lead'>Your image has been successfully uploaded to Mapler.me!</p>
		<center><img src="http://i.mapler.me/i/<?php echo $filename; ?>" class="picture"/></center>
		<p>View your image! <a href='http://i.mapler.me/view/<?php echo $filenamewithoutextention; ?>' target='_blank'>http://i.mapler.me/view/<?php echo $filenamewithoutextention; ?></a></p>
		<hr />
		<p>Direct Link: <pre>http://i.mapler.me/i/<?php echo $filename; ?></pre>
		<p>Signature / BBCode: <pre>[img]http://i.mapler.me/i/<?php echo $filename; ?>[/img]</pre></p>
		</div>
		<?php
			$q = $__database->query("
			INSERT INTO cdn_log 
			(id, owner, file) VALUES('?','$owner', '$filename') 
			");
			}
			curl_close ($ch);
		}
	}
?>

<style>

.lead {
	font-size:30px;
}

.picture {
	box-shadow: 0 1px 2px rgba(0,0,0,0.15);
	border: 1px solid #ddd;
	margin: 10px;
	margin-top: 0px;
	margin-bottom: 0px;
	-webkit-border-radius:3px;
	-moz-border-radius:3px;
	border-radius:3px;
	max-width: 920px;
}

</style>
<div class="row">
	<div class="span4">
		<div class="status">
			<center>
				<p style="font-size:40px;">
					<img src="//<?php echo $domain; ?>/inc/img/shadowlogo.png" style="width:50px;position:relative;top:10px;"/>mapler.cdn
				</p>
			<hr />
			<p>Finally, something simple!</p>
			</center>
		</div>
		<p>Mapler.me offers a free service for uploading <b>pictures</b> to share or include in status messages. When uploading a file, you will be given a link as well as a code to include it in statuses.</p>
	</div>
	<div class="span8">
		<div class="status">
			<form method="POST" enctype="multipart/form-data" style="margin:0 !important;">
				<input type="file" name="file" id="file" class="status">
				<br/>
				<input type="submit" class="btn" value="Send!">			
			</form>
		</div>
	</div>
</div>

<?php
endif;
?>
      
<?php require_once __DIR__.'/../inc/footer.php'; ?>