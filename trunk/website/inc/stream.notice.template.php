<?php
$notice = file_get_contents('../actions/website/notice.txt'); 
if ($notice !== '') {
?>
<div class="status">
<?php echo $notice; ?>
</div>
<?
}
?>
