<?php 
require_once __DIR__.'/../inc/header.php';
$statusid = intval($_GET['id']);
    
$q = $__database->query("
SELECT
	social_statuses.*,
	accounts.username,
	TIMESTAMPDIFF(SECOND, timestamp, NOW()) AS `secs_since`
FROM
	social_statuses
LEFT JOIN
	accounts
	ON
		social_statuses.account_id = accounts.id
WHERE
	social_statuses.id = '".$statusid."'
	
ORDER BY
secs_since ASC
");

$cache = array();
while ($row = $q->fetch_assoc()) {
	$cache[] = $row;
}

$q->free();

function time_elapsed_string($etime) {
   if ($etime < 1) {
       return 'now';
   }
   
   $a = array( 12 * 30 * 24 * 60 * 60  =>  'year',
               30 * 24 * 60 * 60       =>  'month',
               24 * 60 * 60            =>  'day',
               60 * 60                 =>  'hour',
               60                      =>  'minute',
               1                       =>  'second'
               );
   
   foreach ($a as $secs => $str) {
       $d = $etime / $secs;
       if ($d >= 1) {
           $r = round($d);
           return $r . ' ' . $str . ($r > 1 ? 's' : '');
       }
   }
}

?>
	<div class="row">
	<div class="span12">

<?php
if (count($cache) == 0) {
?>
	<center>
		<img src="http://mapler.me/inc/img/icon.png"/>
		<p>404: Status not found.</p>
	</center>
<?php
}

// printing table rows

foreach ($cache as $row) {
	$content = $row['content'];
	//@replies
	$content1 = preg_replace('/(^|[^a-z0-9_])@([a-z0-9_]+)/i', '$1<a href="http://$2.mapler.me/">@$2</a>', $content);
	//#hashtags (no search for the moment)
	$content2 = preg_replace('/(^|[^a-z0-9_])#([a-z0-9_]+)/i', '$1<a href="#">#$2</a>', $content1);
?>
			<div class="status <?php if ($row['override'] == 1): ?> notification<?php endif; ?>">
				<div class="header" style="background: url('http://mapler.me/avatar/<?php echo $row['character']; ?>') no-repeat right -30px #FFF;">
					<a href="//<?php echo $row['username'];?>.<?php echo $domain; ?>/"><?php echo $row['nickname'];?></a> said:
				</div>
				<br />
				<?php $parser->parse($content2); echo $parser->getAsHtml(); ?>
				<div class="status-extra">
					<?php if ($row['account_id'] !== 2): ?><a href="#" class="mention-<?php echo $row['id']; ?>" mentioned="<?php echo $row['username']; ?>"><i class="icon-share-alt"></i></a>
					<script type="text/javascript">
							$('.mention-<?php echo $row['id']; ?>').click(function() {
								var value = $(".mention-<?php echo $row['id']; ?>").attr('mentioned');
								var input = $('#post-status');
								input.val(input.val() + '@' + value + ' ');
								$(".poster").addClass("in");
								$('.poster').css("height","auto");
								return false;
							});
					</script>
					<?php endif; ?>
					<a href="//<?php echo $domain; ?>/stream/status/<?php echo $row['id']; ?>"><?php echo time_elapsed_string($row['secs_since']); ?> ago</a>
<?php
	if ($_loggedin) {
		if (IsOwnAccount()) {
?>
						- <a href="#" onclick="RemoveStatus(<?php echo $row['id']; ?>);">delete?</a>
<?php
		}
		else {
			// Report button
?>
						- <a href="#"></a>
<?php
		}
	}
?>
				</div>
			</div>
<?php    
}
?>
		</div>
	</div>
<?php
require_once __DIR__.'/../inc/footer.php';
?>