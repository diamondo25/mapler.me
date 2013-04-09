<?php
$notice = @file_get_contents('../inc/notice.txt');
if (!empty($notice)) {
?>
	<div class="status">
		<?php echo $notice; ?>
	</div>
<br />
<?php
}
?>