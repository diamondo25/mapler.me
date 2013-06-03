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
			$localFile = $_FILES['file']['tmp_name']; // This is the entire file that was uploaded to a temp location.
			$filename = md5(basename($_FILES['file']['tmp_name'])) . '.' . $ext;
			$owner = $_loginaccount->GetID();
			$ftp = '$ftp';
			$fp = fopen($localFile, 'r');
	
			//Connecting to website.
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_USERPWD, "maplerme:".$ftp."->login");
			curl_setopt($ch, CURLOPT_URL, 'ftp://direct.cdn.mapler.me/cdn.mapler.me/media/'.$filename);
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
		<p>You can find it by visiting <a href='http://cdn.mapler.me/media/<?php echo $filename; ?>' target='_blank'>http://cdn.mapler.me/media/<?php echo $filename; ?></a></p>
		<hr />
		<p>Direct Link: <pre>http://cdn.mapler.me/media/<?php echo $filename; ?></pre>
		<p>Signature / BBCode: <pre>[img]http://cdn.mapler.me/media/<?php echo $filename; ?>[/img]</pre></p>
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
.status input[type="file"]
{
    position: absolute;
    top: 0;
    right: 0;
    margin: 0;
    border: solid transparent;
    border-width: 0 0 100px 200px;
    opacity: 0.0;
    filter: alpha(opacity=0);
    -o-transform: translate(250px, -50px) scale(1);
    -moz-transform: translate(-300px, 0) scale(4);
    direction: ltr;
    cursor: pointer;
}
.upload
	{
    display: inline-block;
padding: 4px 12px;
margin-bottom: 0;
font-size: 14px;
line-height: 20px;
text-align: center;
vertical-align: middle;
cursor: pointer;
color: #333;
text-shadow: 0 1px 1px rgba(255,255,255,0.75);
background-color: #f5f5f5;
background-image: -moz-linear-gradient(top,#fff,#e6e6e6);
background-image: -webkit-gradient(linear,0 0,0 100%,from(#fff),to(#e6e6e6));
background-image: -webkit-linear-gradient(top,#fff,#e6e6e6);
background-image: -o-linear-gradient(top,#fff,#e6e6e6);
background-image: linear-gradient(to bottom,#fff,#e6e6e6);
background-repeat: repeat-x;
filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffffffff',endColorstr='#ffe6e6e6',GradientType=0);
border-color: #e6e6e6 #e6e6e6 #bfbfbf;
border-color: rgba(0,0,0,0.1) rgba(0,0,0,0.1) rgba(0,0,0,0.25);
filter: progid:DXImageTransform.Microsoft.gradient(enabled = false);
border: 1px solid #bbb;
border-bottom-color: #a2a2a2;
-webkit-border-radius: 4px;
-moz-border-radius: 4px;
border-radius: 4px;
-webkit-box-shadow: inset 0 1px 0 rgba(255,255,255,.2),0 1px 2px rgba(0,0,0,.05);
-moz-box-shadow: inset 0 1px 0 rgba(255,255,255,.2),0 1px 2px rgba(0,0,0,.05);
box-shadow: inset 0 1px 0 rgba(255,255,255,.2),0 1px 2px rgba(0,0,0,.05);
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
				<label class="upload">
				<span>Select image..</span>
				<input type="file" name="file" id="file" class="status">
				</label>&nbsp;
				<input type="submit" class="btn" value="Send!">			
			</form>
		</div>
	</div>
</div>

<?php
endif;
?>
      
<?php require_once __DIR__.'/../inc/footer.php'; ?>