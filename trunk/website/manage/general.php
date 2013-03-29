<?php
require_once __DIR__.'/../inc/header.php';
?>

		<h4>Change the stream notice:</h4>

		<form method="post">
			<textarea name="updatetxt" class="span12" id="updatetxt" style="height:50px;" > <?php echo file_get_contents('notice.txt'); ?> </textarea>
			<button type="submit" class="btn">Update!</button>
		</form>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['updatetxt'])) {
	$updatetxt = $_POST['updatetxt']; //not protected from sql injection to prevent html / php added from derping.

	$filename = 'notice.txt';
	$somecontent = $updatetxt;

	if (is_writable($filename)) {

		if (!$handle = fopen($filename, 'w')) {
			 echo '<p class="alert info-danger">Could not open the sidebar. Protected?</p>';
			 exit;
		}

		// Write $somecontent to our opened file.
		if (fwrite($handle, $somecontent) === FALSE) {
			echo '<p class="alert info-danger">Could not write to sidebar.</p>';
			exit;
		}

		echo '<p class="alert info-sucess">Successfully updated sidebar!</p>';

		fclose($handle);

	}
	else {
		echo '<p class="alert info-danger">Error: The sidebar is not writable.</p>';
	}


}
?>
		<h4>Various functions and information:</h4>
		<button type="button" class="btn" onclick="location.href = '?clear_cache'">Clear Cache</button> <button type="button" class="btn" onclick="location.href = 'info.php'">View Apache/Php Info?</button>
		<br />
		<br />
<?php
if (isset($_GET['clear_cache'])) {
	$files = glob('../../cache/*');
	$i = 0;
	foreach($files as $file){
		if (is_file($file)) {
			unlink($file);
			$i++;
		}
	}
?>
<p class="alert info-danger">Notice: <?php echo $i; ?> cached files were deleted! </p>
<?php

	// Clear data caches
	apc_delete('data_cache');
	apc_delete('data_iteminfo_cache');
	apc_delete('data_itemoptions_cache');
}
?>
	
	<div class="accordion" id="accordion2">
	<h4>View Mapler.me records:</h4>
  <div class="accordion-group" style="margin-bottom:10px;">
    <div class="accordion-heading">
      <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseOne">
        <button class="btn">Mapler.me Accounts</button>
      </a>
    </div>
    <div id="collapseOne" class="accordion-body collapse">
      <div class="accordion-inner">
      <br/>
        <?php
	        $q = $__database->query("SELECT username FROM accounts ORDER BY username ASC");
	        while ($row = $q->fetch_row()) {
	    ?>
		<a href="//<?php echo $row[0]; ?>.<?php echo $domain; ?>/" class="btn btn-mini"><?php echo $row[0]; ?></a> 
		<?php
			}

			?>
      </div>
    </div>
  </div>
  <div class="accordion-group">
    <div class="accordion-heading">
      <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseTwo">
        <button class="btn">Mapler.me Characters</button>
      </a>
    </div>
    <div id="collapseTwo" class="accordion-body collapse">
      <div class="accordion-inner">
      <br/>
        <?php
	        $q = $__database->query("SELECT name FROM characters ORDER BY internal_id DESC");
	        while ($row = $q->fetch_row()) {
	    ?>
		<a href="//<?php echo $domain; ?>/player/<?php echo $row[0]; ?>" class="btn btn-mini"><?php echo $row[0]; ?></a>  
		<?php
			}

			?>
      </div>
    </div>
  </div>
</div>
	
</div>

<?php
require_once __DIR__.'/../inc/footer.php';
?>
