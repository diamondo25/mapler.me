<?php
$notice = file_get_contents('../manage/notice.txt');
if ($notice !== '') {
?>
<div class="status">
<?php echo $notice; ?>
</div>
<br/>
<?php
}
?>