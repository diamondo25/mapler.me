	<div class="span4 pull-right no-mobile">
		<div class="stream-block hide">
			<a href="#" class="btn btn-large btn-inverse" style="width: 100%;
max-width: 240px;">
				Start Mapler.me
			</a>
		</div>
	
		<div class="stream-block">
			<?php MakePlayerAvatar($main_char); ?>
			<p>@<?php echo $_loginaccount->GetUsername(); ?><br/>
			<i class="icon-comments"></i> <span id="memberstatuses"></span></p>
			<p><i class="icon-star"></i> <?php echo GetRankTitle($rank); ?></p>
		</div>
		
		
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

		
		<div class="stream-block">
<?php
	$rss = new DOMDocument();
	$rss->loadXML(file_get_contents('http://blog.mapler.me/rss'));
	$feed = array();
	foreach ($rss->getElementsByTagName('item') as $node) {
		$item = array ( 
			'title' => $node->getElementsByTagName('title')->item(0)->nodeValue,
			'desc' => $node->getElementsByTagName('description')->item(0)->nodeValue,
			'link' => $node->getElementsByTagName('link')->item(0)->nodeValue,
			'date' => $node->getElementsByTagName('pubDate')->item(0)->nodeValue,
			);
		array_push($feed, $item);
	}
	$limit = 1;
	for($x = 0; $x < $limit && $x < count($item); $x++) {
		$title = str_replace(' & ', ' &amp; ', $feed[$x]['title']);
		$link = $feed[$x]['link'];
		$description = $feed[$x]['desc'];
		if (strlen($description) > 100) {
			// truncate string
			$desc = substr($description, 0, 100);

			// make sure it ends in a word so assassinate doesn't become ass...
			$description = substr($desc, 0, strrpos($desc, ' ')).'... <br /><a href="'.$link.'">Read More?</a>'; 
		}
		$date = date('l F d, Y', strtotime($feed[$x]['date']));
		echo '<span class="label label-info">Latest Mapler.me News:</span> <strong><a href="'.$link.'" title="'.$title.'">'.$title.'</a></strong>';
	}
	
	unset($rss);
	unset($feed);
?>
	</div>
	
	
	<div class="stream-block">
<?php
	
	$serverinfo = GetMaplerServerInfo();
	
	foreach ($serverinfo as $servername => $data) {
?>
		<div mapler-locale="<?php echo $servername; ?>">
			<span class="online-server"<?php echo ($data['state'] !== 'online' ? ' style="display: none;"' : ''); ?>><span class="label label-success">MapleStory <?php echo $data['locale']; ?> V<span version><?php echo $data['version']; ?></span></span> <span class="badge badge-success"><span players><?php echo $data['players']; ?></span> online</span></span>
			<span class="offline-server"<?php echo ($data['state'] !== 'offline' ? ' style="display: none;"' : ''); ?>>Mapler.me server '<?php echo $servername; ?>' is offline...</span>
		</div>
<?php
	}
?>

	</div>
<?php
	// Check for expiring items...
	$q = $__database->query("
SELECT
	c.name,
	GROUP_CONCAT(i.itemid),
	GROUP_CONCAT(UNIX_TIMESTAMP(FROM_FILETIME(i.expires)))
FROM
	items i
LEFT JOIN
	characters c
	ON
		c.internal_id = i.character_id
WHERE
	`GetCharacterAccountID`(c.id) = ".$_loginaccount->GetID()."
	AND
	i.expires <> 150842304000000000
	AND
	TO_FILETIME(NOW()) < i.expires
	AND
	TO_FILETIME(DATE_ADD(NOW(), INTERVAL 1 WEEK)) > i.expires
	AND
	(flags & 0x01) = 0 # Flag 0x01 = Item lock
GROUP BY
	c.internal_id
");
	while ($row = $q->fetch_row()) {
		$itemids = explode(',', $row[1]);
		$times = explode(',', $row[2]);
?>
		
		<div class="stream-block">
			<?php MakePlayerAvatar($row[0], array('face' => 'angry', 'styleappend' => 'float: right;')); ?>
			<strong>Expiring Items</strong><br />
<?php foreach ($itemids as $index => $itemid): ?>
			<?php echo GetMapleStoryString('item', $itemid, 'name'); ?> expires in <?php echo time_elapsed_string($times[$index] - $__server_time); ?>!<br />
<?php endforeach; ?>
		</div>
<?php	
	}
?>
</div>