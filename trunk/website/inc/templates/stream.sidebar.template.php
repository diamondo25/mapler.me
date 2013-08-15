	<div class="span4 pull-right no-mobile">
		<div class="stream-block hide">
			<a href="#" class="btn btn-large btn-inverse" style="width: 100%;
max-width: 240px;">
				Start Mapler.me
			</a>
		</div>
	
		<div class="stream-block">
			<?php MakePlayerAvatar($main_char); ?>
			<p style="margin:0;border-bottom:1px solid rgba(0,0,0,0.1);margin-bottom:10px;">@<?php echo $_loginaccount->GetUsername(); ?> <span class="ct-label"><?php echo GetRankTitle($rank); ?></span><br/>
			<sup><a href="//<?php echo $_loginaccount->GetUsername(); ?>.<?php echo $domain; ?>/">View my profile..</a></sup></p>
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
	$rss->load('http://blog.mapler.me/rss');
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
	for($x = 0; $x < $limit; $x++) {
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
	require_once __DIR__.'/../server_info.php';
	foreach ($maplerme_servers as $servername => $data) {
		$socket = @fsockopen($data[0], $data[1], $errno, $errstr, 5);
		$data = array('state' => 'offline', 'locale' => '?', 'version' => '?', 'players' => 0);
		if ($socket) {
			$size = fread($socket, 1);
			for ($i = 0; strlen($size) < 1 && $i < 10; $i++) {
				$size = fread($socket, 1);
			}
			if (strlen($size) == 1) {
				$size = ord($size[0]);
				$data = fread($socket, $size);
				for ($i = 0; strlen($data) < $size && $i < 10; $i++) {
					$data .= fread($socket, $size - strlen($data));
				}
				if (strlen($data) == $size) {
					$data = unpack('vversion/clocale/Vplayers', $data);
					$data['state'] = 'online';
					
					switch ($data['locale']) {
						case 2: $data['locale'] = 'Korea'; $data['version'] = '1.2.'.$data['version']; break;
						case 8: $data['locale'] = 'Global'; $data['version'] /= 100; break;
						case 9: $data['locale'] = 'Europe'; $data['version'] /= 100; break;
					}
				}
			}
			fclose($socket);
		}
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