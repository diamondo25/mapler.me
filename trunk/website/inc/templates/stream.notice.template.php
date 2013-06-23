<?php
$notice = @file_get_contents('../inc/notice.txt');
if (!empty($notice)) {
?>
	<div class="stream-block" style="box-shadow: 0px 0px 30px #FFF;">
		<p class="notice" style="margin:0;">
			<?php echo $notice; ?>
		</p>
	</div>
<?php
}
?>