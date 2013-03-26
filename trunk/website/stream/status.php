<?php 
require_once __DIR__.'/../inc/header.php';
$statusid = htmlentities($_GET['id']);
    
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
	
$fixugh = '0';
	
$cache = array();
while ($row = $q->fetch_assoc()) {
	if (isset($fixugh)) {
		if ($fixugh == 2) { // Always hide... :)
			continue;
		}
	}
	$cache[] = $row;
}

$q->free();

function time_elapsed_string($etime) {
   if ($etime < 1) {
       return '0 seconds';
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
if (count($cache) == 0) { ?>
<center>
	<img src="http://mapler.me/inc/img/icon.png"/>
		<p>404: Status not found.</p>
</center>
<?php
}
?>
	
	<?php

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
					<?php if ($row['comments_disabled'] == '0'): ?>
					<a href="//<?php echo $domain; ?>/stream/status/<?php echo $row['id']; ?>#disqus_thread"></a>
					<img src="//<?php echo $domain; ?>/inc/img/icons/comment.png"/> – <?php endif; ?><a href="//<?php echo $domain; ?>/stream/status/<?php echo $row['id']; ?>"><?php echo time_elapsed_string($row['secs_since']); ?> ago</a>

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
						
<?php if ($_loggedin && $row['comments_disabled'] == '0') { ?>
			<div class="status">
				<div id="disqus_thread"></div>
				<script type="text/javascript">
					var disqus_shortname = 'maplerme';
					(function() {
					var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
					dsq.src = '//' + disqus_shortname + '.disqus.com/embed.js';
					(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
					})();
				</script>
			</div>
<?php
	}    
}
?>
		</div>
	</div>
<?php
require_once __DIR__.'/../inc/footer.php';
?>