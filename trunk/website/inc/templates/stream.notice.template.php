<?php
$notice = @file_get_contents('../inc/notice.txt');
if (!empty($notice)) {
?>
	<div class="stream-block">
		<p class="lead" style="margin:0;">
			<?php echo $notice; ?>
		</p>
	</div>
<?php
}
?>