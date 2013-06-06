<?php
require_once __DIR__.'/../../inc/functions.php';
require_once __DIR__.'/../../inc/functions.ajax.php';
require_once __DIR__.'/../../inc/classes/statusses.php';

CheckSupportedTypes('info');


if ($request_type == 'info') {
	$res = array();
	$res['time'] = $__server_time;
	
	$_client_time = isset($_POST['client-time']) ? intval($_POST['client-time']) : 0;

	
	$res['loggedin'] = $_loggedin;
	$res['notifications'] = $_loggedin ? (int)GetNotification() : 0;

	if ($_loggedin) {
		$res['membername'] = $_loginaccount->GetUsername();
		$__database->query("UPDATE accounts SET last_login = NOW(), last_ip = '".$_SERVER['REMOTE_ADDR']."' WHERE id = ".$_loginaccount->GetID());
	}
	
	$status_info = array();
	if (isset($_POST['shown-statuses'])) {
		// Check status info
		$correctids = array();
		foreach ($_POST['shown-statuses'] as $oriid) {
			$id = intval($oriid);
			if ($id == 0) {
				continue;
			}
			$correctids[] = $id;
		}
		$correctids = array_unique($correctids);
		if (count($correctids) > 0) {
			$tmp = "
SELECT
	s.id,
	(SELECT COUNT(s2.id) FROM social_statuses s2 WHERE s2.reply_to = s.id) AS `reply_count`
FROM
	social_statuses s
WHERE
	s.id IN (".implode(',', $correctids).")";
			$q = $__database->query($tmp);
			if ($q->num_rows == 0) {
				// All deleted
				foreach ($_POST['shown-statuses'] as $oriid)
					$status_info['deleted'][] = $oriid;
			}
			else {
				while ($row = $q->fetch_row()) {
					$status_info['reply_count'][$row[0]] = $row[1];
					if (($key = array_search($row[0], $correctids)) !== false) {
						unset($correctids[$key]);
					}
				}
				foreach ($correctids as $oriid)
					$status_info['deleted'][] = $oriid;
			}
			$q->free();
		}
		
	}
	$res['status_info'] = $status_info;
	
	
	$url = isset($_POST['url']) ? $_POST['url'] : null;
	$parsed_url = $url == null ? null : parse_url($url);
	$is_ok_url = $url != null && strpos($parsed_url['host'], $domain) !== false;
	
	if ($_loggedin && $res['membername'] == 'Diamondo25') {
		$res['is_ok'] = $is_ok_url;
		$res['parsed_url'] = $parsed_url;
	}
	
	if ($is_ok_url && isset($_POST['has-statusses']) && $_POST['has-statusses'] != 0) {
		$subdomain = trim(substr($parsed_url['host'], 0, strpos($parsed_url['host'], $domain)), '.');

		$whereq = '> FROM_UNIXTIME('.$_client_time.')';
		if (isset($_POST['older-than'])) {
			$whereq = '< FROM_UNIXTIME('.$_client_time.')';
		}
		$whereq_1 = '`when` '.$whereq.($_client_time == 0 ? ' AND `when` > DATE_SUB(NOW(), INTERVAL 2 DAY)' : '');
		$whereq_2 = '`timestamp` '.$whereq.($_client_time == 0 ? ' AND `timestamp` > DATE_SUB(NOW(), INTERVAL 2 DAY)' : '');
		if (isset($_POST['older-than'])) {
			$whereq_1 .= ' AND `when` > DATE_SUB(FROM_UNIXTIME('.$_client_time.'), INTERVAL 2 DAY)';
			$whereq_2 .= ' AND `timestamp` > DATE_SUB(FROM_UNIXTIME('.$_client_time.'), INTERVAL 2 DAY)';
		}
		
		if (strpos($url, '/'.$domain.'/blog/') !== false) {
			// No time thingies, only blog posts
			$whereq_1 = ' 1 = 0 '; // heh
			$whereq_2 .= ' AND blog = 1';
		}
		
		$q = "
SELECT
	UNIX_TIMESTAMP(`timestamp`),
	`type`,
	`col1`,
	`col2`,
	a.username AS `account_name`,
	a.nickname,
	a.account_rank
FROM
(
SELECT 
	`when` AS `timestamp`,
	'timeline_row' AS `type`,
	CONVERT(CONCAT(c.`name`, X'02', `type`) USING latin1) AS `col1`,
	CONVERT(`data` USING latin1) AS `col2`,
	`GetCharacterInternalIDAccountID`(`objectid`) AS `account_id`
FROM
	`timeline`
LEFT JOIN
	characters c
	ON
		c.internal_id = objectid
WHERE
	`type` = 'levelup'
	AND
	".$whereq_1."

UNION ALL

SELECT 
	`timestamp`,
	'status' AS `type`,
	CONVERT(CONCAT(`id`, X'02', `account_id`, X'02', `nickname`, X'02', `character`, X'02', `blog`, X'02', `override`, X'02', IF(`reply_to` IS NULL, '-', `reply_to`), X'02',(
	SELECT
		COUNT(s_inner.id)
	FROM
		social_statuses s_inner
	WHERE
		s_inner.reply_to = ss.id
	)) USING latin1) AS `col1`,
	CONVERT(`content` USING latin1) AS `col2`,
	`account_id`
FROM
	`social_statuses` ss
WHERE
	".$whereq_2."
) stream
LEFT JOIN
	accounts a
	ON
		a.id = `account_id`
";
		$whereadded = false;
		if ($_loggedin && $subdomain == '') { // Main screen
			$whereadded = true;
			$q .= "
WHERE
	`FriendStatus`(`account_id`, ".$_loginaccount->GetID().") IN ('FRIENDS', 'FOREVER_ALONE')";
		}
		if ($subdomain != '') {
			if ($whereadded)
				$q .= ' AND ';
			else 
				$q .= ' WHERE ';
			$whereadded = true;
			$q .= "a.username = '".$__database->real_escape_string($subdomain)."'";
		}

		$q .= "
ORDER BY
	`timestamp` DESC
LIMIT
	15
";
		if ($_loggedin && $res['membername'] == 'Diamondo25') {
			$res['q'] = $q;
		}
		$q = $__database->query($q);

		$stream = array();
		$timestamp = $__server_time;
		$lowest_date = 0;
		while ($row = $q->fetch_row()) {
			$timestamp = $row[0];
			$type = $row[1];
			$content = explode(chr(0x02), $row[2]);
			$info = $row[3];
			$username = $row[4];
			$nickname = $row[5];
			$account_rank = $row[6];
			$seconds_since = $__server_time - $timestamp;
			if (count($stream) == 0)
				$lowest_date = $timestamp;
			ob_start();
			if ($type == 'timeline_row') {
				if ($content[1] == 'levelup') {
?>
			<div class="status">
				<div class="header">
					<div class="character" style="background: url('http://<?php echo $domain; ?>/avatar/<?php echo $content[0]; ?>') no-repeat center -17px #FFF;"></div><br/>
			<p><a href="//<?php echo $username; ?>.mapler.me/"><?php echo $nickname; ?></a> <span class="faded">(@<?php echo $username; ?>)</span>
			<?php if ($account_rank >= RANK_MODERATOR): ?>
					<span class="ct-label"><i class="icon-star"></i> <?php echo GetRankTitle($account_rank); ?></span>
			<?php endif; ?>
			</p>
			</div>
			<br/>
			<div class="status-contents">
			<a href="//mapler.me/player/<?php echo $content[0]; ?>"><?php echo $content[0]; ?></a> just reached Level <span style="font-size: 13px"><?php echo $info; ?>!</span>
			</div>
			<div class="status-extra" style="clear:both;">
			 <span status-post-time="<?php echo $timestamp; ?>" class="status-time" style="float:right;"><?php echo time_elapsed_string($seconds_since); ?> ago - Auto</span>
			</div>
		</div>
<?php
				}
				// hurr
			}
			elseif ($type == 'status') {
				$status = new Status(array(
					'id' => $content[0],
					'account_id' => $content[1],
					'nickname' => $content[2],
					'character' => $content[3],
					'blog' => $content[4],
					'override' => $content[5],
					'reply_to' => $content[6],
					'reply_count' => $content[7],
					'content' => $info,
					'timestamp' => $timestamp
				));
				
				$status->PrintAsHTML('');
				unset($status);
			}
			$level_info = ob_get_clean();
			$stream[] = array($timestamp, $level_info);
		}
		$highest_date = $timestamp;

		$res['statuses'] = $stream;
		$res['oldest_status'] = $lowest_date;
		$res['newest_status'] = $highest_date;

	}
	
	JSONAnswer($res);
}
?>