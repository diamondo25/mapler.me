<?php
$notice = file_get_contents('../actions/website/notice.txt'); 
if ($notice !== '') {
?>
<div class="status">
<?php require_once __DIR__.'/../inc/stream.notice.template.php'; ?>
</div>
<?
}
?>
