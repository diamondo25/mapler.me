	<div class="span4 pull-right no-mobile">
		<div class="stream-block hide">
			<a href="#" class="btn btn-large btn-inverse" style="width: 100%; max-width: 240px;">
				Start Mapler.me
			</a>
		</div>
	
		<div class="stream-block">
			<?php MakePlayerAvatar($__login_main_character['name'], $__login_main_character['locale']); ?>
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

<p class="lead alert alert-info">
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
		echo '<i class="icon-book"></i> <b>Mapler.me News:</b><br/><a href="'.$link.'" title="'.$title.'">'.$title.'</a>';
	}
	
	unset($rss);
	unset($feed);
?>
</p>
	
<?php
	
	$serverinfo = GetMaplerServerInfo();
	
	foreach ($serverinfo as $servername => $data) {
?>
		<p mapler-locale="<?php echo $servername; ?>" class="lead alert alert-info">
			<span class="online-server"<?php echo ($data['state'] !== 'online' ? ' style="display: none;"' : ''); ?>>
				<span><i class="icon-ok-sign"></i> <strong>MapleStory <?php echo $data['locale']; ?></strong> – </span> 
				<span players><?php echo $data['players']; ?></span> players mapling.</span>
			</span>
			<span class="offline-server"<?php echo ($data['state'] !== 'offline' ? ' style="display: none;"' : ''); ?>><i class="icon-exclamation-sign"></i> Mapler.me server '<?php echo $servername; ?>' is offline...</span>
		</p>
<?php
	}
?>

<?php
	// Check for expiring items...
	$query = "
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
";
	foreach ($_supported_locales as $locale) {
		$db = ConnectCharacterDatabase($locale);
		$q = $db->query($query);
		while ($row = $q->fetch_row()) {
			$itemids = explode(',', $row[1]);
			$times = explode(',', $row[2]);
?>
		
		<div class="stream-block">
			<?php MakePlayerAvatar($row[0], $locale, array('face' => 'angry', 'styleappend' => 'float: right;')); ?>
			<strong>Expiring Items</strong><br />
<?php foreach ($itemids as $index => $itemid): ?>
			<?php echo GetMapleStoryString('item', $itemid, 'name', $locale); ?> expires in <?php echo time_elapsed_string($times[$index] - $__server_time); ?>!<br />
<?php endforeach; ?>
		</div>
<?php	
		}
	}
?>
</div>