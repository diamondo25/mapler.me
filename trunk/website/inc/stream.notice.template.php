<?php
$notice = file_get_contents('notice.txt');
if ($notice !== '') {
?>
	<div class="status">
		<?php echo $notice; ?>
	</div>
<br/>
<?php
}
?>